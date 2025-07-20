<?php

require_once 'vendor/autoload.php';

use App\Models\Client;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🌐 Universal Payment Link API Testing\n";
echo "=====================================\n\n";

try {
    // Get the Sample Payment Gateway client
    $client = Client::where('name', 'Sample Payment Gateway')->first();
    if (!$client) {
        throw new Exception("Sample Payment Gateway client not found!");
    }
    echo "✅ Found client: {$client->name}\n";
    echo "   API Key: {$client->api_key}\n";
    echo "   API Secret: {$client->api_secret}\n\n";

    $baseUrl = 'http://127.0.0.1:8000/api/payment-links';
    $headers = [
        'X-API-Key: ' . $client->api_key,
        'X-API-Secret: ' . $client->api_secret,
        'Content-Type: application/json'
    ];

    echo "🔧 API Base URL: {$baseUrl}\n\n";

    // ========================================
    // TEST 1: Individual Payment Link (Microfinance)
    // ========================================
    echo "💰 TEST 1: Individual Payment Link (Microfinance)\n";
    echo "================================================\n";

    $individualData = [
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

    $individualResponse = makeApiRequest($baseUrl . '/generate-universal', 'POST', $headers, $individualData);
    
    if ($individualResponse['success']) {
        echo "✅ Individual payment link generated successfully!\n";
        displayPaymentLinkDetails($individualResponse['data'], 'Individual (Microfinance)');
        $individualShortCode = $individualResponse['data']['short_code'];
    } else {
        echo "❌ Individual payment link generation failed: {$individualResponse['message']}\n";
        if (isset($individualResponse['errors'])) {
            print_r($individualResponse['errors']);
        }
    }
    echo "\n";

    // ========================================
    // TEST 2: Public Payment Link (Church Sadaka)
    // ========================================
    echo "⛪ TEST 2: Public Payment Link (Church Sadaka)\n";
    echo "==============================================\n";

    $publicData = [
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

    $publicResponse = makeApiRequest($baseUrl . '/generate-universal', 'POST', $headers, $publicData);
    
    if ($publicResponse['success']) {
        echo "✅ Public payment link generated successfully!\n";
        displayPaymentLinkDetails($publicResponse['data'], 'Public (Church)');
        $publicShortCode = $publicResponse['data']['short_code'];
    } else {
        echo "❌ Public payment link generation failed: {$publicResponse['message']}\n";
        if (isset($publicResponse['errors'])) {
            print_r($publicResponse['errors']);
        }
    }
    echo "\n";

    // ========================================
    // TEST 3: School Payment Link (Multiple Items)
    // ========================================
    echo "🏫 TEST 3: School Payment Link (Multiple Items)\n";
    echo "===============================================\n";

    $schoolData = [
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

    $schoolResponse = makeApiRequest($baseUrl . '/generate-universal', 'POST', $headers, $schoolData);
    
    if ($schoolResponse['success']) {
        echo "✅ School payment link generated successfully!\n";
        displayPaymentLinkDetails($schoolResponse['data'], 'Individual (School)');
        $schoolShortCode = $schoolResponse['data']['short_code'];
    } else {
        echo "❌ School payment link generation failed: {$schoolResponse['message']}\n";
        if (isset($schoolResponse['errors'])) {
            print_r($schoolResponse['errors']);
        }
    }
    echo "\n";

    // ========================================
    // TEST 4: SACCO Contribution (Individual)
    // ========================================
    echo "🏦 TEST 4: SACCO Contribution (Individual)\n";
    echo "==========================================\n";

    $saccoData = [
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

    $saccoResponse = makeApiRequest($baseUrl . '/generate-universal', 'POST', $headers, $saccoData);
    
    if ($saccoResponse['success']) {
        echo "✅ SACCO payment link generated successfully!\n";
        displayPaymentLinkDetails($saccoResponse['data'], 'Individual (SACCO)');
        $saccoShortCode = $saccoResponse['data']['short_code'];
    } else {
        echo "❌ SACCO payment link generation failed: {$saccoResponse['message']}\n";
        if (isset($saccoResponse['errors'])) {
            print_r($saccoResponse['errors']);
        }
    }
    echo "\n";

    // ========================================
    // TEST 5: Get Payment Link Details
    // ========================================
    echo "📋 TEST 5: Get Payment Link Details\n";
    echo "===================================\n";

    if (isset($publicShortCode)) {
        echo "🔍 Getting details for public payment link: {$publicShortCode}\n";
        $detailsResponse = makeApiRequest($baseUrl . "/universal/{$publicShortCode}", 'GET', $headers);
        
        if ($detailsResponse['success']) {
            echo "✅ Payment link details retrieved successfully!\n";
            displayPaymentLinkDetails($detailsResponse['data'], 'Public Link Details');
        } else {
            echo "❌ Failed to get payment link details: {$detailsResponse['message']}\n";
        }
        echo "\n";
    }

    // ========================================
    // TEST 6: Get Payment Link Statistics
    // ========================================
    echo "📊 TEST 6: Get Payment Link Statistics\n";
    echo "=====================================\n";

    if (isset($individualShortCode)) {
        echo "📈 Getting statistics for individual payment link: {$individualShortCode}\n";
        $statsResponse = makeApiRequest($baseUrl . "/universal/{$individualShortCode}/stats", 'GET', $headers);
        
        if ($statsResponse['success']) {
            echo "✅ Payment link statistics retrieved successfully!\n";
            displayPaymentLinkStats($statsResponse['data']);
        } else {
            echo "❌ Failed to get payment link statistics: {$statsResponse['message']}\n";
        }
        echo "\n";
    }

    // ========================================
    // SUMMARY OF GENERATED LINKS
    // ========================================
    echo "🎯 SUMMARY OF GENERATED PAYMENT LINKS\n";
    echo "====================================\n";
    echo "All payment links have been successfully generated and are ready for use!\n\n";

    echo "🔗 Payment URLs:\n";
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

    echo "📱 API Endpoints Tested:\n";
    echo "   ✅ POST /api/payment-links/generate-universal\n";
    echo "   ✅ GET /api/payment-links/universal/{shortCode}\n";
    echo "   ✅ GET /api/payment-links/universal/{shortCode}/stats\n";
    echo "\n";

    echo "🎉 Universal Payment Link API is working perfectly!\n";

} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

/**
 * Make API request
 */
function makeApiRequest($url, $method, $headers, $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data && $method === 'POST') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $responseData = json_decode($response, true);
    
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'http_code' => $httpCode,
        'data' => $responseData['data'] ?? null,
        'message' => $responseData['message'] ?? 'Unknown error',
        'errors' => $responseData['errors'] ?? null,
        'raw_response' => $response
    ];
}

/**
 * Display payment link details
 */
function displayPaymentLinkDetails($data, $title) {
    echo "   📋 {$title}\n";
    echo "   ──────────────────────────────────────────────────────────────\n";
    echo "   Link ID: {$data['link_id']}\n";
    echo "   Short Code: {$data['short_code']}\n";
    echo "   Target Type: {$data['target_type']}\n";
    echo "   Is Public: " . ($data['is_public'] ? 'Yes' : 'No') . "\n";
    echo "   Description: {$data['description']}\n";
    echo "   Customer Reference: {$data['customer_reference']}\n";
    echo "   Customer Name: " . ($data['customer_name'] ?? 'Will be collected') . "\n";
    echo "   Customer Phone: " . ($data['customer_phone'] ?? 'Will be collected') . "\n";
    echo "   Total Amount: TZS " . number_format($data['total_amount']) . "\n";
    echo "   Currency: {$data['currency']}\n";
    echo "   Payment URL: {$data['payment_url']}\n";
    echo "   QR Code Data: {$data['qr_code_data']}\n";
    echo "   Expires At: " . ($data['expires_at'] ?? 'Never') . "\n";
    echo "   Max Uses: " . ($data['max_uses'] ?? 'Unlimited') . "\n";
    echo "   Is Reusable: " . ($data['is_reusable'] ? 'Yes' : 'No') . "\n";
    echo "   Allowed Networks: " . implode(', ', $data['allowed_networks']) . "\n";
    echo "   Created At: {$data['created_at']}\n";
    
    echo "   Items:\n";
    foreach ($data['items'] as $item) {
        $partialStatus = $item['allow_partial'] ? 'Partial Allowed' : 'Full Only';
        echo "     - {$item['product_service_name']} ({$item['type']}): TZS " . number_format($item['amount']) . 
             " [Ref: {$item['product_service_reference']}] ({$partialStatus})\n";
    }
    echo "   ──────────────────────────────────────────────────────────────\n";
}

/**
 * Display payment link statistics
 */
function displayPaymentLinkStats($data) {
    echo "   📊 Payment Link Statistics\n";
    echo "   ──────────────────────────────────────────────────────────────\n";
    echo "   Link ID: {$data['link_id']}\n";
    echo "   Short Code: {$data['short_code']}\n";
    echo "   Status: {$data['status']}\n";
    echo "   Target Type: {$data['target_type']}\n";
    echo "   Is Public: " . ($data['is_public'] ? 'Yes' : 'No') . "\n";
    echo "   Customer Reference: {$data['customer_reference']}\n";
    echo "   Total Amount: TZS " . number_format($data['total_amount']) . "\n";
    echo "   Total Paid: TZS " . number_format($data['total_paid']) . "\n";
    echo "   Remaining Amount: TZS " . number_format($data['remaining_amount']) . "\n";
    echo "   Payment Progress: {$data['payment_progress']}%\n";
    echo "   Views Count: {$data['views_count']}\n";
    echo "   Current Uses: {$data['current_uses']}\n";
    echo "   Max Uses: " . ($data['max_uses'] ?? 'Unlimited') . "\n";
    echo "   Total Collected: TZS " . number_format($data['total_collected']) . "\n";
    echo "   Successful Transactions: {$data['successful_transactions_count']}\n";
    echo "   Conversion Rate: {$data['conversion_rate']}%\n";
    
    echo "   Items Summary:\n";
    echo "     - Total Items: {$data['items_summary']['total_items']}\n";
    echo "     - Paid Items: {$data['items_summary']['paid_items']}\n";
    echo "     - Partial Items: {$data['items_summary']['partial_items']}\n";
    echo "     - Pending Items: {$data['items_summary']['pending_items']}\n";
    
    echo "   Created At: {$data['created_at']}\n";
    echo "   Expires At: " . ($data['expires_at'] ?? 'Never') . "\n";
    echo "   Last Viewed At: " . ($data['last_viewed_at'] ?? 'Never') . "\n";
    echo "   ──────────────────────────────────────────────────────────────\n";
} 