<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\SecurityService;
use App\Services\ThreatDetectionService;
use Illuminate\Support\Facades\DB;

class SecurityTestSuite extends TestCase
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

    public function test_security_service_generates_secure_api_credentials()
    {
        $credentials = $this->securityService->generateApiCredentials();
        
        $this->assertArrayHasKey('api_key', $credentials);
        $this->assertArrayHasKey('api_secret', $credentials);
        $this->assertEquals(64, strlen($credentials['api_key'])); // 32 bytes * 2 (hex)
        $this->assertEquals(128, strlen($credentials['api_secret'])); // 64 bytes * 2 (hex)
        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $credentials['api_key']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $credentials['api_secret']);
    }

    public function test_security_service_encrypts_and_decrypts_data_successfully()
    {
        $testData = [
            'short_string' => 'test',
            'long_string' => str_repeat('A', 1000),
            'special_chars' => '!@#$%^&*()_+-=[]{}|;:,.<>?',
            'json_data' => '{"user_id": 123, "amount": 50000}',
            'unicode' => 'Hàló Wörlñ 🌍',
        ];

        foreach ($testData as $label => $originalData) {
            $encrypted = $this->securityService->encryptSensitiveData($originalData);
            
            // Verify encrypted data structure
            $this->assertArrayHasKey('data', $encrypted, "Failed for: $label");
            $this->assertArrayHasKey('iv', $encrypted, "Failed for: $label");
            $this->assertArrayHasKey('tag', $encrypted, "Failed for: $label");
            
            // Verify base64 encoding
            $this->assertNotFalse(base64_decode($encrypted['data'], true), "Invalid base64 data for: $label");
            $this->assertNotFalse(base64_decode($encrypted['iv'], true), "Invalid base64 IV for: $label");
            $this->assertNotFalse(base64_decode($encrypted['tag'], true), "Invalid base64 tag for: $label");
            
            // Test decryption
            $decrypted = $this->securityService->decryptSensitiveData($encrypted);
            $this->assertEquals($originalData, $decrypted, "Decryption failed for: $label");
        }
    }

    public function test_security_service_generates_consistent_request_signatures()
    {
        $testCases = [
            [
                'method' => 'GET',
                'uri' => '/api/esb/health',
                'body' => '',
                'secret' => 'test-secret-key',
                'timestamp' => '1640995200',
                'nonce' => 'test-nonce-123'
            ],
            [
                'method' => 'POST',
                'uri' => '/api/esb/payment',
                'body' => '{"amount": 1000, "phone": "255123456789"}',
                'secret' => 'different-secret',
                'timestamp' => '1640995300',
                'nonce' => 'different-nonce-456'
            ]
        ];

        foreach ($testCases as $index => $testCase) {
            $signature1 = $this->securityService->generateRequestSignature(
                $testCase['method'],
                $testCase['uri'],
                $testCase['body'],
                $testCase['secret'],
                $testCase['timestamp'],
                $testCase['nonce']
            );

            $signature2 = $this->securityService->generateRequestSignature(
                $testCase['method'],
                $testCase['uri'],
                $testCase['body'],
                $testCase['secret'],
                $testCase['timestamp'],
                $testCase['nonce']
            );

            $this->assertEquals($signature1, $signature2, "Signatures not consistent for test case $index");
            $this->assertEquals(64, strlen($signature1), "Invalid signature length for test case $index");
            $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $signature1, "Invalid signature format for test case $index");
        }
    }

    public function test_security_database_tables_exist_and_functional()
    {
        // Test security_logs table
        DB::table('security_logs')->insert([
            'event_type' => 'test_event',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'endpoint' => '/api/test',
            'data' => json_encode(['test' => 'data']),
            'severity' => 'medium',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->assertDatabaseHas('security_logs', [
            'event_type' => 'test_event',
            'ip_address' => '127.0.0.1'
        ]);

        // Test ip_blacklist table
        DB::table('ip_blacklist')->insert([
            'ip_address' => '192.168.1.100',
            'reason' => 'test_block',
            'is_permanent' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->assertDatabaseHas('ip_blacklist', [
            'ip_address' => '192.168.1.100',
            'reason' => 'test_block'
        ]);

        // Test security_incidents table
        DB::table('security_incidents')->insert([
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
            'incident_type' => 'automated_threat_detection',
            'severity' => 'high'
        ]);
    }

    public function test_api_rate_limits_configuration()
    {
        // Create test client
        $clientId = DB::table('clients')->insertGetId([
            'name' => 'Test Security Client',
            'code' => 'SEC001',
            'api_key' => 'security-test-key-' . uniqid(),
            'api_secret' => 'security-test-secret-' . uniqid(),
            'status' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Configure rate limits
        DB::table('api_rate_limits')->insert([
            'client_id' => $clientId,
            'endpoint' => null, // Global limit
            'requests_per_minute' => 60,
            'requests_per_hour' => 3600,
            'requests_per_day' => 86400,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Endpoint-specific limits
        DB::table('api_rate_limits')->insert([
            'client_id' => $clientId,
            'endpoint' => 'api/v1/transactions',
            'requests_per_minute' => 10,
            'requests_per_hour' => 100,
            'requests_per_day' => 1000,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $globalLimits = DB::table('api_rate_limits')
            ->where('client_id', $clientId)
            ->whereNull('endpoint')
            ->first();

        $endpointLimits = DB::table('api_rate_limits')
            ->where('client_id', $clientId)
            ->where('endpoint', 'api/v1/transactions')
            ->first();

        $this->assertEquals(60, $globalLimits->requests_per_minute);
        $this->assertEquals(3600, $globalLimits->requests_per_hour);
        $this->assertEquals(10, $endpointLimits->requests_per_minute);
        $this->assertEquals(100, $endpointLimits->requests_per_hour);
    }

    public function test_security_incident_management()
    {
        $incidentData = [
            'incident_type' => 'coordinated_attack',
            'severity' => 'critical',
            'title' => 'Multiple IP Coordinated Attack',
            'description' => 'Detected coordinated attack from multiple IP addresses',
            'source_ip' => '10.0.0.1',
            'affected_systems' => json_encode(['api', 'dashboard']),
            'attack_vectors' => json_encode(['sql_injection', 'brute_force']),
            'status' => 'open',
            'detected_at' => now(),
            'indicators_of_compromise' => json_encode([
                'pattern' => 'rapid_requests',
                'frequency' => 'high',
                'geographic_anomaly' => true
            ]),
            'created_at' => now(),
            'updated_at' => now()
        ];

        $incidentId = DB::table('security_incidents')->insertGetId($incidentData);

        $this->assertDatabaseHas('security_incidents', [
            'id' => $incidentId,
            'incident_type' => 'coordinated_attack',
            'severity' => 'critical'
        ]);

        // Test incident update
        DB::table('security_incidents')
            ->where('id', $incidentId)
            ->update([
                'status' => 'investigating',
                'assigned_to' => 'security@zimaesb.com',
                'mitigation_steps' => 'Blocked source IPs, increased monitoring',
                'updated_at' => now()
            ]);

        $this->assertDatabaseHas('security_incidents', [
            'id' => $incidentId,
            'status' => 'investigating',
            'assigned_to' => 'security@zimaesb.com'
        ]);
    }

    public function test_encryption_key_management()
    {
        $keyData = [
            'key_id' => 'test-key-' . uniqid(),
            'encrypted_key' => base64_encode('encrypted-key-material'),
            'algorithm' => 'AES-256-GCM',
            'purpose' => 'data',
            'is_active' => true,
            'expires_at' => now()->addDays(30),
            'created_at' => now(),
            'updated_at' => now()
        ];

        DB::table('encryption_keys')->insert($keyData);

        $this->assertDatabaseHas('encryption_keys', [
            'key_id' => $keyData['key_id'],
            'algorithm' => 'AES-256-GCM',
            'purpose' => 'data'
        ]);

        // Test key rotation
        $newKeyData = [
            'key_id' => 'rotated-key-' . uniqid(),
            'encrypted_key' => base64_encode('new-encrypted-key-material'),
            'algorithm' => 'AES-256-GCM',
            'purpose' => 'data',
            'is_active' => true,
            'expires_at' => now()->addDays(30),
            'rotated_at' => now(),
            'rotated_by' => 'system',
            'created_at' => now(),
            'updated_at' => now()
        ];

        // Deactivate old key
        DB::table('encryption_keys')
            ->where('key_id', $keyData['key_id'])
            ->update(['is_active' => false]);

        // Add new key
        DB::table('encryption_keys')->insert($newKeyData);

        $this->assertDatabaseHas('encryption_keys', [
            'key_id' => $keyData['key_id'],
            'is_active' => false
        ]);

        $this->assertDatabaseHas('encryption_keys', [
            'key_id' => $newKeyData['key_id'],
            'is_active' => true
        ]);
    }

    public function test_failed_authentication_logging()
    {
        $failureData = [
            [
                'api_key' => 'invalid-key-001',
                'ip_address' => '192.168.1.10',
                'user_agent' => 'BadBot/1.0',
                'failure_reason' => 'invalid_api_key',
                'request_headers' => json_encode(['X-API-Key' => 'invalid-key-001']),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'api_key' => 'expired-key-002',
                'ip_address' => '192.168.1.11',
                'user_agent' => 'AttackBot/2.0',
                'failure_reason' => 'invalid_signature',
                'request_headers' => json_encode(['X-Signature' => 'bad-signature']),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($failureData as $failure) {
            DB::table('failed_authentications')->insert($failure);
        }

        $this->assertDatabaseCount('failed_authentications', 2);
        
        $this->assertDatabaseHas('failed_authentications', [
            'api_key' => 'invalid-key-001',
            'failure_reason' => 'invalid_api_key'
        ]);

        $this->assertDatabaseHas('failed_authentications', [
            'api_key' => 'expired-key-002',
            'failure_reason' => 'invalid_signature'
        ]);
    }

    public function test_webhook_security_logging()
    {
        // Create test client
        $clientId = DB::table('clients')->insertGetId([
            'name' => 'Webhook Test Client',
            'code' => 'WHK001',
            'api_key' => 'webhook-test-key',
            'api_secret' => 'webhook-test-secret',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $webhookData = [
            'client_id' => $clientId,
            'webhook_url' => 'https://client.example.com/webhook',
            'request_signature' => 'sha256=abcdef123456',
            'signature_valid' => true,
            'headers_sent' => json_encode([
                'Content-Type' => 'application/json',
                'X-Webhook-Signature' => 'sha256=abcdef123456'
            ]),
            'payload_hash' => hash('sha256', '{"event": "payment.completed"}'),
            'response_code' => 200,
            'response_body' => 'OK',
            'response_time' => 0.125,
            'created_at' => now(),
            'updated_at' => now()
        ];

        DB::table('webhook_security_logs')->insert($webhookData);

        $this->assertDatabaseHas('webhook_security_logs', [
            'client_id' => $clientId,
            'webhook_url' => 'https://client.example.com/webhook',
            'signature_valid' => true,
            'response_code' => 200
        ]);
    }

    public function test_ip_whitelist_functionality()
    {
        // Create test client
        $clientId = DB::table('clients')->insertGetId([
            'name' => 'Whitelisted Client',
            'code' => 'WL001',
            'api_key' => 'whitelist-test-key',
            'api_secret' => 'whitelist-test-secret',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $whitelistData = [
            [
                'client_id' => $clientId,
                'ip_address' => '192.168.1.100',
                'description' => 'Main office IP',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'client_id' => $clientId,
                'ip_address' => '10.0.0.0/24',
                'description' => 'Internal network range',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($whitelistData as $entry) {
            DB::table('ip_whitelist')->insert($entry);
        }

        $this->assertDatabaseCount('ip_whitelist', 2);
        
        $this->assertDatabaseHas('ip_whitelist', [
            'client_id' => $clientId,
            'ip_address' => '192.168.1.100',
            'is_active' => true
        ]);

        $this->assertDatabaseHas('ip_whitelist', [
            'client_id' => $clientId,
            'ip_address' => '10.0.0.0/24',
            'is_active' => true
        ]);
    }

    public function test_security_configuration_values()
    {
        $expectedConfig = [
            'api.authentication.timestamp_tolerance' => 300,
            'api.authentication.nonce_cache_duration' => 600,
            'api.rate_limiting.global.requests_per_hour' => 1000,
            'threat_detection.enable' => true,
            'encryption.algorithm' => 'aes-256-gcm',
            'monitoring.enable_logging' => true
        ];

        foreach ($expectedConfig as $key => $expectedValue) {
            $actualValue = config("security.$key");
            $this->assertEquals($expectedValue, $actualValue, "Config mismatch for: $key");
        }
    }
}