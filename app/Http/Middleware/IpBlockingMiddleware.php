<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\SecurityService;
use App\Services\ThreatDetectionService;

class IpBlockingMiddleware
{
    
    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        $ip = $request->ip();
        
        // Check if IP is blocked
        if ($this->isIpBlocked($ip)) {
            return $this->blockedResponse($ip);
        }
        
        // Check for immediate threats
        $client = $request->get('authenticated_client');
        $clientId = $client->id ?? null;
        
        // Analyze request for threats
        $threatDetectionService = app(ThreatDetectionService::class);
        $threatAnalysis = $threatDetectionService->analyzeRequest($request, $clientId);
        
        if ($threatAnalysis['threats_detected']) {
            // Process threat detection
            $threatDetectionService->processThreatDetection($threatAnalysis, $request, $clientId);
            
            // Block immediately for critical threats
            if ($threatAnalysis['severity'] === 'critical' || $threatAnalysis['risk_score'] >= 80) {
                $this->blockIpTemporarily($ip, 'critical_threat_detected', 3600);
                return $this->blockedResponse($ip, 'Critical security threat detected');
            }
            
            // Return warning for high-risk requests
            if ($threatAnalysis['severity'] === 'high' || $threatAnalysis['risk_score'] >= 50) {
                return $this->suspiciousActivityResponse($threatAnalysis);
            }
        }
        
        // Check for suspicious activity patterns
        $securityService = app(SecurityService::class);
        if ($securityService->detectSuspiciousActivity($request, $clientId)) {
            return $this->suspiciousActivityResponse(['message' => 'Suspicious activity detected']);
        }
        
        return $next($request);
    }
    
    /**
     * Check if IP is blocked
     */
    private function isIpBlocked(string $ip): bool
    {
        // Check temporary blocks first (faster)
        if (Cache::has("blocked_ip:{$ip}")) {
            return true;
        }
        
        // Check permanent blacklist
        return DB::table('ip_blacklist')
            ->where('ip_address', $ip)
            ->where(function($query) {
                $query->where('is_permanent', true)
                      ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }
    
    /**
     * Block IP temporarily
     */
    private function blockIpTemporarily(string $ip, string $reason, int $duration = 3600): void
    {
        Cache::put("blocked_ip:{$ip}", $reason, $duration);
        
        // Log the block
        DB::table('security_logs')->insert([
            'event_type' => 'ip_blocked_temporary',
            'ip_address' => $ip,
            'user_agent' => request()->userAgent(),
            'endpoint' => request()->path(),
            'data' => json_encode([
                'reason' => $reason,
                'duration' => $duration,
                'expires_at' => now()->addSeconds($duration)->toISOString()
            ]),
            'severity' => 'high',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    
    /**
     * Return blocked response
     */
    private function blockedResponse(string $ip, string $reason = 'IP address blocked'): JsonResponse
    {
        // Log the blocked attempt
        DB::table('security_logs')->insert([
            'event_type' => 'blocked_ip_attempt',
            'ip_address' => $ip,
            'user_agent' => request()->userAgent(),
            'endpoint' => request()->path(),
            'data' => json_encode(['reason' => $reason]),
            'severity' => 'medium',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        return response()->json([
            'error' => 'Access Denied',
            'message' => 'Your IP address has been blocked due to security violations.',
            'code' => 'IP_BLOCKED',
            'timestamp' => now()->toISOString()
        ], 403);
    }
    
    /**
     * Return suspicious activity response
     */
    private function suspiciousActivityResponse(array $details): JsonResponse
    {
        return response()->json([
            'error' => 'Suspicious Activity',
            'message' => 'Suspicious activity detected. Request blocked for security.',
            'code' => 'SUSPICIOUS_ACTIVITY',
            'details' => $details['threats'] ?? $details,
            'timestamp' => now()->toISOString()
        ], 403);
    }
}