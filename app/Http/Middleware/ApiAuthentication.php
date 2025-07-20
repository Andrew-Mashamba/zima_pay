<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ApiAuthentication
{
    /**
     * Handle an incoming request with military-grade authentication
     */
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        try {
            // Extract authentication headers
            $apiKey = $request->header('X-API-Key');
            $timestamp = $request->header('X-Timestamp');
            $signature = $request->header('X-Signature');
            $nonce = $request->header('X-Nonce');
            
            // Basic validation
            if (!$apiKey || !$timestamp || !$signature || !$nonce) {
                return $this->unauthorizedResponse('Missing required authentication headers');
            }
            
            // Validate timestamp (prevent replay attacks)
            if (!$this->isValidTimestamp($timestamp)) {
                return $this->unauthorizedResponse('Invalid or expired timestamp');
            }
            
            // Check for nonce reuse (prevent replay attacks)
            if ($this->isNonceUsed($nonce, $apiKey)) {
                return $this->unauthorizedResponse('Nonce already used - potential replay attack');
            }
            
            // Authenticate client
            $client = $this->authenticateClient($apiKey);
            if (!$client) {
                return $this->unauthorizedResponse('Invalid API key');
            }
            
            // Check client status and permissions
            if (!$client->status) {
                return $this->unauthorizedResponse('Client account suspended');
            }
            
            // Verify request signature
            if (!$this->verifySignature($request, $client->api_secret, $timestamp, $nonce, $signature)) {
                return $this->unauthorizedResponse('Invalid signature');
            }
            
            // Check rate limiting
            if ($this->isRateLimited($client)) {
                return $this->rateLimitResponse($client);
            }
            
            // Check IP whitelist if configured
            if (!$this->isIpAllowed($client, $request->ip())) {
                return $this->unauthorizedResponse('IP address not whitelisted');
            }
            
            // Store nonce to prevent reuse
            $this->storeNonce($nonce, $apiKey);
            
            // Add client to request for use in controllers
            $request->merge(['authenticated_client' => $client]);
            
            // Log successful authentication
            $this->logSecurityEvent('api_authentication_success', [
                'client_id' => $client->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'endpoint' => $request->path()
            ]);
            
            return $next($request);
            
        } catch (\Exception $e) {
            Log::error('API Authentication error: ' . $e->getMessage(), [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'headers' => $request->headers->all()
            ]);
            
            return $this->unauthorizedResponse('Authentication failed');
        }
    }
    
    /**
     * Validate timestamp to prevent replay attacks
     */
    private function isValidTimestamp(string $timestamp): bool
    {
        $requestTime = Carbon::createFromTimestamp($timestamp);
        $now = Carbon::now();
        
        // Allow 5 minutes tolerance for clock skew
        $tolerance = 300; // 5 minutes in seconds
        
        return abs($now->timestamp - $requestTime->timestamp) <= $tolerance;
    }
    
    /**
     * Check if nonce has been used (prevent replay attacks)
     */
    private function isNonceUsed(string $nonce, string $apiKey): bool
    {
        $key = "nonce:{$apiKey}:{$nonce}";
        return Cache::has($key);
    }
    
    /**
     * Store nonce with expiration
     */
    private function storeNonce(string $nonce, string $apiKey): void
    {
        $key = "nonce:{$apiKey}:{$nonce}";
        // Store for 10 minutes (longer than timestamp tolerance)
        Cache::put($key, true, 600);
    }
    
    /**
     * Authenticate client by API key
     */
    private function authenticateClient(string $apiKey)
    {
        return DB::table('clients')
            ->where('api_key', $apiKey)
            ->where('status', true)
            ->first();
    }
    
    /**
     * Verify HMAC signature
     */
    private function verifySignature(Request $request, string $secret, string $timestamp, string $nonce, string $signature): bool
    {
        // Get request body
        $body = $request->getContent();
        
        // Create canonical string
        $method = $request->method();
        $uri = $request->getRequestUri();
        $contentType = $request->header('Content-Type', '');
        
        $canonicalString = implode("\n", [
            $method,
            $uri,
            $contentType,
            $timestamp,
            $nonce,
            hash('sha256', $body)
        ]);
        
        // Calculate expected signature
        $expectedSignature = hash_hmac('sha256', $canonicalString, $secret);
        
        // Use timing-safe comparison to prevent timing attacks
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * Check rate limiting
     */
    private function isRateLimited($client): bool
    {
        $key = "rate_limit:{$client->id}";
        $limit = $client->rate_limit ?? 1000; // Default 1000 requests per hour
        $window = 3600; // 1 hour
        
        $current = Cache::get($key, 0);
        
        if ($current >= $limit) {
            return true;
        }
        
        // Increment counter
        Cache::put($key, $current + 1, $window);
        
        return false;
    }
    
    /**
     * Check IP whitelist
     */
    private function isIpAllowed($client, string $ip): bool
    {
        // Get client settings
        $settings = json_decode($client->settings ?? '{}', true);
        $whitelist = $settings['ip_whitelist'] ?? [];
        
        // If no whitelist configured, allow all IPs
        if (empty($whitelist)) {
            return true;
        }
        
        // Check if IP is in whitelist (supports CIDR notation)
        foreach ($whitelist as $allowedIp) {
            if ($this->ipInRange($ip, $allowedIp)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if IP is in range (supports CIDR)
     */
    private function ipInRange(string $ip, string $range): bool
    {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }
        
        list($range, $netmask) = explode('/', $range, 2);
        $range_decimal = ip2long($range);
        $ip_decimal = ip2long($ip);
        $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
        $netmask_decimal = ~ $wildcard_decimal;
        
        return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
    }
    
    /**
     * Log security events
     */
    private function logSecurityEvent(string $event, array $data): void
    {
        DB::table('security_logs')->insert([
            'event_type' => $event,
            'client_id' => $data['client_id'] ?? null,
            'ip_address' => $data['ip_address'],
            'user_agent' => $data['user_agent'],
            'endpoint' => $data['endpoint'] ?? null,
            'data' => json_encode($data),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    
    /**
     * Return unauthorized response
     */
    private function unauthorizedResponse(string $message): JsonResponse
    {
        $this->logSecurityEvent('api_authentication_failed', [
            'reason' => $message,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'headers' => request()->headers->all()
        ]);
        
        return response()->json([
            'error' => 'Unauthorized',
            'message' => $message,
            'timestamp' => now()->toISOString()
        ], 401);
    }
    
    /**
     * Return rate limit response
     */
    private function rateLimitResponse($client): JsonResponse
    {
        $this->logSecurityEvent('rate_limit_exceeded', [
            'client_id' => $client->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
        
        return response()->json([
            'error' => 'Rate Limit Exceeded',
            'message' => 'Too many requests. Please try again later.',
            'retry_after' => 3600,
            'timestamp' => now()->toISOString()
        ], 429)->header('Retry-After', 3600);
    }
}