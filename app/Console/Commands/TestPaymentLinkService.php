<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PaymentLinkService;
use Carbon\Carbon;
use Exception;

class TestPaymentLinkService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:test-link 
                            {type=basic : Type of test (basic|member|loan|installments|status|all)}
                            {--live : Use live API instead of mock data}
                            {--debug : Show debug information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test PaymentLinkService with various scenarios';

    protected PaymentLinkService $paymentService;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->paymentService = new PaymentLinkService();
        $type = $this->argument('type');
        $useLive = $this->option('live');
        $debug = $this->option('debug');

        if (!$useLive) {
            $this->warn('Running in TEST MODE. Use --live flag to make actual API calls.');
        }

        $this->info("Testing PaymentLinkService - Type: {$type}");
        $this->newLine();

        try {
            switch ($type) {
                case 'basic':
                    $this->testBasicPaymentLink($debug);
                    break;
                case 'member':
                    $this->testMemberPaymentLink($debug);
                    break;
                case 'loan':
                    $this->testLoanPaymentLink($debug);
                    break;
                case 'installments':
                    $this->testLoanInstallmentsLink($debug);
                    break;
                case 'status':
                    $this->testCheckPaymentStatus($debug);
                    break;
                case 'all':
                    $this->testBasicPaymentLink($debug);
                    $this->newLine();
                    $this->testMemberPaymentLink($debug);
                    $this->newLine();
                    $this->testLoanPaymentLink($debug);
                    $this->newLine();
                    $this->testLoanInstallmentsLink($debug);
                    $this->newLine();
                    $this->testCheckPaymentStatus($debug);
                    break;
                default:
                    $this->error("Unknown test type: {$type}");
                    return 1;
            }

            $this->newLine();
            $this->info('✅ Test completed successfully!');
            return 0;

        } catch (Exception $e) {
            $this->newLine();
            $this->error('❌ Test failed with error:');
            $this->error($e->getMessage());
            
            if ($debug) {
                $this->error('Stack trace:');
                $this->line($e->getTraceAsString());
            }
            
            return 1;
        }
    }

    /**
     * Test basic payment link generation
     */
    protected function testBasicPaymentLink(bool $debug = false)
    {
        $this->info('📝 Testing Basic Payment Link Generation...');
        
        $data = [
            'description' => 'Test E-commerce Order #' . rand(1000, 9999),
            'target' => 'individual',
            'customer_reference' => 'CUST-' . date('Y') . '-' . rand(1000, 9999),
            'customer_name' => 'John Test Doe',
            'customer_phone' => '0712345678',
            'customer_email' => 'test@example.com',
            'expires_at' => Carbon::now()->addDays(3)->toIso8601String(),
            'items' => [
                [
                    'type' => 'product',
                    'product_service_reference' => 'PROD-001',
                    'product_service_name' => 'Test Product - Laptop',
                    'amount' => 1500000.00,
                    'is_required' => true,
                    'allow_partial' => false
                ],
                [
                    'type' => 'service',
                    'product_service_reference' => 'SERV-001',
                    'product_service_name' => 'Installation Service',
                    'amount' => 50000.00,
                    'is_required' => false,
                    'allow_partial' => false
                ]
            ]
        ];

        if ($debug) {
            $this->info('Request Data:');
            $this->line(json_encode($data, JSON_PRETTY_PRINT));
        }

        if (!$this->option('live')) {
            $this->mockResponse($data);
            return;
        }

        $response = $this->paymentService->generateUniversalPaymentLink($data);
        $this->displayResponse($response, $debug);
    }

    /**
     * Test SACCOS member payment link
     */
    protected function testMemberPaymentLink(bool $debug = false)
    {
        $this->info('👥 Testing SACCOS Member Payment Link...');
        
        $memberReference = 'MEM-' . date('Y') . '-' . rand(1000, 9999);
        $memberName = 'Jane Test Smith';
        $memberPhone = '0754321098';
        $memberEmail = 'jane.test@saccos.co.tz';
        $sharesAmount = 100000.00;
        $depositsAmount = 200000.00;
        
        $options = [
            'description' => 'SACCOS Monthly Contribution - ' . date('F Y'),
            'shares_reference' => 'SHARES_' . date('m_Y'),
            'shares_name' => 'MANDATORY SHARES - ' . date('M Y'),
            'deposits_reference' => 'DEP_' . date('m_Y'),
            'deposits_name' => 'SAVINGS DEPOSITS - ' . date('M Y'),
            'expires_at' => Carbon::now()->endOfMonth()->toIso8601String()
        ];

        if ($debug) {
            $this->info('Member Details:');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Reference', $memberReference],
                    ['Name', $memberName],
                    ['Phone', $memberPhone],
                    ['Email', $memberEmail],
                    ['Shares Amount', 'TZS ' . number_format($sharesAmount, 2)],
                    ['Deposits Amount', 'TZS ' . number_format($depositsAmount, 2)],
                ]
            );
        }

        if (!$this->option('live')) {
            $this->mockMemberResponse($memberReference, $sharesAmount + $depositsAmount);
            return;
        }

        $response = $this->paymentService->generateMemberPaymentLink(
            $memberReference,
            $memberName,
            $memberPhone,
            $memberEmail,
            $sharesAmount,
            $depositsAmount,
            $options
        );
        
        $this->displayResponse($response, $debug);
    }

    /**
     * Test loan payment link
     */
    protected function testLoanPaymentLink(bool $debug = false)
    {
        $this->info('💰 Testing Loan Payment URL Generation...');
        
        $loanReference = 'LOAN-' . date('Y') . '-' . rand(10000, 99999);
        $memberName = 'Peter Test Johnson';
        $memberPhone = '0765432109';
        $amount = 250000.00;
        
        $options = [
            'email' => 'peter.test@example.com',
            'description' => 'Loan Repayment - ' . $loanReference,
            'allow_partial' => true,
            'expires_at' => Carbon::now()->addDays(5)->toIso8601String()
        ];

        if ($debug) {
            $this->info('Loan Details:');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Loan Reference', $loanReference],
                    ['Borrower Name', $memberName],
                    ['Phone', $memberPhone],
                    ['Amount', 'TZS ' . number_format($amount, 2)],
                    ['Allow Partial', $options['allow_partial'] ? 'Yes' : 'No'],
                ]
            );
        }

        if (!$this->option('live')) {
            $this->mockLoanResponse($loanReference, $amount);
            return;
        }

        $paymentUrl = $this->paymentService->generateLoanPaymentUrl(
            $loanReference,
            $memberName,
            $memberPhone,
            $amount,
            $options
        );
        
        $this->info("✅ Payment URL Generated:");
        $this->line("   📎 URL: {$paymentUrl}");
    }

    /**
     * Test loan installments payment link
     */
    protected function testLoanInstallmentsLink(bool $debug = false)
    {
        $this->info('📅 Testing Loan Installments Payment Link...');
        
        $loanId = rand(100000, 999999);
        
        // Mock client object
        $client = (object) [
            'client_number' => 'CLI-' . date('Y') . '-' . rand(1000, 9999),
            'first_name' => 'Mary',
            'middle_name' => 'Test',
            'last_name' => 'Williams',
            'phone_number' => '0776543210',
            'email' => 'mary.test@example.com'
        ];
        
        // Generate 6-month loan schedule
        $schedules = [];
        $baseDate = Carbon::now()->startOfMonth();
        $principalPerMonth = 200000.00;
        $interestPerMonth = 6000.00;
        
        // First installment (interest only - already paid)
        $schedules[] = (object) [
            'id' => 1001,
            'principle' => 0,
            'interest' => $interestPerMonth,
            'penalties' => 0,
            'charges' => 0,
            'installment_date' => $baseDate->format('Y-m-d'),
            'completion_status' => 'PAID'
        ];
        
        // Remaining installments
        for ($i = 1; $i <= 6; $i++) {
            $schedules[] = (object) [
                'id' => 1001 + $i,
                'principle' => $principalPerMonth,
                'interest' => $interestPerMonth,
                'penalties' => 0,
                'charges' => 0,
                'installment_date' => $baseDate->copy()->addMonths($i)->format('Y-m-d'),
                'completion_status' => 'PENDING'
            ];
        }

        if ($debug) {
            $this->info('Loan Details:');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Loan ID', $loanId],
                    ['Client Number', $client->client_number],
                    ['Client Name', "{$client->first_name} {$client->middle_name} {$client->last_name}"],
                    ['Phone', $client->phone_number],
                    ['Total Schedules', count($schedules)],
                    ['Pending Schedules', count(array_filter($schedules, fn($s) => $s->completion_status === 'PENDING'))],
                ]
            );
            
            $this->info('Payment Schedule:');
            $scheduleTable = [];
            foreach ($schedules as $schedule) {
                $scheduleTable[] = [
                    $schedule->id,
                    $schedule->installment_date,
                    'TZS ' . number_format($schedule->principle, 2),
                    'TZS ' . number_format($schedule->interest, 2),
                    $schedule->completion_status
                ];
            }
            $this->table(['ID', 'Due Date', 'Principal', 'Interest', 'Status'], $scheduleTable);
        }

        if (!$this->option('live')) {
            $totalAmount = array_sum(array_map(fn($s) => 
                $s->completion_status === 'PENDING' ? 
                ($s->principle + $s->interest + $s->penalties + $s->charges) : 0, 
                $schedules
            ));
            $this->mockInstallmentsResponse($loanId, $totalAmount);
            return;
        }

        $response = $this->paymentService->generateLoanInstallmentsPaymentLink(
            $loanId,
            $client,
            $schedules,
            ['description' => "Business Loan #{$loanId} - Payment Schedule"]
        );
        
        $this->displayResponse($response, $debug);
    }

    /**
     * Test payment status checking
     */
    protected function testCheckPaymentStatus(bool $debug = false)
    {
        $this->info('🔍 Testing Payment Status Check...');
        
        $linkId = $this->ask('Enter Payment Link ID to check status', 'LINK_TEST_123456');
        
        if ($debug) {
            $this->info("Checking status for: {$linkId}");
        }

        if (!$this->option('live')) {
            $this->mockStatusResponse($linkId);
            return;
        }

        $status = $this->paymentService->checkPaymentStatus($linkId);
        
        $this->info("Payment Link Status:");
        $this->table(
            ['Field', 'Value'],
            [
                ['Link ID', $status['data']['link_id'] ?? 'N/A'],
                ['Status', $status['data']['status'] ?? 'N/A'],
                ['Total Amount', 'TZS ' . number_format($status['data']['total_amount'] ?? 0, 2)],
                ['Total Paid', 'TZS ' . number_format($status['data']['total_paid'] ?? 0, 2)],
                ['Remaining', 'TZS ' . number_format($status['data']['remaining_amount'] ?? 0, 2)],
                ['Transactions', count($status['data']['transactions'] ?? [])],
            ]
        );

        if (!empty($status['data']['transactions']) && $debug) {
            $this->info('Transaction History:');
            $transTable = [];
            foreach ($status['data']['transactions'] as $trans) {
                $transTable[] = [
                    $trans['transaction_id'] ?? 'N/A',
                    'TZS ' . number_format($trans['amount'] ?? 0, 2),
                    $trans['payment_method'] ?? 'N/A',
                    $trans['status'] ?? 'N/A',
                    $trans['payment_date'] ?? 'N/A'
                ];
            }
            $this->table(['Transaction ID', 'Amount', 'Method', 'Status', 'Date'], $transTable);
        }
    }

    /**
     * Display API response
     */
    protected function displayResponse(array $response, bool $debug = false)
    {
        if ($debug) {
            $this->info('Full Response:');
            $this->line(json_encode($response, JSON_PRETTY_PRINT));
        }

        if (isset($response['success']) && $response['success']) {
            $this->info('✅ Payment Link Generated Successfully!');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Link ID', $response['data']['link_id'] ?? 'N/A'],
                    ['Payment URL', $response['data']['payment_url'] ?? 'N/A'],
                    ['Short Code', $response['data']['short_code'] ?? 'N/A'],
                    ['Total Amount', 'TZS ' . number_format($response['data']['total_amount'] ?? 0, 2)],
                    ['Expires At', $response['data']['expires_at'] ?? 'N/A'],
                ]
            );
        } else {
            $this->error('❌ Failed to generate payment link');
            $this->error('Error: ' . ($response['error'] ?? 'Unknown error'));
        }
    }

    /**
     * Mock responses for testing without live API
     */
    protected function mockResponse(array $data)
    {
        $totalAmount = array_sum(array_column($data['items'], 'amount'));
        $this->info('🔧 MOCK Response (Test Mode):');
        $this->table(
            ['Field', 'Value'],
            [
                ['Link ID', 'LINK_MOCK_' . strtoupper(substr(md5(time()), 0, 8))],
                ['Payment URL', 'https://zimapay.co.tz/pay/' . strtoupper(substr(md5(time()), 0, 8))],
                ['Total Amount', 'TZS ' . number_format($totalAmount, 2)],
                ['Status', 'active'],
                ['Expires', $data['expires_at'] ?? 'N/A'],
            ]
        );
    }

    protected function mockMemberResponse(string $reference, float $amount)
    {
        $this->info('🔧 MOCK Response (Test Mode):');
        $this->table(
            ['Field', 'Value'],
            [
                ['Link ID', 'LINK_MEM_' . strtoupper(substr(md5($reference), 0, 8))],
                ['Payment URL', 'https://zimapay.co.tz/pay/MEM' . strtoupper(substr(md5($reference), 0, 6))],
                ['Total Amount', 'TZS ' . number_format($amount, 2)],
                ['Type', 'SACCOS Member Payment'],
                ['Status', 'active'],
            ]
        );
    }

    protected function mockLoanResponse(string $reference, float $amount)
    {
        $paymentUrl = 'https://zimapay.co.tz/pay/LOAN' . strtoupper(substr(md5($reference), 0, 6));
        $this->info('🔧 MOCK Response (Test Mode):');
        $this->line("   📎 Payment URL: {$paymentUrl}");
        $this->line("   💰 Amount: TZS " . number_format($amount, 2));
    }

    protected function mockInstallmentsResponse(int $loanId, float $amount)
    {
        $this->info('🔧 MOCK Response (Test Mode):');
        $this->table(
            ['Field', 'Value'],
            [
                ['Link ID', 'LINK_INST_' . $loanId],
                ['Payment URL', 'https://zimapay.co.tz/pay/INST' . substr(md5($loanId), 0, 6)],
                ['Total Amount', 'TZS ' . number_format($amount, 2)],
                ['Type', 'Loan Installments'],
                ['Status', 'active'],
            ]
        );
    }

    protected function mockStatusResponse(string $linkId)
    {
        $this->info('🔧 MOCK Status Response (Test Mode):');
        $this->table(
            ['Field', 'Value'],
            [
                ['Link ID', $linkId],
                ['Status', 'partially_paid'],
                ['Total Amount', 'TZS 500,000.00'],
                ['Total Paid', 'TZS 350,000.00'],
                ['Remaining', 'TZS 150,000.00'],
                ['Mock Transactions', '2'],
            ]
        );
    }
}