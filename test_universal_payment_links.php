<?php

require_once 'vendor/autoload.php';

use App\Models\Client;
use App\Models\PaymentLink;
use App\Services\UniversalPaymentLinkService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🌐 Universal Payment Link Service - Improved Request Format\n";
echo "==========================================================\n\n";

try {
    // Get the Sample Payment Gateway client
    $client = Client::where('name', 'Sample Payment Gateway')->first();
    if (!$client) {
        throw new Exception("Sample Payment Gateway client not found!");
    }
    echo "✅ Found client: {$client->name}\n\n";

    // Initialize Universal Payment Link Service
    $universalService = new UniversalPaymentLinkService();
    echo "🔧 Universal Payment Link Service initialized\n\n";

    // ========================================
    // SCENARIO 1: Individual Payment Link (Microfinance)
    // ========================================
    echo "💰 SCENARIO 1: Individual Payment Link (Microfinance)\n";
    echo "==================================================\n";
    echo "Target: individual (customer info provided upfront)\n";
    echo "Use Case: Loan repayment for specific borrower\n\n";

    $individualPaymentData = [
        'description' => 'Loan Repayment - Installment #3 of 12',
        'target' => 'individual',
        'customer_reference' => 'LOAN_2025_001',
        'customer_name' => 'Sarah Johnson',
        'customer_phone' => '255723456789',
        'customer_email' => 'sarah.johnson@email.com',
        'expires_at' => now()->addDays(7)->toISOString(),
        'max_uses' => 1,
        'is_reusable' => false,
        'allow_partial_payment' => false,
        'webhook_url' => 'https://microfinance.example.com/repayment-webhook',
        'items' => [
            [
                'type' => 'service',
                'product_service_reference' => 'LOAN_INST_003',
                'product_service_name' => 'Loan Installment',
                'description' => 'Monthly installment payment for business loan',
                'amount' => 75000,
                'is_required' => true,
                'allow_partial' => false
            ]
        ]
    ];

    $individualResult = $universalService->generateUniversalPaymentLink($individualPaymentData, $client);
    
    if ($individualResult['success']) {
        echo "✅ Individual payment link generated successfully!\n";
        echo "   Link ID: {$individualResult['data']['link_id']}\n";
        echo "   Short Code: {$individualResult['data']['short_code']}\n";
        echo "   Target Type: {$individualResult['data']['target_type']}\n";
        echo "   Is Public: " . ($individualResult['data']['is_public'] ? 'Yes' : 'No') . "\n";
        echo "   Customer Reference: {$individualResult['data']['customer_reference']}\n";
        echo "   Customer Name: {$individualResult['data']['customer_name']}\n";
        echo "   Customer Phone: {$individualResult['data']['customer_phone']}\n";
        echo "   Total Amount: TZS " . number_format($individualResult['data']['total_amount']) . "\n";
        echo "   Items:\n";
        foreach ($individualResult['data']['items'] as $item) {
            echo "     - {$item['product_service_name']} ({$item['type']}): TZS " . number_format($item['amount']) . 
                 " [Ref: {$item['product_service_reference']}]\n";
        }
        echo "\n";
        
        $individualShortCode = $individualResult['data']['short_code'];
    } else {
        echo "❌ Individual payment link generation failed: {$individualResult['error']}\n\n";
    }

    // ========================================
    // SCENARIO 2: Public Payment Link (Church Sadaka)
    // ========================================
    echo "⛪ SCENARIO 2: Public Payment Link (Church Sadaka)\n";
    echo "=================================================\n";
    echo "Target: public (customer info collected during payment)\n";
    echo "Use Case: Church collecting donations from anyone\n\n";

    $publicPaymentData = [
        'description' => 'Sunday Service Donation - St. Mary\'s Church',
        'target' => 'public',
        'customer_reference' => 'CHURCH_SADAKA_001',
        'expires_at' => now()->addDays(30)->toISOString(),
        'max_uses' => 100,
        'is_reusable' => true,
        'allow_partial_payment' => true,
        'webhook_url' => 'https://church.example.com/donation-webhook',
        'items' => [
            [
                'type' => 'service',
                'product_service_reference' => 'SADAKA_GENERAL',
                'product_service_name' => 'General Donation',
                'description' => 'General church donation for Sunday service',
                'amount' => 10000,
                'is_required' => false,
                'allow_partial' => true,
                'minimum_amount' => 1000
            ],
            [
                'type' => 'service',
                'product_service_reference' => 'SADAKA_BUILDING',
                'product_service_name' => 'Building Fund',
                'description' => 'Donation for church building fund',
                'amount' => 5000,
                'is_required' => false,
                'allow_partial' => true,
                'minimum_amount' => 500
            ]
        ]
    ];

    $publicResult = $universalService->generateUniversalPaymentLink($publicPaymentData, $client);
    
    if ($publicResult['success']) {
        echo "✅ Public payment link generated successfully!\n";
        echo "   Link ID: {$publicResult['data']['link_id']}\n";
        echo "   Short Code: {$publicResult['data']['short_code']}\n";
        echo "   Target Type: {$publicResult['data']['target_type']}\n";
        echo "   Is Public: " . ($publicResult['data']['is_public'] ? 'Yes' : 'No') . "\n";
        echo "   Customer Reference: {$publicResult['data']['customer_reference']}\n";
        echo "   Customer Name: " . ($publicResult['data']['customer_name'] ?? 'Will be collected') . "\n";
        echo "   Customer Phone: " . ($publicResult['data']['customer_phone'] ?? 'Will be collected') . "\n";
        echo "   Total Amount: TZS " . number_format($publicResult['data']['total_amount']) . "\n";
        echo "   Items:\n";
        foreach ($publicResult['data']['items'] as $item) {
            echo "     - {$item['product_service_name']} ({$item['type']}): TZS " . number_format($item['amount']) . 
                 " [Ref: {$item['product_service_reference']}]\n";
        }
        echo "\n";
        
        $publicShortCode = $publicResult['data']['short_code'];
    } else {
        echo "❌ Public payment link generation failed: {$publicResult['error']}\n\n";
    }

    // ========================================
    // SCENARIO 3: School Payment Link (Individual with Multiple Items)
    // ========================================
    echo "🏫 SCENARIO 3: School Payment Link (Individual with Multiple Items)\n";
    echo "==================================================================\n";
    echo "Target: individual with multiple service items\n";
    echo "Use Case: School fees with different payment rules\n\n";

    $schoolPaymentData = [
        'description' => 'Semester 1 Payment - St. Mary\'s Secondary School',
        'target' => 'individual',
        'customer_reference' => 'STUDENT_2025_001',
        'customer_name' => 'John Doe (Parent of Mary Doe)',
        'customer_phone' => '255712345678',
        'customer_email' => 'john.doe@email.com',
        'expires_at' => now()->addDays(30)->toISOString(),
        'max_uses' => 1,
        'is_reusable' => false,
        'allow_partial_payment' => true,
        'webhook_url' => 'https://school.example.com/payment-webhook',
        'items' => [
            [
                'type' => 'service',
                'product_service_reference' => 'SCHOOL_FEES_2025_SEM1',
                'product_service_name' => 'School Fees',
                'description' => 'Semester 1 school fees for Form 3',
                'amount' => 150000,
                'is_required' => true,
                'allow_partial' => true,
                'minimum_amount' => 50000
            ],
            [
                'type' => 'product',
                'product_service_reference' => 'UNIFORM_SET_2025',
                'product_service_name' => 'School Uniform',
                'description' => 'Complete school uniform set',
                'amount' => 45000,
                'is_required' => false,
                'allow_partial' => false
            ],
            [
                'type' => 'product',
                'product_service_reference' => 'TEXTBOOKS_FORM3_2025',
                'product_service_name' => 'Textbooks',
                'description' => 'Required textbooks for Form 3 subjects',
                'amount' => 25000,
                'is_required' => true,
                'allow_partial' => true,
                'minimum_amount' => 10000
            ]
        ]
    ];

    $schoolResult = $universalService->generateUniversalPaymentLink($schoolPaymentData, $client);
    
    if ($schoolResult['success']) {
        echo "✅ School payment link generated successfully!\n";
        echo "   Link ID: {$schoolResult['data']['link_id']}\n";
        echo "   Short Code: {$schoolResult['data']['short_code']}\n";
        echo "   Target Type: {$schoolResult['data']['target_type']}\n";
        echo "   Customer Reference: {$schoolResult['data']['customer_reference']}\n";
        echo "   Customer Name: {$schoolResult['data']['customer_name']}\n";
        echo "   Total Amount: TZS " . number_format($schoolResult['data']['total_amount']) . "\n";
        echo "   Items:\n";
        foreach ($schoolResult['data']['items'] as $item) {
            $partialStatus = $item['allow_partial'] ? 'Partial Allowed' : 'Full Only';
            echo "     - {$item['product_service_name']} ({$item['type']}): TZS " . number_format($item['amount']) . 
                 " [Ref: {$item['product_service_reference']}] ({$partialStatus})\n";
        }
        echo "\n";
        
        $schoolShortCode = $schoolResult['data']['short_code'];
    } else {
        echo "❌ School payment link generation failed: {$schoolResult['error']}\n\n";
    }

    // ========================================
    // SCENARIO 4: SACCO Contribution (Individual)
    // ========================================
    echo "🏦 SCENARIO 4: SACCO Contribution (Individual)\n";
    echo "=============================================\n";
    echo "Target: individual with service reference\n";
    echo "Use Case: Monthly SACCO contribution\n\n";

    $saccoPaymentData = [
        'description' => 'Monthly Contribution - January 2025',
        'target' => 'individual',
        'customer_reference' => 'MEMBER_2025_001',
        'customer_name' => 'Michael Chen',
        'customer_phone' => '255734567890',
        'customer_email' => 'michael.chen@email.com',
        'expires_at' => now()->addDays(15)->toISOString(),
        'max_uses' => 1,
        'is_reusable' => false,
        'allow_partial_payment' => false,
        'webhook_url' => 'https://sacco.example.com/contribution-webhook',
        'items' => [
            [
                'type' => 'service',
                'product_service_reference' => 'SACCO_CONT_JAN_2025',
                'product_service_name' => 'Monthly Contribution',
                'description' => 'Regular monthly contribution to SACCO',
                'amount' => 50000,
                'is_required' => true,
                'allow_partial' => false
            ]
        ]
    ];

    $saccoResult = $universalService->generateUniversalPaymentLink($saccoPaymentData, $client);
    
    if ($saccoResult['success']) {
        echo "✅ SACCO payment link generated successfully!\n";
        echo "   Link ID: {$saccoResult['data']['link_id']}\n";
        echo "   Short Code: {$saccoResult['data']['short_code']}\n";
        echo "   Target Type: {$saccoResult['data']['target_type']}\n";
        echo "   Customer Reference: {$saccoResult['data']['customer_reference']}\n";
        echo "   Customer Name: {$saccoResult['data']['customer_name']}\n";
        echo "   Total Amount: TZS " . number_format($saccoResult['data']['total_amount']) . "\n";
        echo "   Items:\n";
        foreach ($saccoResult['data']['items'] as $item) {
            echo "     - {$item['product_service_name']} ({$item['type']}): TZS " . number_format($item['amount']) . 
                 " [Ref: {$item['product_service_reference']}]\n";
        }
        echo "\n";
        
        $saccoShortCode = $saccoResult['data']['short_code'];
    } else {
        echo "❌ SACCO payment link generation failed: {$saccoResult['error']}\n\n";
    }

    // ========================================
    // PAYMENT PROCESSING SIMULATION
    // ========================================
    echo "💳 PAYMENT PROCESSING SIMULATION\n";
    echo "===============================\n";

    // Simulate public payment (customer info collected during payment)
    if (isset($publicShortCode)) {
        echo "📱 Public Payment Simulation (Church Sadaka):\n";
        echo "  Customer will be prompted to enter name and phone\n";
        echo "  Payment URL: http://127.0.0.1:8000/pay/{$publicShortCode}\n";
        echo "  Customer info will be collected during payment process\n\n";
    }

    // Simulate individual payment (customer info already provided)
    if (isset($individualShortCode)) {
        echo "📱 Individual Payment Simulation (Microfinance):\n";
        echo "  Customer info already provided: Sarah Johnson\n";
        echo "  Payment URL: http://127.0.0.1:8000/pay/{$individualShortCode}\n";
        echo "  No additional customer info collection needed\n\n";
    }

    // ========================================
    // IMPROVED REQUEST FORMAT SUMMARY
    // ========================================
    echo "🎯 IMPROVED UNIVERSAL REQUEST FORMAT SUMMARY\n";
    echo "===========================================\n";
    echo "✅ Key Improvements:\n";
    echo "   - Universal format for all payment link types\n";
    echo "   - Target type: 'individual' or 'public'\n";
    echo "   - Customer reference for tracking\n";
    echo "   - Service/Product type classification\n";
    echo "   - Product/Service references for integration\n";
    echo "   - Conditional customer info validation\n\n";

    echo "🔧 Request Format:\n";
    echo "```json\n";
    echo "{\n";
    echo "  \"description\": \"Payment description\",\n";
    echo "  \"target\": \"individual|public\",\n";
    echo "  \"customer_reference\": \"REF_001\",\n";
    echo "  \"customer_name\": \"John Doe\", // Required for individual\n";
    echo "  \"customer_phone\": \"255712345678\", // Required for individual\n";
    echo "  \"customer_email\": \"john@email.com\",\n";
    echo "  \"expires_at\": \"2025-07-27T10:00:00Z\",\n";
    echo "  \"items\": [\n";
    echo "    {\n";
    echo "      \"type\": \"service|product\",\n";
    echo "      \"product_service_reference\": \"REF_001\",\n";
    echo "      \"product_service_name\": \"Service Name\",\n";
    echo "      \"amount\": 75000,\n";
    echo "      \"is_required\": true,\n";
    echo "      \"allow_partial\": false\n";
    echo "    }\n";
    echo "  ]\n";
    echo "}\n";
    echo "```\n\n";

    echo "🎉 Benefits:\n";
    echo "   - Single API endpoint for all payment link types\n";
    echo "   - Flexible customer information handling\n";
    echo "   - Service/Product classification for better tracking\n";
    echo "   - Reference system for integration with external systems\n";
    echo "   - Support for church sadaka and other public collections\n";
    echo "   - Backward compatible with existing functionality\n\n";

    echo "🔗 Sample Payment URLs:\n";
    if (isset($individualShortCode)) {
        echo "   Individual (Microfinance): http://127.0.0.1:8000/pay/{$individualShortCode}\n";
    }
    if (isset($publicShortCode)) {
        echo "   Public (Church): http://127.0.0.1:8000/pay/{$publicShortCode}\n";
    }
    if (isset($schoolShortCode)) {
        echo "   Individual (School): http://127.0.0.1:8000/pay/{$schoolShortCode}\n";
    }
    if (isset($saccoShortCode)) {
        echo "   Individual (SACCO): http://127.0.0.1:8000/pay/{$saccoShortCode}\n";
    }
    echo "\n";

    echo "🚀 Universal Payment Link Service is ready for all use cases!\n";

} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 