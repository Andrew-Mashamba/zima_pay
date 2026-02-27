<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PaymentLinkService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use Exception;

class PaymentLinkServiceTest extends TestCase
{
    protected PaymentLinkService $service;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up test configuration
        Config::set('services.payment_gateway.base_url', 'https://zimapay.co.tz');
        Config::set('services.payment_gateway.api_key', 'test_api_key_123');
        Config::set('services.payment_gateway.api_secret', 'test_api_secret_456');
        Config::set('services.payment_gateway.frontend_url', 'https://zimapay.co.tz');
        Config::set('services.payment_gateway.url', 'https://zimapay.co.tz/api/payment-links/generate-universal');
        
        $this->service = new PaymentLinkService();
    }
    
    /**
     * Test phone number formatting
     */
    public function test_phone_number_formatting()
    {
        // Test with 9 digits (missing country code)
        $formatted = $this->invokeMethod($this->service, 'formatPhoneNumber', ['712345678']);
        $this->assertEquals('255712345678', $formatted);
        
        // Test with 10 digits starting with 0
        $formatted = $this->invokeMethod($this->service, 'formatPhoneNumber', ['0712345678']);
        $this->assertEquals('255712345678', $formatted);
        
        // Test with full number including country code
        $formatted = $this->invokeMethod($this->service, 'formatPhoneNumber', ['255712345678']);
        $this->assertEquals('255712345678', $formatted);
        
        // Test with special characters
        $formatted = $this->invokeMethod($this->service, 'formatPhoneNumber', ['+255-71-234-5678']);
        $this->assertEquals('255712345678', $formatted);
    }
    
    /**
     * Test request data validation and preparation
     */
    public function test_prepare_request_data_validation()
    {
        // Test with valid data
        $validData = [
            'description' => 'Test payment',
            'target' => 'individual',
            'customer_reference' => 'CUST001',
            'customer_name' => 'John Doe',
            'customer_phone' => '0712345678',
            'items' => [
                [
                    'type' => 'service',
                    'product_service_reference' => 'SERV001',
                    'product_service_name' => 'Test Service',
                    'amount' => 10000
                ]
            ]
        ];
        
        $prepared = $this->invokeMethod($this->service, 'prepareRequestData', [$validData]);
        
        $this->assertArrayHasKey('expires_at', $prepared);
        $this->assertEquals('255712345678', $prepared['customer_phone']);
        $this->assertCount(1, $prepared['items']);
    }
    
    /**
     * Test validation fails with missing required fields
     */
    public function test_prepare_request_data_throws_exception_on_missing_fields()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Required field 'customer_name' is missing");
        
        $invalidData = [
            'description' => 'Test payment',
            'target' => 'individual',
            'customer_reference' => 'CUST001',
            'customer_phone' => '0712345678',
            'items' => []
        ];
        
        $this->invokeMethod($this->service, 'prepareRequestData', [$invalidData]);
    }
    
    /**
     * Test validation fails with empty items array
     */
    public function test_prepare_request_data_throws_exception_on_empty_items()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Required field 'items' is missing");
        
        $invalidData = [
            'description' => 'Test payment',
            'target' => 'individual',
            'customer_reference' => 'CUST001',
            'customer_name' => 'John Doe',
            'customer_phone' => '0712345678',
            'items' => []
        ];
        
        $this->invokeMethod($this->service, 'prepareRequestData', [$invalidData]);
    }
    
    /**
     * Test successful universal payment link generation
     */
    public function test_generate_universal_payment_link_success()
    {
        // Mock successful HTTP response
        Http::fake([
            'zimapay.co.tz/api/payment-links/generate-universal' => Http::response([
                'success' => true,
                'data' => [
                    'link_id' => 'LINK_ABC123',
                    'payment_url' => 'https://zimapay.co.tz/pay/ABC123XYZ',
                    'short_code' => 'ABC123XYZ',
                    'total_amount' => 25000.00,
                    'expires_at' => Carbon::now()->addDays(7)->toIso8601String()
                ]
            ], 200)
        ]);
        
        $data = [
            'description' => 'Test payment for services',
            'target' => 'individual',
            'customer_reference' => 'CUST001',
            'customer_name' => 'John Doe',
            'customer_phone' => '0712345678',
            'customer_email' => 'john.doe@example.com',
            'items' => [
                [
                    'type' => 'service',
                    'product_service_reference' => 'SERV001',
                    'product_service_name' => 'Consultation Service',
                    'amount' => 25000.00,
                    'is_required' => true,
                    'allow_partial' => false
                ]
            ]
        ];
        
        $response = $this->service->generateUniversalPaymentLink($data);
        
        $this->assertTrue($response['success']);
        $this->assertEquals('LINK_ABC123', $response['data']['link_id']);
        $this->assertStringContainsString('/pay/', $response['data']['payment_url']);
        $this->assertEquals(25000.00, $response['data']['total_amount']);
    }
    
    /**
     * Test failed payment link generation
     */
    public function test_generate_universal_payment_link_failure()
    {
        // Mock failed HTTP response
        Http::fake([
            'zimapay.co.tz/api/payment-links/generate-universal' => Http::response([
                'success' => false,
                'error' => 'Invalid API credentials'
            ], 401)
        ]);
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to generate payment link');
        
        $data = [
            'description' => 'Test payment',
            'target' => 'individual',
            'customer_reference' => 'CUST001',
            'customer_name' => 'John Doe',
            'customer_phone' => '0712345678',
            'items' => [
                [
                    'type' => 'service',
                    'product_service_reference' => 'SERV001',
                    'product_service_name' => 'Test Service',
                    'amount' => 10000
                ]
            ]
        ];
        
        $this->service->generateUniversalPaymentLink($data);
    }
    
    /**
     * Test member payment link generation
     */
    public function test_generate_member_payment_link()
    {
        Http::fake([
            'zimapay.co.tz/api/payment-links/generate-universal' => Http::response([
                'success' => true,
                'data' => [
                    'link_id' => 'LINK_MEMBER123',
                    'payment_url' => 'https://zimapay.co.tz/pay/MEM123XYZ',
                    'total_amount' => 150000.00
                ]
            ], 200)
        ]);
        
        $response = $this->service->generateMemberPaymentLink(
            'MEM001',
            'Jane Smith',
            '0754321098',
            'jane.smith@example.com',
            50000.00,  // Shares amount
            100000.00, // Deposits amount
            [
                'description' => 'SACCOS Member Payment',
                'shares_name' => 'MANDATORY SHARES 2024',
                'deposits_name' => 'SAVINGS DEPOSITS'
            ]
        );
        
        $this->assertTrue($response['success']);
        $this->assertEquals('LINK_MEMBER123', $response['data']['link_id']);
        $this->assertEquals(150000.00, $response['data']['total_amount']);
    }
    
    /**
     * Test loan payment URL generation
     */
    public function test_generate_loan_payment_url()
    {
        Http::fake([
            'zimapay.co.tz/api/payment-links/generate-universal' => Http::response([
                'success' => true,
                'data' => [
                    'payment_url' => 'https://zimapay.co.tz/pay/LOAN123XYZ'
                ]
            ], 200)
        ]);
        
        $paymentUrl = $this->service->generateLoanPaymentUrl(
            'LOAN001',
            'Peter Johnson',
            '0765432109',
            75000.00,
            [
                'email' => 'peter.j@example.com',
                'description' => 'Monthly loan repayment',
                'allow_partial' => true
            ]
        );
        
        $this->assertEquals('https://zimapay.co.tz/pay/LOAN123XYZ', $paymentUrl);
    }
    
    /**
     * Test loan installments payment link generation
     */
    public function test_generate_loan_installments_payment_link()
    {
        Http::fake([
            'zimapay.co.tz/api/payment-links/generate-universal' => Http::response([
                'success' => true,
                'data' => [
                    'link_id' => 'LINK_INST123',
                    'payment_url' => 'https://zimapay.co.tz/pay/INST123XYZ',
                    'total_amount' => 320000.00
                ]
            ], 200)
        ]);
        
        // Mock client object
        $client = (object) [
            'client_number' => 'CLI001',
            'first_name' => 'Mary',
            'middle_name' => 'Jane',
            'last_name' => 'Williams',
            'phone_number' => '0776543210',
            'email' => 'mary.williams@example.com'
        ];
        
        // Mock loan schedules
        $schedules = [
            (object) [
                'id' => 1,
                'principle' => 50000,
                'interest' => 5000,
                'penalties' => 0,
                'charges' => 0,
                'installment_date' => '2024-01-15',
                'completion_status' => 'PAID' // This will be skipped
            ],
            (object) [
                'id' => 2,
                'principle' => 50000,
                'interest' => 5000,
                'penalties' => 0,
                'charges' => 0,
                'installment_date' => '2024-02-15',
                'completion_status' => 'PENDING'
            ],
            (object) [
                'id' => 3,
                'principle' => 50000,
                'interest' => 5000,
                'penalties' => 0,
                'charges' => 0,
                'installment_date' => '2024-03-15',
                'completion_status' => 'PENDING'
            ]
        ];
        
        $response = $this->service->generateLoanInstallmentsPaymentLink(
            12345,
            $client,
            $schedules,
            ['description' => 'Business Loan Repayment Schedule']
        );
        
        $this->assertTrue($response['success']);
        $this->assertEquals('LINK_INST123', $response['data']['link_id']);
        $this->assertStringContainsString('/pay/', $response['data']['payment_url']);
    }
    
    /**
     * Test payment status check
     */
    public function test_check_payment_status()
    {
        Http::fake([
            'zimapay.co.tz/api/payment-links/LINK_ABC123/status' => Http::response([
                'success' => true,
                'data' => [
                    'link_id' => 'LINK_ABC123',
                    'status' => 'active',
                    'total_paid' => 15000.00,
                    'total_amount' => 25000.00,
                    'transactions' => [
                        [
                            'transaction_id' => 'TXN001',
                            'amount' => 15000.00,
                            'status' => 'success',
                            'payment_date' => '2024-12-24 10:30:00'
                        ]
                    ]
                ]
            ], 200)
        ]);
        
        $status = $this->service->checkPaymentStatus('LINK_ABC123');
        
        $this->assertTrue($status['success']);
        $this->assertEquals('active', $status['data']['status']);
        $this->assertEquals(15000.00, $status['data']['total_paid']);
        $this->assertCount(1, $status['data']['transactions']);
    }
    
    /**
     * Helper method to invoke private/protected methods
     */
    protected function invokeMethod($object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        
        return $method->invokeArgs($object, $parameters);
    }
}