<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ThreatDetectionService
{
    private array $threatPatterns;
    private array $suspiciousPatterns;
    
    public function __construct()
    {
        $this->threatPatterns = [
            'sql_injection' => [
                'union select', 'drop table', 'delete from', 'insert into',
                '1=1', '1\'=\'1', 'or 1=1', 'and 1=1', '--', 
                'exec(', 'execute(', 'sp_', 'xp_', 'cmd', 'shell',
                // More specific SQL injection patterns
                'union all select', 'union distinct select',
                'drop database', 'truncate table',
                'xp_cmdshell', 'sp_executesql'
            ],
            'xss' => [
                '<script', '</script>', 'javascript:', 'onclick=', 'onerror=',
                'onload=', 'alert(', 'document.cookie', 'eval(', 'expression('
            ],
            'path_traversal' => [
                '../', '..\\', '..\/', '..%2f', '..%5c', '%2e%2e%2f',
                '/etc/passwd', '/etc/hosts', 'c:\\windows', '\\windows\\'
            ],
            'command_injection' => [
                '|', '&&', '||', '`', '$(', '${', 'exec',
                'system(', 'shell_exec(', 'passthru(', 'eval(',
                // More specific command injection patterns
                '; rm ', '; ls ', '; cat ', '; wget ', '; curl ',
                '| rm ', '| ls ', '| cat ', '| wget ', '| curl ',
                '&& rm ', '&& ls ', '&& cat ', '&& wget ', '&& curl '
            ]
        ];
        
        $this->suspiciousPatterns = [
            'scanner_signatures' => [
                'nmap', 'masscan', 'zap', 'burp', 'sqlmap', 'nikto',
                'dirb', 'dirbuster', 'gobuster', 'wfuzz', 'ffuf'
            ],
            'bot_signatures' => [
                'bot', 'crawler', 'spider', 'scraper', 'scanner',
                'curl', 'wget', 'python-requests', 'go-http-client'
            ]
        ];
    }
    
    /**
     * Analyze incoming request for threats
     */
    public function analyzeRequest(Request $request, ?int $clientId = null): array
    {
        $threats = [];
        $severity = 'low';
        
        // Analyze URL for malicious patterns
        $urlThreats = $this->analyzeUrl($request->fullUrl());
        if (!empty($urlThreats)) {
            $threats = array_merge($threats, $urlThreats);
            $severity = 'high';
        }
        
        // Analyze request headers
        $headerThreats = $this->analyzeHeaders($request->headers->all());
        if (!empty($headerThreats)) {
            $threats = array_merge($threats, $headerThreats);
            $severity = max($severity, 'medium');
        }
        
        // Analyze request body
        $bodyThreats = $this->analyzeBody($request->getContent());
        if (!empty($bodyThreats)) {
            $threats = array_merge($threats, $bodyThreats);
            $severity = 'high';
        }
        
        // Analyze behavioral patterns
        $behaviorThreats = $this->analyzeBehavior($request, $clientId);
        if (!empty($behaviorThreats)) {
            $threats = array_merge($threats, $behaviorThreats);
            $severity = max($severity, 'medium');
        }
        
        // Check for coordinated attacks
        $coordinatedThreats = $this->detectCoordinatedAttack($request->ip());
        if (!empty($coordinatedThreats)) {
            $threats = array_merge($threats, $coordinatedThreats);
            $severity = 'critical';
        }
        
        return [
            'threats_detected' => !empty($threats),
            'threats' => $threats,
            'severity' => $severity,
            'risk_score' => $this->calculateRiskScore($threats)
        ];
    }
    
    /**
     * Analyze URL for malicious patterns
     */
    private function analyzeUrl(string $url): array
    {
        $threats = [];
        $decodedUrl = urldecode($url);
        
        foreach ($this->threatPatterns as $type => $patterns) {
            foreach ($patterns as $pattern) {
                if (stripos($decodedUrl, $pattern) !== false) {
                    $threats[] = [
                        'type' => $type,
                        'pattern' => $pattern,
                        'location' => 'url',
                        'evidence' => $this->extractEvidence($decodedUrl, $pattern)
                    ];
                }
            }
        }
        
        return $threats;
    }
    
    /**
     * Analyze request headers for threats
     */
    private function analyzeHeaders(array $headers): array
    {
        $threats = [];
        
        // Whitelist of common browser headers that may contain harmless patterns
        $browserHeaderWhitelist = [
            'accept', 'accept-language', 'accept-encoding', 'user-agent',
            'sec-ch-ua', 'sec-ch-ua-mobile', 'sec-ch-ua-platform',
            'sec-fetch-dest', 'sec-fetch-mode', 'sec-fetch-site',
            'cache-control', 'pragma', 'upgrade-insecure-requests',
            'cookie', 'referer', 'origin', 'host', 'connection'
        ];
        
        foreach ($headers as $name => $values) {
            $headerValue = is_array($values) ? implode(' ', $values) : $values;
            $headerNameLower = strtolower($name);
            
            // Skip analysis for whitelisted browser headers to prevent false positives
            if (in_array($headerNameLower, $browserHeaderWhitelist)) {
                continue;
            }
            
            // Check User-Agent for suspicious patterns
            if ($headerNameLower === 'user-agent') {
                foreach ($this->suspiciousPatterns['scanner_signatures'] as $signature) {
                    if (stripos($headerValue, $signature) !== false) {
                        $threats[] = [
                            'type' => 'suspicious_user_agent',
                            'pattern' => $signature,
                            'location' => 'headers',
                            'evidence' => $headerValue
                        ];
                    }
                }
            }
            
            // Check for injection attempts in non-whitelisted headers
            foreach ($this->threatPatterns as $type => $patterns) {
                foreach ($patterns as $pattern) {
                    if (stripos($headerValue, $pattern) !== false) {
                        $threats[] = [
                            'type' => $type,
                            'pattern' => $pattern,
                            'location' => "header:{$name}",
                            'evidence' => $this->extractEvidence($headerValue, $pattern)
                        ];
                    }
                }
            }
        }
        
        return $threats;
    }
    
    /**
     * Analyze request body for threats
     */
    private function analyzeBody(string $body): array
    {
        $threats = [];
        
        if (empty($body)) {
            return $threats;
        }
        
        // Decode various encoding formats
        $decodedBodies = [
            $body,
            urldecode($body),
            base64_decode($body, true) ?: '',
            html_entity_decode($body)
        ];
        
        foreach ($decodedBodies as $decodedBody) {
            if (empty($decodedBody)) continue;
            
            foreach ($this->threatPatterns as $type => $patterns) {
                foreach ($patterns as $pattern) {
                    if (stripos($decodedBody, $pattern) !== false) {
                        $threats[] = [
                            'type' => $type,
                            'pattern' => $pattern,
                            'location' => 'body',
                            'evidence' => $this->extractEvidence($decodedBody, $pattern)
                        ];
                    }
                }
            }
        }
        
        return $threats;
    }
    
    /**
     * Analyze behavioral patterns
     */
    private function analyzeBehavior(Request $request, ?int $clientId): array
    {
        $threats = [];
        $ip = $request->ip();
        
        // Check for rapid requests from same IP
        $requestKey = "request_frequency:{$ip}";
        $requestCount = Cache::get($requestKey, 0);
        Cache::put($requestKey, $requestCount + 1, 60);
        
        if ($requestCount > 100) { // More than 100 requests per minute
            $threats[] = [
                'type' => 'rapid_requests',
                'pattern' => 'high_frequency',
                'location' => 'behavioral',
                'evidence' => "requests per minute: {$requestCount}"
            ];
        }
        
        // Check for unusual endpoints access pattern
        $endpointKey = "endpoint_access:{$ip}";
        $endpoints = Cache::get($endpointKey, []);
        $endpoints[] = $request->path();
        Cache::put($endpointKey, array_slice($endpoints, -50), 300); // Keep last 50, 5 minutes
        
        $uniqueEndpoints = array_unique($endpoints);
        if (count($uniqueEndpoints) > 20) { // Accessing more than 20 different endpoints
            $threats[] = [
                'type' => 'endpoint_enumeration',
                'pattern' => 'multiple_endpoints',
                'location' => 'behavioral',
                'evidence' => "unique endpoints accessed: " . count($uniqueEndpoints)
            ];
        }
        
        // Check for error rate patterns
        $this->checkErrorPatterns($ip, $threats);
        
        return $threats;
    }
    
    /**
     * Check for error rate patterns that might indicate attacks
     */
    private function checkErrorPatterns(string $ip, array &$threats): void
    {
        $errorKey = "error_rate:{$ip}";
        $errors = Cache::get($errorKey, []);
        
        // Count recent errors (last 10 minutes)
        $recentErrors = array_filter($errors, function($timestamp) {
            return (time() - $timestamp) < 600;
        });
        
        if (count($recentErrors) > 10) { // More than 10 errors in 10 minutes
            $threats[] = [
                'type' => 'high_error_rate',
                'pattern' => 'multiple_errors',
                'location' => 'behavioral',
                'evidence' => "errors in 10 minutes: " . count($recentErrors)
            ];
        }
    }
    
    /**
     * Detect coordinated attacks from multiple IPs
     */
    private function detectCoordinatedAttack(string $ip): array
    {
        $threats = [];
        
        // Check if multiple IPs are performing similar attacks
        $attackKey = "attack_pattern:*";
        $attackingIps = [];
        
        // This would typically use Redis SCAN for better performance
        // For now, we'll check recent security logs
        $recentAttacks = DB::table('security_logs')
            ->where('event_type', 'LIKE', 'threat_%')
            ->where('created_at', '>=', Carbon::now()->subMinutes(30))
            ->groupBy('ip_address')
            ->havingRaw('COUNT(*) > 5')
            ->pluck('ip_address');
        
        if ($recentAttacks->count() > 3) { // 3 or more IPs attacking simultaneously
            $threats[] = [
                'type' => 'coordinated_attack',
                'pattern' => 'multiple_sources',
                'location' => 'network',
                'evidence' => "attacking IPs: " . $recentAttacks->count()
            ];
        }
        
        return $threats;
    }
    
    /**
     * Calculate risk score based on threats
     */
    private function calculateRiskScore(array $threats): int
    {
        $score = 0;
        
        foreach ($threats as $threat) {
            $score += match($threat['type']) {
                'sql_injection' => 20,
                'command_injection' => 20,
                'xss' => 15,
                'path_traversal' => 18,
                'coordinated_attack' => 25,
                'rapid_requests' => 10,
                'endpoint_enumeration' => 12,
                'high_error_rate' => 8,
                'suspicious_user_agent' => 5,
                default => 5
            };
        }
        
        return min($score, 100); // Cap at 100
    }
    
    /**
     * Extract evidence around the matched pattern
     */
    private function extractEvidence(string $text, string $pattern): string
    {
        $pos = stripos($text, $pattern);
        if ($pos === false) return '';
        
        $start = max(0, $pos - 20);
        $length = min(strlen($text) - $start, 60);
        
        return substr($text, $start, $length);
    }
    
    /**
     * Process threat detection results
     */
    public function processThreatDetection(array $analysis, Request $request, ?int $clientId = null): void
    {
        if (!$analysis['threats_detected']) {
            return;
        }
        
        $incidentId = $this->createSecurityIncident($analysis, $request, $clientId);
        
        // Log all threats
        foreach ($analysis['threats'] as $threat) {
            $this->logThreat($threat, $request, $clientId, $incidentId);
        }
        
        // Take automated actions based on severity
        $this->takeAutomatedActions($analysis, $request);
        
        // Alert security team for high/critical threats
        if (in_array($analysis['severity'], ['high', 'critical'])) {
            $this->alertSecurityTeam($analysis, $request, $incidentId);
        }
    }
    
    /**
     * Create security incident record
     */
    private function createSecurityIncident(array $analysis, Request $request, ?int $clientId): int
    {
        $threatTypes = array_unique(array_column($analysis['threats'], 'type'));
        
        return DB::table('security_incidents')->insertGetId([
            'incident_type' => 'automated_threat_detection',
            'severity' => $analysis['severity'],
            'title' => 'Automated Threat Detection: ' . implode(', ', $threatTypes),
            'description' => 'Multiple threats detected in incoming request',
            'source_ip' => $request->ip(),
            'client_id' => $clientId,
            'affected_systems' => json_encode(['api']),
            'attack_vectors' => json_encode($threatTypes),
            'detected_at' => now(),
            'indicators_of_compromise' => json_encode([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'endpoint' => $request->path(),
                'threats' => $analysis['threats']
            ]),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    
    /**
     * Log individual threat
     */
    private function logThreat(array $threat, Request $request, ?int $clientId, int $incidentId): void
    {
        DB::table('security_logs')->insert([
            'event_type' => "threat_{$threat['type']}",
            'client_id' => $clientId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'endpoint' => $request->path(),
            'data' => json_encode([
                'threat' => $threat,
                'incident_id' => $incidentId,
                'request_method' => $request->method(),
                'request_uri' => $request->getRequestUri()
            ]),
            'severity' => $this->getThreatSeverity($threat['type']),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    
    /**
     * Get threat severity level
     */
    private function getThreatSeverity(string $threatType): string
    {
        return match($threatType) {
            'sql_injection', 'command_injection' => 'critical',
            'xss', 'path_traversal', 'coordinated_attack' => 'high',
            'rapid_requests', 'endpoint_enumeration' => 'medium',
            default => 'low'
        };
    }
    
    /**
     * Take automated actions based on threat analysis
     */
    private function takeAutomatedActions(array $analysis, Request $request): void
    {
        $ip = $request->ip();
        
        // Skip IP blocking for payment link access to allow customers from different areas
        $isPaymentLinkAccess = str_contains($request->path(), '/pay/');
        
        if (!$isPaymentLinkAccess) {
            // Block IP for critical threats (only for non-payment link access)
            if ($analysis['severity'] === 'critical' || $analysis['risk_score'] >= 50) {
                Cache::put("blocked_ip:{$ip}", 'automated_threat_detection', 3600);
                
                Log::critical("IP {$ip} automatically blocked due to threat detection", [
                    'threats' => $analysis['threats'],
                    'risk_score' => $analysis['risk_score']
                ]);
            }
        } else {
            // For payment link access, only log threats but don't block IPs
            Log::warning("Threat detected on payment link access (IP not blocked)", [
                'ip' => $ip,
                'threats' => $analysis['threats'],
                'risk_score' => $analysis['risk_score'],
                'path' => $request->path()
            ]);
        }
        
        // Increase monitoring for high-risk IPs (but don't block for payment links)
        if ($analysis['risk_score'] >= 30 && !$isPaymentLinkAccess) {
            Cache::put("monitor_ip:{$ip}", true, 7200); // Monitor for 2 hours
        }
    }
    
    /**
     * Alert security team for critical threats
     */
    private function alertSecurityTeam(array $analysis, Request $request, int $incidentId): void
    {
        $alertData = [
            'incident_id' => $incidentId,
            'severity' => $analysis['severity'],
            'ip' => $request->ip(),
            'threats' => $analysis['threats'],
            'risk_score' => $analysis['risk_score'],
            'timestamp' => now()->toISOString()
        ];
        
        // Log critical alert
        Log::critical('Security threat detected - immediate attention required', $alertData);
        
        // Send email alert (implement based on your mail configuration)
        // Mail::to(config('security.alert_emails'))->send(new ThreatDetectionAlert($alertData));
        
        // Send to monitoring system (Slack, PagerDuty, etc.)
        $this->sendToMonitoringSystem($alertData);
    }
    
    /**
     * Send alert to external monitoring system
     */
    private function sendToMonitoringSystem(array $alertData): void
    {
        // Implementation depends on your monitoring setup
        // This could be Slack webhook, PagerDuty API, etc.
        
        try {
            // Example: Send to webhook
            $webhookUrl = config('security.alert_webhook');
            if ($webhookUrl) {
                $ch = curl_init($webhookUrl);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($alertData));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_exec($ch);
                curl_close($ch);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send security alert to monitoring system', [
                'error' => $e->getMessage(),
                'alert_data' => $alertData
            ]);
        }
    }
}