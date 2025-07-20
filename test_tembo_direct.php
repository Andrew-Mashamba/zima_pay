<?php

echo "🚀 Direct Tembo API Test\n";
echo "=======================\n\n";

// Tembo credentials
$apiKey = 'bf71ba501b37d989db6224fd';
$apiSecret = 'vd//lqSw67Nl08e7Y2YzWcs2EL+rAIImpl4U4uNHdQg=';
$baseUrl = 'https://sandbox.temboplus.com/tembo/v1';

echo "🔑 Using Tembo Credentials:\n";
echo "   API Key: {$apiKey}\n";
echo "   API Secret: " . substr($apiSecret, 0, 10) . "...\n";
echo "   Base URL: {$baseUrl}\n\n";

// Test 1: Collection Balance (This one works)
echo "💳 Test 1: Collection Balance\n";
echo "-----------------------------\n";

$balanceUrl = $baseUrl . '/wallet/collection-balance';
$balanceHeaders = [
    'Content-Type: application/json',
    'Accept: application/json',
    'x-account-id: ' . $apiKey,
    'x-secret-key: ' . $apiSecret,
    'x-request-id: ' . uniqid()
];

echo "📤 Sending balance request to: {$balanceUrl}\n";
echo "📋 Headers: " . json_encode($balanceHeaders, JSON_PRETTY_PRINT) . "\n";

$balanceResponse = makeRequest('POST', $balanceUrl, [], $balanceHeaders);
echo "📥 Balance Response:\n";
echo "   Status Code: " . $balanceResponse['status_code'] . "\n";
echo "   Response: " . json_encode($balanceResponse['data'], JSON_PRETTY_PRINT) . "\n\n";

// Test 2: Money Collection
echo "💰 Test 2: Money Collection\n";
echo "---------------------------\n";

$collectionUrl = $baseUrl . '/collection';
$uniqueRef = 'DIRECT_TEST_' . time() . '_' . rand(1000, 9999);
$collectionData = [
    'msisdn' => '255712345678',
    'amount' => 1000,
    'narration' => 'Direct test collection',
    'reference' => $uniqueRef
];

$collectionHeaders = [
    'Content-Type: application/json',
    'Accept: application/json',
    'x-account-id: ' . $apiKey,
    'x-secret-key: ' . $apiSecret,
    'x-request-id: ' . uniqid()
];

echo "📤 Sending collection request to: {$collectionUrl}\n";
echo "📋 Headers: " . json_encode($collectionHeaders, JSON_PRETTY_PRINT) . "\n";
echo "📦 Data: " . json_encode($collectionData, JSON_PRETTY_PRINT) . "\n";

$collectionResponse = makeRequest('POST', $collectionUrl, $collectionData, $collectionHeaders);
echo "📥 Collection Response:\n";
echo "   Status Code: " . $collectionResponse['status_code'] . "\n";
echo "   Response: " . json_encode($collectionResponse['data'], JSON_PRETTY_PRINT) . "\n\n";

// Test 3: Collection Statement
echo "📊 Test 3: Collection Statement\n";
echo "-------------------------------\n";

$statementUrl = $baseUrl . '/wallet/collection-statement';
$statementData = [
    'startDate' => date('Y-m-d', strtotime('-7 days')),
    'endDate' => date('Y-m-d')
];

$statementHeaders = [
    'Content-Type: application/json',
    'Accept: application/json',
    'x-account-id: ' . $apiKey,
    'x-secret-key: ' . $apiSecret,
    'x-request-id: ' . uniqid()
];

echo "📤 Sending statement request to: {$statementUrl}\n";
echo "📋 Headers: " . json_encode($statementHeaders, JSON_PRETTY_PRINT) . "\n";
echo "📦 Data: " . json_encode($statementData, JSON_PRETTY_PRINT) . "\n";

$statementResponse = makeRequest('POST', $statementUrl, $statementData, $statementHeaders);
echo "📥 Statement Response:\n";
echo "   Status Code: " . $statementResponse['status_code'] . "\n";
echo "   Response: " . json_encode($statementResponse['data'], JSON_PRETTY_PRINT) . "\n\n";

// Test 4: Payment Status (if we have a transaction ID from collection)
echo "🔍 Test 4: Payment Status\n";
echo "-------------------------\n";

if (isset($collectionResponse['data']['transactionId']) || isset($collectionResponse['data']['reference'])) {
    $statusUrl = $baseUrl . '/collection/status';
    $statusData = [
        'reference' => $uniqueRef
    ];

    $statusHeaders = [
        'Content-Type: application/json',
        'Accept: application/json',
        'x-account-id: ' . $apiKey,
        'x-secret-key: ' . $apiSecret,
        'x-request-id: ' . uniqid()
    ];

    echo "📤 Sending status request to: {$statusUrl}\n";
    echo "📋 Headers: " . json_encode($statusHeaders, JSON_PRETTY_PRINT) . "\n";
    echo "📦 Data: " . json_encode($statusData, JSON_PRETTY_PRINT) . "\n";

    $statusResponse = makeRequest('POST', $statusUrl, $statusData, $statusHeaders);
    echo "📥 Status Response:\n";
    echo "   Status Code: " . $statusResponse['status_code'] . "\n";
    echo "   Response: " . json_encode($statusResponse['data'], JSON_PRETTY_PRINT) . "\n\n";
} else {
    echo "⏭️  Skipping status check - no transaction ID available\n\n";
}

echo "🎉 Direct Tembo API testing completed!\n";

/**
 * Make HTTP request
 */
function makeRequest($method, $url, $data = [], $headers = [])
{
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    // Add debug info
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Get debug info
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    
    if ($error) {
        echo "❌ cURL Error: {$error}\n";
    }
    
    return [
        'status_code' => $httpCode,
        'data' => json_decode($response, true) ?: $response,
        'raw_response' => $response,
        'error' => $error,
        'debug' => $verboseLog
    ];
} 