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

echo "🔧 TEST: Payment Status with Tembo Transaction ID\n";
echo "================================================\n\n";

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
    
    // First, create a collection transaction to get a valid Tembo transaction ID
    echo "💰 Step 1: Create a collection transaction\n";
    echo "-----------------------------------------\n";
    
    $collectionMapping = ServiceMapping::where('client_id', $client->id)
        ->whereHas('service', function($q) {
            $q->where('code', 'MONEY_COLLECTION');
        })->first();
    
    $uniqueRef = 'TEMBO_ID_TEST_' . time() . '_' . rand(1000, 9999);
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
            'description' => 'Tembo ID test collection',
            'reference' => $uniqueRef,
            'date' => date('Y-m-d H:i:s'),
            'webhook_url' => 'https://webhook.site/tembo-id-test'
        ]
    ]);
    
    $collectionData = [
        'customer_phone' => '255712345678',
        'mobile_network' => 'TZ-AIRTEL-C2B',
        'amount' => 1000,
        'description' => 'Tembo ID test collection',
        'reference' => $uniqueRef,
        'date' => date('Y-m-d H:i:s'),
        'webhook_url' => 'https://webhook.site/tembo-id-test'
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
    
    if ($temboTransactionId) {
        // Test the Payment Status with Tembo transaction ID
        echo "🔍 Step 2: Test Payment Status with Tembo Transaction ID\n";
        echo "--------------------------------------------------------\n";
        
        $statusData = [
            'reference' => $temboTransactionId  // Use Tembo transaction ID instead of client reference
        ];
        
        $statusTransaction = Transaction::create([
            'client_id' => $client->id,
            'aggregator_id' => $tembo->id,
            'service_id' => $statusMapping->service->id,
            'service_mapping_id' => $statusMapping->id,
            'transaction_id' => 'TEST_TEMBO_ID_' . time(),
            'client_reference' => 'TEST_TEMBO_ID_' . time(),
            'amount' => 0,
            'currency' => 'TZS',
            'status' => 'pending',
            'request_data' => $statusData
        ]);
        
        echo "📤 Testing Payment Status with Tembo Transaction ID...\n";
        echo "   Request data: " . json_encode($statusData, JSON_PRETTY_PRINT) . "\n";
        
        $statusResult = $esbService->processRequest($statusMapping, $statusData, $statusTransaction);
        
        if ($statusResult['success']) {
            echo "✅ Payment Status FIXED with Tembo Transaction ID!\n";
            $statusData = $statusResult['response'];
            echo "   Transaction ID: " . ($statusData['transaction_id'] ?? 'N/A') . "\n";
            echo "   Status: " . ($statusData['status'] ?? 'N/A') . "\n";
            echo "   Amount: " . ($statusData['amount'] ?? 'N/A') . "\n";
            echo "   Full Response: " . json_encode($statusData, JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "❌ Payment Status still failing: " . ($statusResult['error'] ?? 'Unknown error') . "\n";
            echo "   Error Details: " . json_encode($statusResult, JSON_PRETTY_PRINT) . "\n";
        }
    } else {
        echo "❌ Cannot test Payment Status - no Tembo transaction ID available\n";
    }

    echo "\n🎉 Payment Status with Tembo ID test completed!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} 