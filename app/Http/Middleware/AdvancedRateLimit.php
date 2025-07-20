<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdvancedRateLimit
{
    /**
     * Handle an incoming request with advanced rate limiting
     */
    public function handle(Request $request, Closure $next): Response
    {
        $client = $request->get('authenticated_client');
        $ip = $request->ip();
        $endpoint = $request->path();
        
        // Check multiple rate limiting tiers
        $violations = [];
        
        // 1. Check global IP-based rate limit (anti-DDoS)
        if ($this->checkGlobalIpLimit($ip)) {
            $violations[] = 'global_ip_limit';
        }
        
        // 2. Check client-specific rate limits
        if ($client && $this->checkClientRateLimit($client, $endpoint)) {
            $violations[] = 'client_rate_limit';
        }
        
        // 3. Check endpoint-specific rate limits
        if ($this->checkEndpointRateLimit($endpoint, $ip)) {
            $violations[] = 'endpoint_rate_limit';
        }
        
        // 4. Check burst protection (sliding window)
        if ($this->checkBurstProtection($client, $ip)) {
            $violations[] = 'burst_protection';
        }
        
        // 5. Check for suspicious patterns
        if ($this->checkSuspiciousPatterns($client, $ip, $endpoint)) {
            $violations[] = 'suspicious_pattern';
        }
        
        // If any violations, return rate limit response
        if (!empty($violations)) {
            return $this->rateLimitResponse($violations, $client, $ip);
        }
        
        // Update usage counters
        $this->updateUsageCounters($client, $ip, $endpoint);
        
        return $next($request);
    }
    
    /**
     * Check global IP-based rate limit (anti-DDoS)
     */
    private function checkGlobalIpLimit(string $ip): bool
    {
        $key = "global_ip_limit:{$ip}";
        $limit = 1000; // 1000 requests per hour per IP globally
        $window = 3600; // 1 hour
        
        $current = Cache::get($key, 0);
        return $current >= $limit;
    }
    
    /**
     * Check client-specific rate limits
     */
    private function checkClientRateLimit($client, string $endpoint): bool
    {
        if (!$client) return false;
        
        // Get client-specific rate limits
        $rateLimits = DB::table('api_rate_limits')
            ->where('client_id', $client->id)
            ->where(function($query) use ($endpoint) {
                $query->whereNull('endpoint')
                      ->orWhere('endpoint', $endpoint);
            })
            ->where('is_active', true)
            ->first();
        
        $limits = [
            'per_minute' => $rateLimits->requests_per_minute ?? 60,
            'per_hour' => $rateLimits->requests_per_hour ?? 3600,
            'per_day' => $rateLimits->requests_per_day ?? 86400
        ];
        
        // Check each time window
        foreach ($limits as $window => $limit) {
            $seconds = match($window) {
                'per_minute' => 60,
                'per_hour' => 3600,
                'per_day' => 86400
            };
            
            $key = "client_limit:{$client->id}:{$window}";
            $current = Cache::get($key, 0);
            
            if ($current >= $limit) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check endpoint-specific rate limits
     */
    private function checkEndpointRateLimit(string $endpoint, string $ip): bool
    {
        // Define endpoint-specific limits
        $endpointLimits = [
            'api/v1/transactions' => 100, // 100 per minute for transaction endpoint
            'api/v1/balance' => 20,       // 20 per minute for balance checks
            'api/v1/status' => 10         // 10 per minute for status checks
        ];
        
        $limit = $endpointLimits[$endpoint] ?? 60; // Default 60 per minute
        
        $key = "endpoint_limit:{$endpoint}:{$ip}";
        $current = Cache::get($key, 0);
        
        return $current >= $limit;
    }
    
    /**
     * Check burst protection using sliding window
     */
    private function checkBurstProtection($client, string $ip): bool
    {
        $identifier = $client ? "client:{$client->id}" : "ip:{$ip}";
        $key = "burst_protection:{$identifier}";
        
        // Allow max 10 requests in 10 seconds (burst)
        $burstLimit = 10;
        $burstWindow = 10;
        
        $now = time();
        $requests = Cache::get($key, []);
        
        // Remove old requests outside the window
        $requests = array_filter($requests, function($timestamp) use ($now, $burstWindow) {
            return ($now - $timestamp) < $burstWindow;
        });
        
        return count($requests) >= $burstLimit;
    }
    
    /**
     * Check for suspicious patterns
     */
    private function checkSuspiciousPatterns($client, string $ip, string $endpoint): bool
    {
        // Pattern 1: Too many failed requests
        $failedKey = "failed_requests:{$ip}";
        $failedCount = Cache::get($failedKey, 0);
        if ($failedCount > 20) { // 20 failed requests in the last hour
            return true;
        }
        
        // Pattern 2: Rapid endpoint switching (potential reconnaissance)
        $endpointKey = "endpoint_pattern:{$ip}";
        $endpoints = Cache::get($endpointKey, []);
        $uniqueEndpoints = array_unique($endpoints);
        if (count($uniqueEndpoints) > 10) { // Accessing more than 10 different endpoints in 5 minutes
            return true;
        }
        
        // Pattern 3: Unusual time-based patterns (requests at unusual hours)
        $hour = date('H');
        if ($hour >= 2 && $hour <= 5) { // 2 AM to 5 AM
            $nightKey = "night_requests:{$ip}";
            $nightRequests = Cache::get($nightKey, 0);
            if ($nightRequests > 50) { // More than 50 requests during night hours
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Update usage counters
     */
    private function updateUsageCounters($client, string $ip, string $endpoint): void
    {
        $now = time();
        
        // Update global IP counter
        $globalKey = "global_ip_limit:{$ip}";
        Cache::increment($globalKey, 1);
        if (!Cache::has($globalKey)) {
            Cache::put($globalKey, 1, 3600);
        }
        
        // Update client counters
        if ($client) {
            $windows = ['per_minute' => 60, 'per_hour' => 3600, 'per_day' => 86400];
            foreach ($windows as $window => $seconds) {
                $key = "client_limit:{$client->id}:{$window}";
                Cache::increment($key, 1);
                if (!Cache::has($key)) {
                    Cache::put($key, 1, $seconds);
                }
            }
        }
        
        // Update endpoint counter
        $endpointKey = "endpoint_limit:{$endpoint}:{$ip}";
        Cache::increment($endpointKey, 1);
        if (!Cache::has($endpointKey)) {
            Cache::put($endpointKey, 1, 60);
        }
        
        // Update burst protection
        $identifier = $client ? "client:{$client->id}" : "ip:{$ip}";
        $burstKey = "burst_protection:{$identifier}";
        $requests = Cache::get($burstKey, []);
        $requests[] = $now;
        Cache::put($burstKey, $requests, 10);
        
        // Update endpoint pattern tracking
        $endpointPatternKey = "endpoint_pattern:{$ip}";
        $endpoints = Cache::get($endpointPatternKey, []);
        $endpoints[] = $endpoint;
        Cache::put($endpointPatternKey, array_slice($endpoints, -20), 300); // Keep last 20, 5 minutes
        
        // Update night requests counter
        $hour = date('H');
        if ($hour >= 2 && $hour <= 5) {
            $nightKey = "night_requests:{$ip}";
            Cache::increment($nightKey, 1);
            if (!Cache::has($nightKey)) {
                Cache::put($nightKey, 1, 3600);
            }
        }
    }
    
    /**
     * Return rate limit response with detailed information
     */
    private function rateLimitResponse(array $violations, $client, string $ip): JsonResponse
    {
        // Log the rate limit violation
        $this->logRateLimitViolation($violations, $client, $ip);
        
        // Determine retry-after based on violation type
        $retryAfter = $this->calculateRetryAfter($violations);
        
        // Check if this IP should be temporarily blocked
        $this->checkForBlocking($ip, $violations);
        
        $response = [
            'error' => 'Rate Limit Exceeded',
            'message' => 'Request rate limit exceeded. Please reduce your request frequency.',
            'violations' => $violations,
            'retry_after' => $retryAfter,
            'limits' => $this->getCurrentLimits($client),
            'timestamp' => now()->toISOString()
        ];
        
        return response()->json($response, 429)
            ->header('Retry-After', $retryAfter)
            ->header('X-RateLimit-Limit', $this->getCurrentLimits($client)['per_hour'])
            ->header('X-RateLimit-Remaining', max(0, $this->getRemainingRequests($client)))
            ->header('X-RateLimit-Reset', time() + $retryAfter);
    }
    
    /**
     * Log rate limit violations
     */
    private function logRateLimitViolation(array $violations, $client, string $ip): void
    {
        DB::table('security_logs')->insert([
            'event_type' => 'rate_limit_violation',
            'client_id' => $client->id ?? null,
            'ip_address' => $ip,
            'user_agent' => request()->userAgent(),
            'endpoint' => request()->path(),
            'data' => json_encode([
                'violations' => $violations,
                'request_count' => $this->getCurrentRequestCount($client, $ip)
            ]),
            'severity' => count($violations) > 2 ? 'high' : 'medium',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    
    /**
     * Calculate retry-after seconds
     */
    private function calculateRetryAfter(array $violations): int
    {
        if (in_array('suspicious_pattern', $violations)) {
            return 3600; // 1 hour for suspicious patterns
        }
        
        if (in_array('burst_protection', $violations)) {
            return 60; // 1 minute for burst protection
        }
        
        return 300; // 5 minutes default
    }
    
    /**
     * Check if IP should be temporarily blocked
     */
    private function checkForBlocking(string $ip, array $violations): void
    {
        $key = "rate_limit_violations:{$ip}";
        $violationCount = Cache::get($key, 0);
        
        Cache::put($key, $violationCount + 1, 3600);
        
        // Block IP if too many violations
        if ($violationCount >= 5) {
            Cache::put("blocked_ip:{$ip}", 'repeated_rate_limit_violations', 7200); // 2 hours
            
            // Log the block
            DB::table('security_logs')->insert([
                'event_type' => 'ip_blocked_rate_limit',
                'ip_address' => $ip,
                'user_agent' => request()->userAgent(),
                'data' => json_encode(['violation_count' => $violationCount + 1]),
                'severity' => 'high',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
    
    /**
     * Get current limits for client
     */
    private function getCurrentLimits($client): array
    {
        if (!$client) {
            return ['per_minute' => 60, 'per_hour' => 3600, 'per_day' => 86400];
        }
        
        $rateLimits = DB::table('api_rate_limits')
            ->where('client_id', $client->id)
            ->whereNull('endpoint')
            ->where('is_active', true)
            ->first();
        
        return [
            'per_minute' => $rateLimits->requests_per_minute ?? 60,
            'per_hour' => $rateLimits->requests_per_hour ?? 3600,
            'per_day' => $rateLimits->requests_per_day ?? 86400
        ];
    }
    
    /**
     * Get remaining requests for client
     */
    private function getRemainingRequests($client): int
    {
        if (!$client) return 0;
        
        $limits = $this->getCurrentLimits($client);
        $key = "client_limit:{$client->id}:per_hour";
        $current = Cache::get($key, 0);
        
        return max(0, $limits['per_hour'] - $current);
    }
    
    /**
     * Get current request count
     */
    private function getCurrentRequestCount($client, string $ip): array
    {
        return [
            'global_ip' => Cache::get("global_ip_limit:{$ip}", 0),
            'client_hour' => $client ? Cache::get("client_limit:{$client->id}:per_hour", 0) : 0,
            'client_minute' => $client ? Cache::get("client_limit:{$client->id}:per_minute", 0) : 0
        ];
    }
}