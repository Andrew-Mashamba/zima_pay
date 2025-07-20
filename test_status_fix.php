<?php

require_once 'vendor/autoload.php';

use App\Models\Client;
use App\Models\ServiceMapping;
use App\Models\Aggregator;
use App\Models\Transaction;
use App\Services\EsbService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔧 TEST: Payment Status Service Fix\n";
echo "==================================\n\n";

try {
    // Get the Sample Payment Gateway client
    $client = Client::where('name', 'Sample Payment Gateway')->first();
    $tembo = Aggregator::where('code', 'TEMBO')->first();
    $esbService = new EsbService();
    
    echo "✅ Found client: {$client->name}\n";
    echo "✅ Found aggregator: {$tembo->name}\n\n";
    
    // Get the payment status mapping
    $statusMapping = ServiceMapping::where('client_id', $client->id)
        ->whereHas('service', function($q) {
            $q->where('code', 'PAYMENT_STATUS');
        })->first();
    
    echo "🔧 Payment Status Mapping:\n";
    echo "   Request Mapping: " . json_encode($statusMapping->request_mapping) . "\n";
    echo "   Response Mapping: " . json_encode($statusMapping->response_mapping) . "\n\n";
    
    // First, create a collection transaction to get a valid reference
    echo "💰 Step 1: Create a collection transaction for testing\n";
    echo "-----------------------------------------------------\n";
    
    $collectionMapping = ServiceMapping::where('client_id', $client->id)
        ->whereHas('service', function($q) {
            $q->where('code', 'MONEY_COLLECTION');
        })->first();
    
    $uniqueRef = 'STATUS_FIX_' . time() . '_' . rand(1000, 9999);
    $collectionTransaction = Transaction::create([
        'client_id' => $client->id,
        'aggregator_id' => $tembo->id,
        'service_id' => $collectionMapping->service->id,
        'service_mapping_id' => $collectionMapping->id,
        'transaction_id' => $uniqueRef,
        'client_reference' => $uniqueRef,
        'amount' => 1000,
        'currency' => 'TZS',
        'status' => 'pending',
        'request_data' => [
            'customer_phone' => '255712345678',
            'mobile_network' => 'TZ-AIRTEL-C2B',
            'amount' => 1000,
            'description' => 'Status fix test collection',
            'reference' => $uniqueRef,
            'date' => date('Y-m-d H:i:s'),
            'webhook_url' => 'https://webhook.site/status-fix-test'
        ]
    ]);
    
    $collectionData = [
        'customer_phone' => '255712345678',
        'mobile_network' => 'TZ-AIRTEL-C2B',
        'amount' => 1000,
        'description' => 'Status fix test collection',
        'reference' => $uniqueRef,
        'date' => date('Y-m-d H:i:s'),
        'webhook_url' => 'https://webhook.site/status-fix-test'
    ];

    echo "📤 Creating collection transaction: {$uniqueRef}\n";
    $collectionResult = $esbService->processRequest($collectionMapping, $collectionData, $collectionTransaction);
    
    if ($collectionResult['success']) {
        echo "✅ Collection created successfully!\n";
        $temboTransactionId = $collectionResult['response']['transaction_id'] ?? null;
        echo "   Tembo Transaction ID: " . ($temboTransactionId ?? 'N/A') . "\n";
    } else {
        echo "❌ Collection failed: " . ($collectionResult['error'] ?? 'Unknown error') . "\n";
        $temboTransactionId = null;
    }
    echo "\n";
    
    // Test the Payment Status fix
    echo "🔍 Step 2: Test Payment Status with fix\n";
    echo "----------------------------------------\n";
    
    $statusData = [
        'reference' => $uniqueRef
    ];
    
    $statusTransaction = Transaction::create([
        'client_id' => $client->id,
        'aggregator_id' => $tembo->id,
        'service_id' => $statusMapping->service->id,
        'service_mapping_id' => $statusMapping->id,
        'transaction_id' => 'TEST_STATUS_FIX_' . time(),
        'client_reference' => 'TEST_STATUS_FIX_' . time(),
        'amount' => 0,
        'currency' => 'TZS',
        'status' => 'pending',
        'request_data' => $statusData
    ]);
    
    echo "📤 Testing Payment Status with fix...\n";
    echo "   Request data: " . json_encode($statusData, JSON_PRETTY_PRINT) . "\n";
    
    $statusResult = $esbService->processRequest($statusMapping, $statusData, $statusTransaction);
    
    if ($statusResult['success']) {
        echo "✅ Payment Status FIXED!\n";
        $statusData = $statusResult['response'];
        echo "   Transaction ID: " . ($statusData['transaction_id'] ?? 'N/A') . "\n";
        echo "   Status: " . ($statusData['status'] ?? 'N/A') . "\n";
        echo "   Amount: " . ($statusData['amount'] ?? 'N/A') . "\n";
        echo "   Full Response: " . json_encode($statusData, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "❌ Payment Status still failing: " . ($statusResult['error'] ?? 'Unknown error') . "\n";
        echo "   Error Details: " . json_encode($statusResult, JSON_PRETTY_PRINT) . "\n";
    }

    echo "\n🎉 Payment Status fix test completed!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} 