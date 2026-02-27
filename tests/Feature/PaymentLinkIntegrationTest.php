<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\PaymentLinkService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PaymentLinkIntegrationTest extends TestCase
{
    protected PaymentLinkService $service;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PaymentLinkService();
    }
    
    /**
     * Test complete payment link generation flow with realistic data
     */
    public function test_complete_payment_link_generation_flow()
    {
        // Mock the API response
        Http::fake([
            '*/api/payment-links/generate-universal' => Http::response([
                'success' => true,
                'message' => 'Payment link generated successfully',
                'data' => [
                    'link_id' => 'LINK_' . uniqid(),
                    'short_code' => strtoupper(substr(md5(time()), 0, 8)),
                    'payment_url' => 'https://zimapay.co.tz/pay/' . strtoupper(substr(md5(time()), 0, 8)),
                    'qr_code_data' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
                    'total_amount' => 175000.00,
                    'currency' => 'TZS',
                    'expires_at' => Carbon::now()->addDays(7)->toIso8601String(),
                    'created_at' => Carbon::now()->toIso8601String()
                ]
            ], 200)
        ]);
        
        // Test data for a complete e-commerce transaction
        $paymentData = [
            'description' => 'E-commerce Order #ORD-2024-001',
            'target' => 'individual',
            'customer_reference' => 'CUST-2024-0156',
            'customer_name' => 'John Michael Doe',
            'customer_phone' => '0712345678',
            'customer_email' => 'john.doe@example.com',
            'expires_at' => Carbon::now()->addDays(3)->toIso8601String(),
            'items' => [
                [
                    'type' => 'product',
                    'product_service_reference' => 'PROD-001',
                    'product_service_name' => 'Laptop - Dell XPS 13',
                    'amount' => 150000.00,
                    'quantity' => 1,
                    'is_required' => true,
                    'allow_partial' => false
                ],
                [
                    'type' => 'product',
                    'product_service_reference' => 'PROD-002',
                    'product_service_name' => 'Wireless Mouse',
                    'amount' => 15000.00,
                    'quantity' => 1,
                    'is_required' => true,
                    'allow_partial' => false
                ],
                [
                    'type' => 'service',
                    'product_service_reference' => 'SERV-001',
                    'product_service_name' => 'Extended Warranty (2 Years)',
                    'amount' => 10000.00,
                    'is_required' => false,
                    'allow_partial' => false
                ]
            ]
        ];
        
        $response = $this->service->generateUniversalPaymentLink($paymentData);
        
        // Assertions
        $this->assertArrayHasKey('success', $response);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('link_id', $response['data']);
        $this->assertArrayHasKey('payment_url', $response['data']);
        $this->assertArrayHasKey('total_amount', $response['data']);
        $this->assertEquals(175000.00, $response['data']['total_amount']);
        $this->assertStringContainsString('https://zimapay.co.tz/pay/', $response['data']['payment_url']);
    }
    
    /**
     * Test SACCOS member payment scenario
     */
    public function test_saccos_member_payment_scenario()
    {
        Http::fake([
            '*/api/payment-links/generate-universal' => Http::response([
                'success' => true,
                'data' => [
                    'link_id' => 'LINK_SACCOS_' . uniqid(),
                    'payment_url' => 'https://zimapay.co.tz/pay/SACCOS' . strtoupper(substr(md5(time()), 0, 6)),
                    'total_amount' => 200000.00,
                    'items_count' => 2
                ]
            ], 200)
        ]);
        
        // SACCOS member details
        $memberReference = 'MEM-2024-0892';
        $memberName = 'Sarah Jennifer Mwamba';
        $memberPhone = '0754321098';
        $memberEmail = 'sarah.mwamba@saccos.co.tz';
        
        // Payment amounts
        $sharesAmount = 75000.00;    // Mandatory shares
        $depositsAmount = 125000.00; // Savings deposits
        
        $response = $this->service->generateMemberPaymentLink(
            $memberReference,
            $memberName,
            $memberPhone,
            $memberEmail,
            $sharesAmount,
            $depositsAmount,
            [
                'description' => 'SACCOS Monthly Contribution - December 2024',
                'shares_reference' => 'SHARES_DEC_2024',
                'shares_name' => 'MANDATORY SHARES - DEC 2024',
                'deposits_reference' => 'DEP_SAV_DEC_2024',
                'deposits_name' => 'SAVINGS DEPOSITS - DEC 2024',
                'expires_at' => Carbon::now()->endOfMonth()->toIso8601String()
            ]
        );
        
        $this->assertTrue($response['success']);
        $this->assertEquals(200000.00, $response['data']['total_amount']);
        $this->assertStringContainsString('SACCOS', $response['data']['link_id']);
    }
    
    /**
     * Test loan repayment scenario with installments
     */
    public function test_loan_installments_payment_scenario()
    {
        Http::fake([
            '*/api/payment-links/generate-universal' => Http::response([
                'success' => true,
                'data' => [
                    'link_id' => 'LINK_LOAN_' . uniqid(),
                    'payment_url' => 'https://zimapay.co.tz/pay/LOAN' . strtoupper(substr(md5(time()), 0, 6)),
                    'total_amount' => 618000.00,
                    'installments_count' => 6
                ]
            ], 200)
        ]);
        
        // Mock loan client
        $client = (object) [
            'client_number' => 'CLI-2024-0456',
            'first_name' => 'Robert',
            'middle_name' => 'James',
            'last_name' => 'Mwasalwiba',
            'phone_number' => '0765432109',
            'email' => 'robert.mwasalwiba@business.co.tz'
        ];
        
        // Create realistic loan schedule (6 months loan)
        $schedules = [];
        $baseDate = Carbon::now()->startOfMonth();
        
        for ($i = 1; $i <= 6; $i++) {
            $schedules[] = (object) [
                'id' => 1000 + $i,
                'principle' => 100000.00,  // 100k principal per month
                'interest' => 3000.00,      // 3k interest per month
                'penalties' => 0,
                'charges' => 0,
                'installment_date' => $baseDate->copy()->addMonths($i)->format('Y-m-d'),
                'completion_status' => 'PENDING'
            ];
        }
        
        $loanId = 789456;
        
        $response = $this->service->generateLoanInstallmentsPaymentLink(
            $loanId,
            $client,
            $schedules,
            [
                'description' => 'Business Loan #BL-2024-789456 - 6 Month Repayment Plan'
            ]
        );
        
        $this->assertTrue($response['success']);
        $this->assertEquals(618000.00, $response['data']['total_amount']);
        $this->assertStringContainsString('LOAN', $response['data']['link_id']);
    }
    
    /**
     * Test payment status checking
     */
    public function test_check_payment_status_with_transactions()
    {
        $linkId = 'LINK_TEST_STATUS_123';
        
        Http::fake([
            "*/api/payment-links/{$linkId}/status" => Http::response([
                'success' => true,
                'data' => [
                    'link_id' => $linkId,
                    'status' => 'partially_paid',
                    'total_amount' => 500000.00,
                    'total_paid' => 350000.00,
                    'remaining_amount' => 150000.00,
                    'currency' => 'TZS',
                    'transactions' => [
                        [
                            'transaction_id' => 'TXN_001',
                            'amount' => 200000.00,
                            'payment_method' => 'TZ-MPESA-C2B',
                            'phone_number' => '255712345678',
                            'status' => 'success',
                            'payment_date' => '2024-12-24 09:30:00',
                            'reference' => 'MPESA_REF_12345'
                        ],
                        [
                            'transaction_id' => 'TXN_002',
                            'amount' => 150000.00,
                            'payment_method' => 'TZ-AIRTEL-C2B',
                            'phone_number' => '255754321098',
                            'status' => 'success',
                            'payment_date' => '2024-12-24 14:15:00',
                            'reference' => 'AIRTEL_REF_67890'
                        ]
                    ],
                    'created_at' => '2024-12-23 08:00:00',
                    'expires_at' => '2024-12-30 23:59:59',
                    'last_payment_at' => '2024-12-24 14:15:00'
                ]
            ], 200)
        ]);
        
        $status = $this->service->checkPaymentStatus($linkId);
        
        $this->assertTrue($status['success']);
        $this->assertEquals('partially_paid', $status['data']['status']);
        $this->assertEquals(500000.00, $status['data']['total_amount']);
        $this->assertEquals(350000.00, $status['data']['total_paid']);
        $this->assertEquals(150000.00, $status['data']['remaining_amount']);
        $this->assertCount(2, $status['data']['transactions']);
        
        // Check first transaction details
        $firstTransaction = $status['data']['transactions'][0];
        $this->assertEquals('TXN_001', $firstTransaction['transaction_id']);
        $this->assertEquals(200000.00, $firstTransaction['amount']);
        $this->assertEquals('TZ-MPESA-C2B', $firstTransaction['payment_method']);
    }
    
    /**
     * Test error handling for network issues
     */
    public function test_handles_network_timeout_gracefully()
    {
        Http::fake(function ($request) {
            throw new \Illuminate\Http\Client\ConnectionException('Connection timeout');
        });
        
        $this->expectException(\Exception::class);
        
        $data = [
            'description' => 'Test payment',
            'target' => 'individual',
            'customer_reference' => 'TIMEOUT_TEST',
            'customer_name' => 'Test User',
            'customer_phone' => '0712345678',
            'items' => [
                [
                    'type' => 'service',
                    'product_service_reference' => 'TEST',
                    'product_service_name' => 'Test Service',
                    'amount' => 1000
                ]
            ]
        ];
        
        $this->service->generateUniversalPaymentLink($data);
    }
    
    /**
     * Test with real-world edge cases
     */
    public function test_edge_cases_handling()
    {
        Http::fake([
            '*/api/payment-links/generate-universal' => Http::response([
                'success' => true,
                'data' => [
                    'link_id' => 'LINK_EDGE_' . uniqid(),
                    'payment_url' => 'https://zimapay.co.tz/pay/EDGE' . strtoupper(substr(md5(time()), 0, 6)),
                    'total_amount' => 0.50
                ]
            ], 200)
        ]);
        
        // Test with minimal amount (50 cents)
        $data = [
            'description' => 'Micro-payment test',
            'target' => 'individual',
            'customer_reference' => 'MICRO_001',
            'customer_name' => 'Test User',
            'customer_phone' => '+255-71-234-5678', // Phone with special characters
            'customer_email' => 'test.user+payment@example.com', // Email with plus sign
            'items' => [
                [
                    'type' => 'service',
                    'product_service_reference' => 'MICRO',
                    'product_service_name' => 'Test Micro Service',
                    'amount' => 0.50
                ]
            ]
        ];
        
        $response = $this->service->generateUniversalPaymentLink($data);
        
        $this->assertTrue($response['success']);
        $this->assertEquals(0.50, $response['data']['total_amount']);
    }
}