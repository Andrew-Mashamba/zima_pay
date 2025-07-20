<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use App\Services\SecurityService;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    private SecurityService $securityService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->securityService = app(SecurityService::class);
        
        // Run security migrations
        $this->artisan('migrate', ['--path' => 'database/migrations/2025_07_20_120000_create_security_tables.php']);
    }

    /** @test */
    public function api_requires_authentication_headers()
    {
        // Apply auth middleware manually for testing
        $response = $this->withMiddleware([
            \App\Http\Middleware\ApiAuthentication::class
        ])->getJson('/api/esb/health');
        
        $this->assertEquals(401, $response->status());
        $this->assertStringContainsString('Missing required authentication headers', $response->content());
    }

    /** @test */
    public function api_rejects_invalid_timestamp()
    {
        $headers = [
            'X-API-Key' => 'test-api-key',
            'X-Timestamp' => time() - 1000, // 16+ minutes ago
            'X-Signature' => 'invalid-signature',
            'X-Nonce' => 'test-nonce'
        ];

        $response = $this->getJson('/api/esb/health', $headers);
        
        $this->assertEquals(401, $response->status());
        $this->assertStringContainsString('Invalid or expired timestamp', $response->content());
    }

    /** @test */
    public function api_rejects_reused_nonce()
    {
        $nonce = 'test-nonce-123';
        $apiKey = 'test-api-key';
        
        // Store nonce as if it was already used
        Cache::put("nonce:{$apiKey}:{$nonce}", true, 600);
        
        $headers = [
            'X-API-Key' => $apiKey,
            'X-Timestamp' => time(),
            'X-Signature' => 'test-signature',
            'X-Nonce' => $nonce
        ];

        $response = $this->getJson('/api/esb/health', $headers);
        
        $this->assertEquals(401, $response->status());
        $this->assertStringContainsString('Nonce already used', $response->content());
    }

    /** @test */
    public function ip_blocking_middleware_blocks_blacklisted_ips()
    {
        // Add IP to blacklist
        DB::table('ip_blacklist')->insert([
            'ip_address' => '192.168.1.100',
            'reason' => 'test_block',
            'is_permanent' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.100'])
                         ->getJson('/api/esb/health');
        
        $this->assertEquals(403, $response->status());
        $this->assertStringContainsString('IP address has been blocked', $response->content());
    }

    /** @test */
    public function security_service_generates_secure_api_credentials()
    {
        $credentials = $this->securityService->generateApiCredentials();
        
        $this->assertArrayHasKey('api_key', $credentials);
        $this->assertArrayHasKey('api_secret', $credentials);
        $this->assertEquals(64, strlen($credentials['api_key'])); // 32 bytes * 2 (hex)
        $this->assertEquals(128, strlen($credentials['api_secret'])); // 64 bytes * 2 (hex)
    }

    /** @test */
    public function security_service_encrypts_and_decrypts_data()
    {
        $originalData = 'sensitive-payment-data-12345';
        
        $encrypted = $this->securityService->encryptSensitiveData($originalData);
        $this->assertArrayHasKey('data', $encrypted);
        $this->assertArrayHasKey('iv', $encrypted);
        $this->assertArrayHasKey('tag', $encrypted);
        
        $decrypted = $this->securityService->decryptSensitiveData($encrypted);
        $this->assertEquals($originalData, $decrypted);
    }

    /** @test */
    public function security_service_generates_valid_request_signature()
    {
        $method = 'POST';
        $uri = '/api/esb/test';
        $body = '{"amount": 1000, "phone": "255123456789"}';
        $secret = 'test-secret-key';
        $timestamp = time();
        $nonce = 'test-nonce';
        
        $signature = $this->securityService->generateRequestSignature(
            $method, $uri, $body, $secret, $timestamp, $nonce
        );
        
        $this->assertIsString($signature);
        $this->assertEquals(64, strlen($signature)); // SHA256 hash length
    }

    /** @test */
    public function threat_detection_identifies_sql_injection_attempts()
    {
        $maliciousData = "'; DROP TABLE users; --";
        
        $request = $this->createMock(\Illuminate\Http\Request::class);
        $request->method('fullUrl')->willReturn("http://example.com/api/test?query={$maliciousData}");
        $request->method('headers')->willReturn(collect([]));
        $request->method('getContent')->willReturn('');
        $request->method('ip')->willReturn('192.168.1.1');
        $request->method('path')->willReturn('/api/test');
        
        $threatDetection = app(\App\Services\ThreatDetectionService::class);
        $analysis = $threatDetection->analyzeRequest($request);
        
        $this->assertTrue($analysis['threats_detected']);
        $this->assertGreaterThan(0, count($analysis['threats']));
        $this->assertEquals('high', $analysis['severity']);
    }

    /** @test */
    public function rate_limiting_blocks_excessive_requests()
    {
        // Create a test client
        $client = DB::table('clients')->insertGetId([
            'name' => 'Test Client',
            'api_key' => 'test-api-key-123',
            'api_secret' => 'test-secret-456',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Set a low rate limit for testing
        Cache::put("client_limit:{$client}:per_minute", 5, 60);

        $headers = [
            'X-API-Key' => 'test-api-key-123',
            'X-Timestamp' => time(),
            'X-Signature' => 'test-signature',
            'X-Nonce' => 'unique-nonce-' . uniqid()
        ];

        // This should trigger rate limiting
        for ($i = 0; $i < 7; $i++) {
            $headers['X-Nonce'] = 'unique-nonce-' . uniqid();
            $response = $this->getJson('/api/esb/health', $headers);
        }

        $this->assertEquals(429, $response->status());
        $this->assertStringContainsString('Rate Limit Exceeded', $response->content());
    }

    /** @test */
    public function security_logs_are_created_for_events()
    {
        // Trigger a security event
        $this->getJson('/api/esb/health');
        
        // Check that security log was created
        $this->assertDatabaseHas('security_logs', [
            'event_type' => 'api_authentication_failed'
        ]);
    }

    /** @test */
    public function blocked_ips_cannot_access_api()
    {
        $ip = '10.0.0.1';
        
        // Block IP temporarily
        Cache::put("blocked_ip:{$ip}", 'test_block', 3600);
        
        $response = $this->withServerVariables(['REMOTE_ADDR' => $ip])
                         ->getJson('/api/esb/health');
        
        $this->assertEquals(403, $response->status());
        $this->assertStringContainsString('Access Denied', $response->content());
    }
}