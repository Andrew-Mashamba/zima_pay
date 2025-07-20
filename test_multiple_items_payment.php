<?php

require_once 'vendor/autoload.php';

use App\Models\Client;
use App\Models\Service;
use App\Models\Aggregator;
use App\Models\ServiceMapping;
use App\Services\UniversalPaymentLinkService;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Multiple Items Payment Link Generator ===\n\n";

try {
    // Get or create a test client
    $client = Client::firstOrCreate(
        ['code' => 'TEST_MULTI_CLIENT'],
        [
            'name' => 'Test Multi-Items Client',
            'code' => 'TEST_MULTI_CLIENT',
            'api_key' => 'test_multi_key_' . time(),
            'api_secret' => 'test_multi_secret_' . time(),
            'webhook_url' => 'https://webhook.site/multi-items-test',
            'status' => true,
            'settings' => [
                'currency' => 'TZS',
                'timezone' => 'Africa/Dar_es_Salaam'
            ]
        ]
    );

    echo "✅ Client: {$client->name} (ID: {$client->id}, Code: {$client->code})\n";

    // Get Tembo Plus aggregator
    $aggregator = Aggregator::where('name', 'Tembo Plus')->first();
    if (!$aggregator) {
        throw new Exception("Tembo Plus aggregator not found. Please run the seeder first.");
    }

    // Get collection service
    $service = Service::where('name', 'Mobile Money Collection')->first();
    if (!$service) {
        throw new Exception("Mobile Money Collection service not found. Please run the seeder first.");
    }

    // Get or create service mapping
    $serviceMapping = ServiceMapping::firstOrCreate(
        [
            'client_id' => $client->id,
            'aggregator_id' => $aggregator->id,
            'service_id' => $service->id
        ],
        [
            'name' => 'Test Multi-Items Collection Mapping',
            'description' => 'Service mapping for multiple items payment testing',
            'request_mapping' => [
                'customer_phone' => 'msisdn',
                'mobile_network' => 'channel',
                'amount' => 'amount',
                'reference' => 'reference',
                'description' => 'narration',
                'date' => 'transactionDate',
                'webhook_url' => 'callbackUrl'
            ],
            'response_mapping' => [
                'status_code' => 'status',
                'transaction_id' => 'transactionId',
                'message' => 'message'
            ],
            'status' => true,
            'settings' => [
                'timeout' => 30,
                'retry_attempts' => 3
            ]
        ]
    );

    echo "✅ Service Mapping: {$service->name} via {$aggregator->name}\n";

    // Create payment link data with multiple items
    $paymentLinkData = [
        'description' => 'School Fees Payment - Multiple Items',
        'target' => 'individual', // or 'public'
        'customer_name' => 'John Doe',
        'customer_phone' => '255712345678',
        'expires_at' => now()->addDays(30)->format('Y-m-d H:i:s'),
        'items' => [
            [
                'type' => 'service',
                'product_service_reference' => 'SCHOOL_FEES_2024',
                'product_service_name' => 'Tuition Fees 2024',
                'amount' => 500000, // 500,000 TZS
                'description' => 'Annual tuition fees for 2024 academic year',
                'is_required' => true,
                'allow_partial' => false,
                'quantity' => 1
            ],
            [
                'type' => 'service',
                'product_service_reference' => 'LIBRARY_FEES',
                'product_service_name' => 'Library Fees',
                'amount' => 25000, // 25,000 TZS
                'description' => 'Library access and book borrowing fees',
                'is_required' => false,
                'allow_partial' => true,
                'minimum_amount' => 10000,
                'quantity' => 1
            ],
            [
                'type' => 'service',
                'product_service_reference' => 'LAB_FEES',
                'product_service_name' => 'Laboratory Fees',
                'amount' => 75000, // 75,000 TZS
                'description' => 'Science laboratory usage and materials',
                'is_required' => true,
                'allow_partial' => false,
                'quantity' => 1
            ],
            [
                'type' => 'product',
                'product_service_reference' => 'UNIFORM_SET',
                'product_service_name' => 'School Uniform Set',
                'amount' => 45000, // 45,000 TZS
                'description' => 'Complete school uniform (shirt, pants, tie)',
                'is_required' => false,
                'allow_partial' => false,
                'quantity' => 1
            ],
            [
                'type' => 'service',
                'product_service_reference' => 'SPORTS_FEES',
                'product_service_name' => 'Sports and Games Fees',
                'amount' => 15000, // 15,000 TZS
                'description' => 'Sports equipment and facilities access',
                'is_required' => false,
                'allow_partial' => true,
                'minimum_amount' => 5000,
                'quantity' => 1
            ]
        ],
        'metadata' => [
            'customer_reference' => 'STUDENT_2024_001',
            'target_type' => 'individual',
            'is_public_link' => false,
            'school_name' => 'Dar es Salaam International School',
            'academic_year' => '2024',
            'payment_type' => 'school_fees'
        ]
    ];

    echo "\n📋 Payment Items:\n";
    $totalAmount = 0;
    foreach ($paymentLinkData['items'] as $index => $item) {
        $itemTotal = $item['amount'] * $item['quantity'];
        $totalAmount += $itemTotal;
        $status = $item['is_required'] ? 'Required' : 'Optional';
        $partial = $item['allow_partial'] ? 'Partial Allowed' : 'Full Only';
        
        echo "  " . ($index + 1) . ". {$item['product_service_name']}\n";
        echo "     - Amount: " . number_format($item['amount']) . " TZS x {$item['quantity']} = " . number_format($itemTotal) . " TZS\n";
        echo "     - Status: {$status} | {$partial}\n";
        echo "     - Reference: {$item['product_service_reference']}\n\n";
    }

    echo "💰 Total Amount: " . number_format($totalAmount) . " TZS\n\n";

    // Generate the payment link
    $service = new UniversalPaymentLinkService();
    $result = $service->generateUniversalPaymentLink($paymentLinkData, $client);

    if ($result['success']) {
        $paymentData = $result['data'];
        
        echo "✅ Payment Link Generated Successfully!\n\n";
        echo "🔗 Payment Link Details:\n";
        echo "   - Link ID: {$paymentData['link_id']}\n";
        echo "   - Short Code: {$paymentData['short_code']}\n";
        echo "   - Total Amount: " . number_format($paymentData['total_amount']) . " TZS\n";
        echo "   - Items Count: " . $paymentData['items']->count() . "\n";
        echo "   - Created: {$paymentData['created_at']}\n";
        echo "   - Expires: " . ($paymentData['expires_at'] ? $paymentData['expires_at'] : 'Never') . "\n\n";

        echo "🌐 Access URLs:\n";
        echo "   - Public URL: {$paymentData['payment_url']}\n";
        echo "   - API URL: " . url("/api/payment-links/universal/{$paymentData['short_code']}") . "\n";
        echo "   - Stats URL: " . url("/api/payment-links/universal/{$paymentData['short_code']}/stats") . "\n\n";

        echo "📋 Items Summary:\n";
        foreach ($paymentData['items'] as $item) {
            $status = $item['is_required'] ? 'Required' : 'Optional';
            $partial = $item['allow_partial'] ? 'Partial Allowed' : 'Full Only';
            $amount = number_format($item['amount']);
            
            echo "   - {$item['product_service_name']}\n";
            echo "     Amount: {$amount} TZS | Status: {$status} | {$partial}\n";
            echo "     Reference: {$item['product_service_reference']}\n";
            if ($item['minimum_amount']) {
                echo "     Minimum Amount: " . number_format($item['minimum_amount']) . " TZS\n";
            }
            echo "\n";
        }

        echo "🎯 Test the payment link by visiting: {$paymentData['payment_url']}\n";
        echo "📱 QR Code Data: {$paymentData['qr_code_data']}\n";

    } else {
        echo "❌ Failed to generate payment link:\n";
        echo "   Error: {$result['error']}\n";
    }

} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}\n";
    echo "Line: {$e->getLine()}\n";
}

echo "\n=== End ===\n"; 