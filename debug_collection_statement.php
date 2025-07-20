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

echo "🔍 DEBUG: Collection Statement Service\n";
echo "=====================================\n\n";

try {
    // Get the Sample Payment Gateway client
    $client = Client::where('name', 'Sample Payment Gateway')->first();
    $tembo = Aggregator::where('code', 'TEMBO')->first();
    $esbService = new EsbService();
    
    echo "✅ Found client: {$client->name}\n";
    echo "✅ Found aggregator: {$tembo->name}\n\n";
    
    // Get the collection statement mapping
    $statementMapping = ServiceMapping::where('client_id', $client->id)
        ->whereHas('service', function($q) {
            $q->where('code', 'COLLECTION_STATEMENT');
        })->first();
    
    echo "🔧 Collection Statement Mapping:\n";
    echo "   ID: {$statementMapping->id}\n";
    echo "   Status: " . ($statementMapping->status ? 'Active' : 'Inactive') . "\n";
    echo "   Request Mapping: " . json_encode($statementMapping->request_mapping) . "\n";
    echo "   Response Mapping: " . json_encode($statementMapping->response_mapping) . "\n";
    echo "   Service Endpoint: {$statementMapping->service->endpoint}\n";
    echo "   Service Method: {$statementMapping->service->method}\n\n";
    
    // Test 1: Direct API call to understand the issue
    echo "📡 Test 1: Direct API Call to Tembo\n";
    echo "-----------------------------------\n";
    
    $apiKey = $tembo->api_key;
    $apiSecret = $tembo->api_secret;
    $baseUrl = $tembo->api_endpoint;
    $statementUrl = $baseUrl . '/wallet/collection-statement';
    
    $statementData = [
        'startDate' => date('Y-m-d', strtotime('-7 days')),
        'endDate' => date('Y-m-d')
    ];
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'x-account-id: ' . $apiKey,
        'x-secret-key: ' . $apiSecret,
        'x-request-id: ' . uniqid()
    ];
    
    echo "📤 Direct API Request:\n";
    echo "   URL: {$statementUrl}\n";
    echo "   Method: POST\n";
    echo "   Headers: " . json_encode($headers, JSON_PRETTY_PRINT) . "\n";
    echo "   Data: " . json_encode($statementData, JSON_PRETTY_PRINT) . "\n\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $statementUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($statementData));
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
    
    $statementTransaction = Transaction::create([
        'client_id' => $client->id,
        'aggregator_id' => $tembo->id,
        'service_id' => $statementMapping->service->id,
        'service_mapping_id' => $statementMapping->id,
        'transaction_id' => 'DEBUG_STATEMENT_' . time(),
        'client_reference' => 'DEBUG_STATEMENT_' . time(),
        'amount' => 0,
        'currency' => 'TZS',
        'status' => 'pending',
        'request_data' => $statementData
    ]);
    
    echo "📤 ESB Request Data: " . json_encode($statementData, JSON_PRETTY_PRINT) . "\n";
    
    $statementResult = $esbService->processRequest($statementMapping, $statementData, $statementTransaction);
    
    echo "📥 ESB Response:\n";
    echo "   Success: " . ($statementResult['success'] ? 'true' : 'false') . "\n";
    echo "   Status Code: " . ($statementResult['status_code'] ?? 'N/A') . "\n";
    echo "   Response Time: " . ($statementResult['response_time'] ?? 'N/A') . "s\n";
    if (!$statementResult['success']) {
        echo "   Error: " . ($statementResult['error'] ?? 'N/A') . "\n";
    }
    echo "   Full Response: " . json_encode($statementResult, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 3: Check if the issue is with request transformation
    echo "🔄 Test 3: Request Transformation Debug\n";
    echo "--------------------------------------\n";
    
    echo "📤 Original Request Data: " . json_encode($statementData, JSON_PRETTY_PRINT) . "\n";
    
    // Call transformRequest method directly
    $transformedData = $statementMapping->transformRequest($statementData);
    echo "📤 Transformed Request Data: " . json_encode($transformedData, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test 4: Try different request formats
    echo "🧪 Test 4: Alternative Request Formats\n";
    echo "-------------------------------------\n";
    
    // Format 1: Empty request (like balance)
    echo "📤 Format 1: Empty request\n";
    $emptyTransaction = Transaction::create([
        'client_id' => $client->id,
        'aggregator_id' => $tembo->id,
        'service_id' => $statementMapping->service->id,
        'service_mapping_id' => $statementMapping->id,
        'transaction_id' => 'DEBUG_EMPTY_' . time(),
        'client_reference' => 'DEBUG_EMPTY_' . time(),
        'amount' => 0,
        'currency' => 'TZS',
        'status' => 'pending',
        'request_data' => []
    ]);
    
    $emptyResult = $esbService->processRequest($statementMapping, [], $emptyTransaction);
    echo "📥 Empty Request Result: " . ($emptyResult['success'] ? 'SUCCESS' : 'FAILED') . "\n";
    if (!$emptyResult['success']) {
        echo "   Error: " . ($emptyResult['error'] ?? 'N/A') . "\n";
    }
    echo "\n";
    
    // Format 2: Different date format
    echo "📤 Format 2: Different date format\n";
    $altData = [
        'startDate' => date('Y-m-d', strtotime('-1 day')),
        'endDate' => date('Y-m-d')
    ];
    
    $altTransaction = Transaction::create([
        'client_id' => $client->id,
        'aggregator_id' => $tembo->id,
        'service_id' => $statementMapping->service->id,
        'service_mapping_id' => $statementMapping->id,
        'transaction_id' => 'DEBUG_ALT_' . time(),
        'client_reference' => 'DEBUG_ALT_' . time(),
        'amount' => 0,
        'currency' => 'TZS',
        'status' => 'pending',
        'request_data' => $altData
    ]);
    
    $altResult = $esbService->processRequest($statementMapping, $altData, $altTransaction);
    echo "📥 Alt Format Result: " . ($altResult['success'] ? 'SUCCESS' : 'FAILED') . "\n";
    if (!$altResult['success']) {
        echo "   Error: " . ($altResult['error'] ?? 'N/A') . "\n";
    }
    echo "\n";
    
    echo "🎉 Collection Statement debugging completed!\n";
    echo "===========================================\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 