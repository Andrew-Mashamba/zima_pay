<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run security migrations
        $this->artisan('migrate', ['--path' => 'database/migrations/2025_07_20_120000_create_security_tables.php']);
    }

    public function test_authentication_components_exist()
    {
        // Test that middleware classes exist
        $this->assertTrue(class_exists(\App\Http\Middleware\ApiAuthentication::class));
        $this->assertTrue(class_exists(\App\Http\Middleware\AdvancedRateLimit::class));
        $this->assertTrue(class_exists(\App\Http\Middleware\IpBlockingMiddleware::class));
        
        // Test that service classes exist
        $this->assertTrue(class_exists(\App\Services\SecurityService::class));
        $this->assertTrue(class_exists(\App\Services\ThreatDetectionService::class));
    }

    public function test_security_configuration_loaded()
    {
        $config = config('security');
        $this->assertIsArray($config);
        $this->assertArrayHasKey('api', $config);
        $this->assertArrayHasKey('encryption', $config);
        $this->assertArrayHasKey('threat_detection', $config);
    }
}