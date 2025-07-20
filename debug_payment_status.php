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

echo "🔍 DEBUG: Payment Status Service\n";
echo "================================\n\n";

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
    echo "   ID: {$statusMapping->id}\n";
    echo "   Status: " . ($statusMapping->status ? 'Active' : 'Inactive') . "\n";
    echo "   Request Mapping: " . json_encode($statusMapping->request_mapping) . "\n";
    echo "   Response Mapping: " . json_encode($statusMapping->response_mapping) . "\n";
    echo "   Service Endpoint: {$statusMapping->service->endpoint}\n";
    echo "   Service Method: {$statusMapping->service->method}\n\n";
    
    // First, create a collection transaction to get a valid reference
    echo "💰 Step 1: Create a collection transaction for testing\n";
    echo "-----------------------------------------------------\n";
    
    $collectionMapping = ServiceMapping::where('client_id', $client->id)
        ->whereHas('service', function($q) {
            $q->where('code', 'MONEY_COLLECTION');
        })->first();
    
    $uniqueRef = 'STATUS_TEST_' . time() . '_' . rand(1000, 9999);
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
            'description' => 'Status test collection',
            'reference' => $uniqueRef,
            'date' => date('Y-m-d H:i:s'),
            'webhook_url' => 'https://webhook.site/status-test'
        ]
    ]);
    
    $collectionData = [
        'customer_phone' => '255712345678',
        'mobile_network' => 'TZ-AIRTEL-C2B',
        'amount' => 1000,
        'description' => 'Status test collection',
        'reference' => $uniqueRef,
        'date' => date('Y-m-d H:i:s'),
        'webhook_url' => 'https://webhook.site/status-test'
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
    
    // Test 1: Direct API call to understand the issue
    echo "📡 Test 1: Direct API Call to Tembo\n";
    echo "-----------------------------------\n";
    
    $apiKey = $tembo->api_key;
    $apiSecret = $tembo->api_secret;
    $baseUrl = $tembo->api_endpoint;
    $statusUrl = $baseUrl . '/collection/status';
    
    $statusData = [
        'reference' => $uniqueRef
    ];
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'x-account-id: ' . $apiKey,
        'x-secret-key: ' . $apiSecret,
        'x-request-id: ' . uniqid()
    ];
    
    echo "📤 Direct API Request:\n";
    echo "   URL: {$statusUrl}\n";
    echo "   Method: POST\n";
    echo "   Headers: " . json_encode($headers, JSON_PRETTY_PRINT) . "\n";
    echo "   Data: " . json_encode($statusData, JSON_PRETTY_PRINT) . "\n\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $statusUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($statusData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "📥 Direct API Response:\n";
    echo "   Status Code: {$httpCode}\n";
    if ($error) {
        echo "   cURL Error: {$error}\n";
    }
    echo "   Response: " . json_encode(json_decode($response, true), JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 2: ESB Service call with detailed logging
    echo "🔧 Test 2: ESB Service Call\n";
    echo "---------------------------\n";
    
    $statusTransaction = Transaction::create([
        'client_id' => $client->id,
        'aggregator_id' => $tembo->id,
        'service_id' => $statusMapping->service->id,
        'service_mapping_id' => $statusMapping->id,
        'transaction_id' => 'DEBUG_STATUS_' . time(),
        'client_reference' => 'DEBUG_STATUS_' . time(),
        'amount' => 0,
        'currency' => 'TZS',
        'status' => 'pending',
        'request_data' => $statusData
    ]);
    
    echo "📤 ESB Request Data: " . json_encode($statusData, JSON_PRETTY_PRINT) . "\n";
    
    $statusResult = $esbService->processRequest($statusMapping, $statusData, $statusTransaction);
    
    echo "📥 ESB Response:\n";
    echo "   Success: " . ($statusResult['success'] ? 'true' : 'false') . "\n";
    echo "   Status Code: " . ($statusResult['status_code'] ?? 'N/A') . "\n";
    echo "   Response Time: " . ($statusResult['response_time'] ?? 'N/A') . "s\n";
    if (!$statusResult['success']) {
        echo "   Error: " . ($statusResult['error'] ?? 'N/A') . "\n";
    }
    echo "   Full Response: " . json_encode($statusResult, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 3: Check if the issue is with request transformation
    echo "🔄 Test 3: Request Transformation Debug\n";
    echo "--------------------------------------\n";
    
    echo "📤 Original Request Data: " . json_encode($statusData, JSON_PRETTY_PRINT) . "\n";
    
    // Call transformRequest method directly
    $transformedData = $statusMapping->transformRequest($statusData);
    echo "📤 Transformed Request Data: " . json_encode($transformedData, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 4: Try different request formats
    echo "🧪 Test 4: Alternative Request Formats\n";
    echo "-------------------------------------\n";
    
    // Format 1: Using transaction ID instead of reference
    if ($temboTransactionId) {
        echo "📤 Format 1: Using transaction ID\n";
        $altData1 = [
            'transactionId' => $temboTransactionId
        ];
        
        $altTransaction1 = Transaction::create([
            'client_id' => $client->id,
            'aggregator_id' => $tembo->id,
            'service_id' => $statusMapping->service->id,
            'service_mapping_id' => $statusMapping->id,
            'transaction_id' => 'DEBUG_ALT1_' . time(),
            'client_reference' => 'DEBUG_ALT1_' . time(),
            'amount' => 0,
            'currency' => 'TZS',
            'status' => 'pending',
            'request_data' => $altData1
        ]);
        
        $altResult1 = $esbService->processRequest($statusMapping, $altData1, $altTransaction1);
        echo "📥 Alt Format 1 Result: " . ($altResult1['success'] ? 'SUCCESS' : 'FAILED') . "\n";
        if (!$altResult1['success']) {
            echo "   Error: " . ($altResult1['error'] ?? 'N/A') . "\n";
        }
        echo "\n";
    }
    
    // Format 2: Using both reference and transaction ID
    if ($temboTransactionId) {
        echo "📤 Format 2: Using both reference and transaction ID\n";
        $altData2 = [
            'reference' => $uniqueRef,
            'transactionId' => $temboTransactionId
        ];
        
        $altTransaction2 = Transaction::create([
            'client_id' => $client->id,
            'aggregator_id' => $tembo->id,
            'service_id' => $statusMapping->service->id,
            'service_mapping_id' => $statusMapping->id,
            'transaction_id' => 'DEBUG_ALT2_' . time(),
            'client_reference' => 'DEBUG_ALT2_' . time(),
            'amount' => 0,
            'currency' => 'TZS',
            'status' => 'pending',
            'request_data' => $altData2
        ]);
        
        $altResult2 = $esbService->processRequest($statusMapping, $altData2, $altTransaction2);
        echo "📥 Alt Format 2 Result: " . ($altResult2['success'] ? 'SUCCESS' : 'FAILED') . "\n";
        if (!$altResult2['success']) {
            echo "   Error: " . ($altResult2['error'] ?? 'N/A') . "\n";
        }
        echo "\n";
    }
    
    echo "🎉 Payment Status debugging completed!\n";
    echo "=====================================\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 