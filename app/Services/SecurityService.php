<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SecurityService
{
    /**
     * Generate API key and secret for client
     */
    public function generateApiCredentials(): array
    {
        return [
            'api_key' => $this->generateSecureKey(32),
            'api_secret' => $this->generateSecureKey(64)
        ];
    }
    
    /**
     * Generate cryptographically secure key
     */
    private function generateSecureKey(int $length): string
    {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Encrypt sensitive data using AES-256-GCM
     */
    public function encryptSensitiveData(string $data, ?string $key = null): array
    {
        $key = $key ?? config('app.encryption_key', env('APP_KEY'));
        $key = hash('sha256', $key, true); // Ensure 32-byte key
        
        $iv = random_bytes(12); // 96-bit IV for GCM
        $tag = '';
        
        $encrypted = openssl_encrypt(
            $data,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        
        if ($encrypted === false) {
            throw new \Exception('Encryption failed');
        }
        
        return [
            'data' => base64_encode($encrypted),
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag)
        ];
    }
    
    /**
     * Decrypt sensitive data
     */
    public function decryptSensitiveData(array $encryptedData, ?string $key = null): string
    {
        $key = $key ?? config('app.encryption_key', env('APP_KEY'));
        $key = hash('sha256', $key, true);
        
        $decrypted = openssl_decrypt(
            base64_decode($encryptedData['data']),
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            base64_decode($encryptedData['iv']),
            base64_decode($encryptedData['tag'])
        );
        
        if ($decrypted === false) {
            throw new \Exception('Decryption failed');
        }
        
        return $decrypted;
    }
    
    /**
     * Generate request signature for client
     */
    public function generateRequestSignature(string $method, string $uri, string $body, string $secret, string $timestamp, string $nonce): string
    {
        $canonicalString = implode("\n", [
            strtoupper($method),
            $uri,
            'application/json',
            $timestamp,
            $nonce,
            hash('sha256', $body)
        ]);
        
        return hash_hmac('sha256', $canonicalString, $secret);
    }
    
    /**
     * Detect and block suspicious activity
     */
    public function detectSuspiciousActivity(Request $request, ?int $clientId = null): bool
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        
        // Check for rapid-fire requests from same IP
        if ($this->isFloodAttack($ip)) {
            $this->blockIp($ip, 'flood_attack');
            return true;
        }
        
        // Check for suspicious user agents
        if ($this->isSuspiciousUserAgent($userAgent)) {
            $this->logThreat('suspicious_user_agent', $ip, ['user_agent' => $userAgent]);
            return true;
        }
        
        // Check for known malicious IPs
        if ($this->isMaliciousIp($ip)) {
            $this->blockIp($ip, 'malicious_ip');
            return true;
        }
        
        // Check for unusual patterns for specific client
        if ($clientId && $this->isUnusualClientActivity($clientId, $ip)) {
            $this->logThreat('unusual_client_activity', $ip, ['client_id' => $clientId]);
            return true;
        }
        
        return false;
    }
    
    /**
     * Check for flood attacks
     */
    private function isFloodAttack(string $ip): bool
    {
        $key = "flood_check:{$ip}";
        $attempts = Cache::get($key, 0);
        
        // Allow max 100 requests per minute
        if ($attempts > 100) {
            return true;
        }
        
        Cache::put($key, $attempts + 1, 60);
        return false;
    }
    
    /**
     * Check for suspicious user agents
     */
    private function isSuspiciousUserAgent(?string $userAgent): bool
    {
        if (!$userAgent) {
            return true;
        }
        
        $suspiciousPatterns = [
            'curl',
            'wget',
            'python',
            'bot',
            'scanner',
            'crawl',
            'nikto',
            'sqlmap',
            'nmap'
        ];
        
        $userAgent = strtolower($userAgent);
        
        foreach ($suspiciousPatterns as $pattern) {
            if (strpos($userAgent, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check against malicious IP database
     */
    private function isMaliciousIp(string $ip): bool
    {
        // Check local blacklist
        $blacklisted = Cache::remember("blacklist:{$ip}", 3600, function() use ($ip) {
            return DB::table('ip_blacklist')->where('ip_address', $ip)->exists();
        });
        
        return $blacklisted;
    }
    
    /**
     * Check for unusual client activity patterns
     */
    private function isUnusualClientActivity(int $clientId, string $ip): bool
    {
        // Get client's usual IPs
        $usualIps = Cache::remember("client_ips:{$clientId}", 1800, function() use ($clientId) {
            return DB::table('security_logs')
                ->where('client_id', $clientId)
                ->where('event_type', 'api_authentication_success')
                ->where('created_at', '>=', now()->subDays(7))
                ->distinct()
                ->pluck('ip_address')
                ->toArray();
        });
        
        // If client has never used this IP and has established patterns
        if (count($usualIps) > 0 && !in_array($ip, $usualIps)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Block IP address
     */
    private function blockIp(string $ip, string $reason): void
    {
        // Add to temporary blacklist
        Cache::put("blocked_ip:{$ip}", $reason, 3600); // Block for 1 hour
        
        // Log the block
        $this->logThreat('ip_blocked', $ip, ['reason' => $reason]);
        
        // Add to permanent blacklist if repeated offenses
        $offenses = DB::table('security_logs')
            ->where('ip_address', $ip)
            ->where('event_type', 'LIKE', '%threat%')
            ->where('created_at', '>=', now()->subDay())
            ->count();
            
        if ($offenses >= 5) {
            DB::table('ip_blacklist')->updateOrInsert(
                ['ip_address' => $ip],
                [
                    'reason' => $reason,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }
    }
    
    /**
     * Log security threats
     */
    private function logThreat(string $threatType, string $ip, array $data = []): void
    {
        DB::table('security_logs')->insert([
            'event_type' => "threat_{$threatType}",
            'ip_address' => $ip,
            'user_agent' => request()->userAgent(),
            'data' => json_encode($data),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Log to Laravel log for immediate alerting
        Log::warning("Security threat detected: {$threatType}", [
            'ip' => $ip,
            'data' => $data
        ]);
    }
    
    /**
     * Generate secure nonce
     */
    public function generateNonce(): string
    {
        return bin2hex(random_bytes(16));
    }
    
    /**
     * Validate data integrity using checksums
     */
    public function validateDataIntegrity(array $data, string $expectedChecksum): bool
    {
        $actualChecksum = hash('sha256', json_encode($data, JSON_SORT_KEYS));
        return hash_equals($expectedChecksum, $actualChecksum);
    }
    
    /**
     * Sanitize input data to prevent injection attacks
     */
    public function sanitizeInput(array $data): array
    {
        array_walk_recursive($data, function(&$value) {
            if (is_string($value)) {
                // Remove potential XSS vectors
                $value = strip_tags($value);
                
                // Remove potential SQL injection vectors
                $value = str_replace(['--', ';', '/*', '*/', 'xp_', 'sp_'], '', $value);
                
                // Trim whitespace
                $value = trim($value);
            }
        });
        
        return $data;
    }
    
    /**
     * Generate audit trail hash
     */
    public function generateAuditHash(array $data): string
    {
        $canonicalData = json_encode($data, JSON_UNESCAPED_SLASHES);
        return hash('sha256', $canonicalData . config('app.key'));
    }
    
    /**
     * Check if IP is blocked
     */
    public function isIpBlocked(string $ip): bool
    {
        return Cache::has("blocked_ip:{$ip}") || 
               DB::table('ip_blacklist')->where('ip_address', $ip)->exists();
    }
}