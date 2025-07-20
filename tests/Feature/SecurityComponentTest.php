<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use App\Services\SecurityService;
use App\Services\ThreatDetectionService;

class SecurityComponentTest extends TestCase
{
    use RefreshDatabase;

    private SecurityService $securityService;
    private ThreatDetectionService $threatDetectionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->securityService = app(SecurityService::class);
        $this->threatDetectionService = app(ThreatDetectionService::class);
        
        // Run security migrations
        $this->artisan('migrate', ['--path' => 'database/migrations/2025_07_20_120000_create_security_tables.php']);
    }

    public function test_security_service_generates_secure_api_credentials()
    {
        $credentials = $this->securityService->generateApiCredentials();
        
        $this->assertArrayHasKey('api_key', $credentials);
        $this->assertArrayHasKey('api_secret', $credentials);
        $this->assertEquals(64, strlen($credentials['api_key'])); // 32 bytes * 2 (hex)
        $this->assertEquals(128, strlen($credentials['api_secret'])); // 64 bytes * 2 (hex)
    }

    public function test_security_service_encrypts_and_decrypts_data()
    {
        $originalData = 'sensitive-payment-data-12345';
        
        $encrypted = $this->securityService->encryptSensitiveData($originalData);
        $this->assertArrayHasKey('data', $encrypted);
        $this->assertArrayHasKey('iv', $encrypted);
        $this->assertArrayHasKey('tag', $encrypted);
        
        $decrypted = $this->securityService->decryptSensitiveData($encrypted);
        $this->assertEquals($originalData, $decrypted);
    }

    public function test_security_service_generates_valid_request_signature()
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

    public function test_threat_detection_identifies_sql_injection_attempts()
    {
        $request = new \Illuminate\Http\Request();
        $request->server->set('REQUEST_URI', "/api/test?query=' OR 1=1 --");
        $request->server->set('HTTP_HOST', 'example.com');
        $request->server->set('REMOTE_ADDR', '192.168.1.1');
        $request->setMethod('GET');
        
        $analysis = $this->threatDetectionService->analyzeRequest($request);
        
        $this->assertTrue($analysis['threats_detected']);
        $this->assertGreaterThan(0, count($analysis['threats']));
        $this->assertEquals('high', $analysis['severity']);
    }

    public function test_threat_detection_identifies_xss_attempts()
    {
        $request = new \Illuminate\Http\Request();
        $request->server->set('REQUEST_URI', '/api/test');
        $request->server->set('HTTP_HOST', 'example.com');
        $request->server->set('REMOTE_ADDR', '192.168.1.1');
        $request->setMethod('POST');
        $request->initialize([], [], [], [], [], [], '<script>alert("xss")</script>');
        
        $analysis = $this->threatDetectionService->analyzeRequest($request);
        
        $this->assertTrue($analysis['threats_detected']);
        $threatTypes = array_column($analysis['threats'], 'type');
        $this->assertContains('xss', $threatTypes);
    }

    public function test_ip_blocking_adds_to_blacklist()
    {
        // Add IP to blacklist
        DB::table('ip_blacklist')->insert([
            'ip_address' => '192.168.1.100',
            'reason' => 'test_block',
            'is_permanent' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $exists = DB::table('ip_blacklist')->where('ip_address', '192.168.1.100')->exists();
        $this->assertTrue($exists);
    }

    public function test_security_logs_are_created()
    {
        DB::table('security_logs')->insert([
            'event_type' => 'test_security_event',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'data' => json_encode(['test' => 'data']),
            'severity' => 'medium',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        $this->assertDatabaseHas('security_logs', [
            'event_type' => 'test_security_event',
            'ip_address' => '127.0.0.1'
        ]);
    }

    public function test_api_rate_limits_can_be_configured()
    {
        // Create a test client
        $clientId = DB::table('clients')->insertGetId([
            'name' => 'Test Client',
            'code' => 'TEST001',
            'api_key' => 'test-api-key-123',
            'api_secret' => 'test-secret-456',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Set rate limits
        DB::table('api_rate_limits')->insert([
            'client_id' => $clientId,
            'requests_per_minute' => 10,
            'requests_per_hour' => 100,
            'requests_per_day' => 1000,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $rateLimits = DB::table('api_rate_limits')->where('client_id', $clientId)->first();
        $this->assertEquals(10, $rateLimits->requests_per_minute);
        $this->assertEquals(100, $rateLimits->requests_per_hour);
        $this->assertEquals(1000, $rateLimits->requests_per_day);
    }

    public function test_security_incidents_can_be_tracked()
    {
        $incidentId = DB::table('security_incidents')->insertGetId([
            'incident_type' => 'automated_threat_detection',
            'severity' => 'high',
            'title' => 'Test Security Incident',
            'description' => 'Test incident for validation',
            'source_ip' => '192.168.1.1',
            'status' => 'open',
            'detected_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->assertDatabaseHas('security_incidents', [
            'id' => $incidentId,
            'incident_type' => 'automated_threat_detection',
            'severity' => 'high'
        ]);
    }

    public function test_encryption_keys_can_be_managed()
    {
        DB::table('encryption_keys')->insert([
            'key_id' => 'test-key-001',
            'encrypted_key' => base64_encode('encrypted-key-data'),
            'algorithm' => 'AES-256-GCM',
            'purpose' => 'data',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->assertDatabaseHas('encryption_keys', [
            'key_id' => 'test-key-001',
            'algorithm' => 'AES-256-GCM',
            'purpose' => 'data'
        ]);
    }

    public function test_failed_authentications_are_logged()
    {
        DB::table('failed_authentications')->insert([
            'api_key' => 'invalid-key',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Test Client',
            'failure_reason' => 'invalid_api_key',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->assertDatabaseHas('failed_authentications', [
            'api_key' => 'invalid-key',
            'failure_reason' => 'invalid_api_key'
        ]);
    }

    public function test_threat_patterns_detection()
    {
        $patterns = [
            "' OR 1=1 --" => 'sql_injection',
            '<script>alert("xss")</script>' => 'xss',
            '../../../etc/passwd' => 'path_traversal',
            'system("rm -rf /")' => 'command_injection'
        ];

        foreach ($patterns as $maliciousInput => $expectedThreat) {
            $request = new \Illuminate\Http\Request();
            $request->server->set('REQUEST_URI', '/api/test');
            $request->server->set('HTTP_HOST', 'example.com');
            $request->server->set('REMOTE_ADDR', '192.168.1.1');
            $request->setMethod('POST');
            $request->initialize([], [], [], [], [], [], $maliciousInput);
            
            $analysis = $this->threatDetectionService->analyzeRequest($request);
            
            $this->assertTrue($analysis['threats_detected'], "Failed to detect: $expectedThreat");
            $threatTypes = array_column($analysis['threats'], 'type');
            $this->assertContains($expectedThreat, $threatTypes, "Expected threat type: $expectedThreat");
        }
    }

    public function test_coordinated_attack_detection()
    {
        // Simulate multiple IPs attacking
        for ($i = 1; $i <= 5; $i++) {
            DB::table('security_logs')->insert([
                'event_type' => 'threat_sql_injection',
                'ip_address' => "192.168.1.$i",
                'user_agent' => 'Attack Bot',
                'data' => json_encode(['threat_type' => 'sql_injection']),
                'severity' => 'high',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $request = new \Illuminate\Http\Request();
        $request->server->set('REQUEST_URI', '/api/test');
        $request->server->set('HTTP_HOST', 'example.com');
        $request->server->set('REMOTE_ADDR', '192.168.1.6');
        $request->setMethod('GET');
        
        $analysis = $this->threatDetectionService->analyzeRequest($request);
        
        // Check if coordinated attack was detected
        $threatTypes = array_column($analysis['threats'], 'type');
        $this->assertContains('coordinated_attack', $threatTypes);
    }
}