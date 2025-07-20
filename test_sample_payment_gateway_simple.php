<?php

require_once 'vendor/autoload.php';

use App\Models\Client;
use App\Models\Service;
use App\Models\ServiceMapping;
use App\Models\Aggregator;
use App\Models\Transaction;
use App\Services\EsbService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🚀 Simple Test for Sample Payment Gateway Client\n";
echo "===============================================\n\n";

try {
    // Get the Sample Payment Gateway client
    $client = Client::where('name', 'Sample Payment Gateway')->first();
    if (!$client) {
        throw new Exception("Sample Payment Gateway client not found!");
    }
    echo "✅ Found client: {$client->name}\n";

    // Get Tembo aggregator
    $tembo = Aggregator::where('code', 'TEMBO')->first();
    if (!$tembo) {
        throw new Exception("Tembo aggregator not found!");
    }
    echo "✅ Found aggregator: {$tembo->name}\n";
    echo "   API Endpoint: {$tembo->api_endpoint}\n";
    echo "   API Key: " . substr($tembo->api_key, 0, 10) . "...\n";
    echo "   API Secret: " . substr($tembo->api_secret, 0, 10) . "...\n\n";

    // Get all services
    $services = Service::whereIn('code', [
        'MONEY_COLLECTION',
        'COLLECTION_BALANCE', 
        'COLLECTION_STATEMENT',
        'PAYMENT_STATUS'
    ])->get();

    echo "📋 Available Services:\n";
    foreach ($services as $service) {
        echo "  - {$service->name} ({$service->code})\n";
        echo "    Endpoint: {$service->endpoint}\n";
        echo "    Method: {$service->method}\n";
    }
    echo "\n";

    // Get service mappings for this client
    $mappings = ServiceMapping::where('client_id', $client->id)
        ->with(['service', 'aggregator'])
        ->get();

    echo "🔗 Service Mappings for {$client->name}:\n";
    foreach ($mappings as $mapping) {
        echo "  - {$mapping->service->name} via {$mapping->aggregator->name}\n";
        echo "    Mapping ID: {$mapping->id}\n";
        echo "    Status: " . ($mapping->status ? 'Active' : 'Inactive') . "\n";
    }
    echo "\n";

    // Initialize ESB Service
    $esbService = new EsbService();
    echo "🔧 ESB Service initialized\n\n";

    // Test 1: Collection Balance (This one works)
    echo "💳 Test 1: Collection Balance\n";
    echo "-----------------------------\n";
    
    // Get the balance mapping
    $balanceMapping = ServiceMapping::where('client_id', $client->id)
        ->whereHas('service', function($q) {
            $q->where('code', 'COLLECTION_BALANCE');
        })->first();
    
    if (!$balanceMapping) {
        throw new Exception("Collection balance mapping not found for client!");
    }
    
    // Create a transaction record for balance check
    $balanceTransaction = Transaction::create([
        'client_id' => $client->id,
        'aggregator_id' => $tembo->id,
        'service_id' => $balanceMapping->service->id,
        'service_mapping_id' => $balanceMapping->id,
        'transaction_id' => 'BALANCE_TEST_' . time(),
        'client_reference' => 'BALANCE_TEST_' . time(),
        'amount' => 0,
        'currency' => 'TZS',
        'status' => 'pending',
        'request_data' => []
    ]);
    
    echo "📤 Fetching collection balance...\n";
    $balanceResult = $esbService->processRequest($balanceMapping, [], $balanceTransaction);
    
    if ($balanceResult['success']) {
        echo "✅ Balance retrieved successfully!\n";
        $balanceData = $balanceResult['response'];
        echo "   Balance: " . ($balanceData['balance'] ?? 'N/A') . "\n";
        echo "   Currency: " . ($balanceData['currency'] ?? 'N/A') . "\n";
        
        // Show full response for debugging
        echo "   Full Response: " . json_encode($balanceData, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "❌ Balance retrieval failed: " . ($balanceResult['error'] ?? 'Unknown error') . "\n";
        echo "   Error Details: " . json_encode($balanceResult, JSON_PRETTY_PRINT) . "\n";
    }
    echo "\n";

    // Test 2: Money Collection with unique reference
    echo "💰 Test 2: Money Collection (Unique Reference)\n";
    echo "---------------------------------------------\n";
    
    // Get the money collection mapping
    $collectionMapping = ServiceMapping::where('client_id', $client->id)
        ->whereHas('service', function($q) {
            $q->where('code', 'MONEY_COLLECTION');
        })->first();
    
    if (!$collectionMapping) {
        throw new Exception("Money collection mapping not found for client!");
    }
    
    // Create a transaction record with unique reference
    $uniqueRef = 'UNIQUE_TEST_' . time() . '_' . rand(1000, 9999);
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
            'amount' => 1000,
            'currency' => 'TZS',
            'phone_number' => '255712345678',
            'reference' => $uniqueRef,
            'description' => 'Unique test collection for Sample Payment Gateway'
        ]
    ]);
    
    $collectionData = [
        'amount' => 1000,
        'currency' => 'TZS',
        'phone_number' => '255712345678',
        'reference' => $uniqueRef,
        'description' => 'Unique test collection for Sample Payment Gateway'
    ];

    echo "📤 Sending collection request with unique reference: {$uniqueRef}\n";
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
    echo "\n";

    // Test 3: Check Service Mapping Configuration
    echo "🔧 Test 3: Service Mapping Configuration\n";
    echo "----------------------------------------\n";
    
    foreach ($mappings as $mapping) {
        echo "Mapping: {$mapping->service->name}\n";
        echo "  - ID: {$mapping->id}\n";
        echo "  - Status: " . ($mapping->status ? 'Active' : 'Inactive') . "\n";
        echo "  - Request Mapping: " . json_encode($mapping->request_mapping) . "\n";
        echo "  - Response Mapping: " . json_encode($mapping->response_mapping) . "\n";
        echo "  - Transformation Rules: " . json_encode($mapping->transformation_rules) . "\n";
        echo "\n";
    }

    echo "🎉 Simple testing completed for Sample Payment Gateway!\n";
    echo "====================================================\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 