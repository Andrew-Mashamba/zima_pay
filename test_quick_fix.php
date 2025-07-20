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

echo "🚀 Quick Test - Money Collection Fix\n";
echo "====================================\n\n";

try {
    // Get the Sample Payment Gateway client
    $client = Client::where('name', 'Sample Payment Gateway')->first();
    $tembo = Aggregator::where('code', 'TEMBO')->first();
    $esbService = new EsbService();
    
    echo "✅ Found client: {$client->name}\n";
    echo "✅ Found aggregator: {$tembo->name}\n\n";
    
    // Get the money collection mapping
    $collectionMapping = ServiceMapping::where('client_id', $client->id)
        ->whereHas('service', function($q) {
            $q->where('code', 'MONEY_COLLECTION');
        })->first();
    
    echo "🔧 Money Collection Mapping:\n";
    echo "   Request Mapping: " . json_encode($collectionMapping->request_mapping) . "\n";
    echo "   Response Mapping: " . json_encode($collectionMapping->response_mapping) . "\n\n";
    
    // Test Money Collection with correct format
    $uniqueRef = 'QUICK_TEST_' . time() . '_' . rand(1000, 9999);
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
            'description' => 'Quick test collection',
            'reference' => $uniqueRef,
            'date' => date('Y-m-d H:i:s'),
            'webhook_url' => 'https://webhook.site/quick-test'
        ]
    ]);
    
    $collectionData = [
        'customer_phone' => '255712345678',
        'mobile_network' => 'TZ-AIRTEL-C2B',
        'amount' => 1000,
        'description' => 'Quick test collection',
        'reference' => $uniqueRef,
        'date' => date('Y-m-d H:i:s'),
        'webhook_url' => 'https://webhook.site/quick-test'
    ];

    echo "📤 Sending collection request...\n";
    echo "   Request data: " . json_encode($collectionData, JSON_PRETTY_PRINT) . "\n";
    
    $collectionResult = $esbService->processRequest($collectionMapping, $collectionData, $collectionTransaction);
    
    if ($collectionResult['success']) {
        echo "✅ Collection successful!\n";
        echo "   Transaction ID: " . ($collectionTransaction->transaction_id) . "\n";
        echo "   Status: " . ($collectionResult['response']['status'] ?? 'N/A') . "\n";
        echo "   Full Response: " . json_encode($collectionResult['response'], JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "❌ Collection failed: " . ($collectionResult['error'] ?? 'Unknown error') . "\n";
        echo "   Error Details: " . json_encode($collectionResult, JSON_PRETTY_PRINT) . "\n";
    }

    echo "\n🎉 Quick test completed!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} 