<?php

/**
 * Test Script for Tembo Money Collection Service
 * 
 * This script demonstrates how to use the ESB API to collect money
 * from mobile subscribers using the Tembo Money Collection Service.
 */

// Test configuration
$apiKey = 'test_bank_key_8CGbIjJCMRWLjFll';
$apiSecret = 'test_bank_secret_GtlfgLnQYUpcTwOry9LV1xvRcCVFO1Ci';
$baseUrl = 'http://127.0.0.1:8000/api/esb';

// Test data
$testData = [
    'customer_phone' => '255778342299',
    'mobile_network' => 'TZ-AIRTEL-C2B',
    'amount' => 1000,
    'description' => 'Test payment from ESB',
    'reference' => 'TEST_' . time(),
    'date' => date('Y-m-d H:i:s'),
    'webhook_url' => 'https://webhook.site/your-unique-url'
];

echo "🚀 Testing Tembo Money Collection Service\n";
echo "==========================================\n\n";

// Test 1: Health Check
echo "1. Testing Health Check...\n";
$healthResponse = makeRequest('GET', $baseUrl . '/health');
echo "Health Status: " . ($healthResponse['status'] ?? 'Unknown') . "\n\n";

// Test 2: Get Available Services
echo "2. Getting Available Services...\n";
$servicesResponse = makeRequest('GET', $baseUrl . '/services', [], [
    'X-API-Key: ' . $apiKey,
    'X-API-Secret: ' . $apiSecret
]);
echo "Available Services: " . count($servicesResponse['services'] ?? []) . " found\n";
foreach ($servicesResponse['services'] ?? [] as $service) {
    echo "  - {$service['name']} ({$service['code']}) - {$service['aggregator']}\n";
}
echo "\n";

// Test 3: Money Collection Request
echo "3. Testing Money Collection Request...\n";
$collectionResponse = makeRequest('POST', $baseUrl . '/MONEY_COLLECTION', $testData, [
    'X-API-Key: ' . $apiKey,
    'X-API-Secret: ' . $apiSecret,
    'Content-Type: application/json'
]);
echo "Collection Response:\n";
print_r($collectionResponse);
echo "\n";

// Test 4: Invalid Request (Missing Required Fields)
echo "4. Testing Invalid Request (Missing Fields)...\n";
$invalidData = [
    'customer_phone' => '255778342299',
    'amount' => 1000
    // Missing required fields
];
$invalidResponse = makeRequest('POST', $baseUrl . '/MONEY_COLLECTION', $invalidData, [
    'X-API-Key: ' . $apiKey,
    'X-API-Secret: ' . $apiSecret,
    'Content-Type: application/json'
]);
echo "Invalid Request Response:\n";
print_r($invalidResponse);
echo "\n";

// Test 5: Invalid Credentials
echo "5. Testing Invalid Credentials...\n";
$invalidAuthResponse = makeRequest('POST', $baseUrl . '/MONEY_COLLECTION', $testData, [
    'X-API-Key: invalid_key',
    'X-API-Secret: invalid_secret',
    'Content-Type: application/json'
]);
echo "Invalid Credentials Response:\n";
print_r($invalidAuthResponse);
echo "\n";

echo "✅ Testing completed!\n";

/**
 * Make HTTP request
 */
function makeRequest($method, $url, $data = [], $headers = [])
{
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status_code' => $httpCode,
        'data' => json_decode($response, true) ?: $response
    ];
}

/**
 * Print formatted response
 */
function printResponse($response)
{
    echo "Status Code: " . $response['status_code'] . "\n";
    echo "Response: " . json_encode($response['data'], JSON_PRETTY_PRINT) . "\n";
} 