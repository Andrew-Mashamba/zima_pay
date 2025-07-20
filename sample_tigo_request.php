<?php

/**
 * Sample Client Request for Tigo Money Collection
 * 
 * This script demonstrates how a client would make a request to collect money
 * from a Tigo mobile subscriber through the ZIMA ESB system.
 */

// Client Configuration
$config = [
    'api_key' => 'test_bank_key_8CGbIjJCMRWLjFll',
    'api_secret' => 'test_bank_secret_GtlfgLnQYUpcTwOry9LV1xvRcCVFO1Ci',
    'base_url' => 'http://127.0.0.1:8000/api/esb',
    'service_code' => 'MONEY_COLLECTION'
];

// Sample Tigo Money Collection Request
$requestData = [
    'customer_phone' => '255778342299',        // Tigo mobile number (Tanzania format)
    'mobile_network' => 'TZ-TIGO-C2B',         // Tigo network code
    'amount' => 5000,                          // Amount in Tanzanian Shillings (TZS)
    'description' => 'Payment for services',   // Transaction description
    'reference' => 'TXN_' . time(),            // Unique transaction reference
    'date' => date('Y-m-d H:i:s'),             // Current timestamp
    'webhook_url' => 'https://your-webhook-url.com/callback'  // Webhook for notifications
];

// Function to make API request
function makeRequest($url, $data, $headers) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'status_code' => $httpCode,
        'response' => $response,
        'error' => $error
    ];
}

// Prepare headers
$headers = [
    'Content-Type: application/json',
    'X-API-Key: ' . $config['api_key'],
    'X-API-Secret: ' . $config['api_secret'],
    'Accept: application/json'
];

echo "🚀 Tigo Money Collection Request\n";
echo "================================\n\n";

echo "📋 Request Details:\n";
echo "Service: " . $config['service_code'] . "\n";
echo "Customer Phone: " . $requestData['customer_phone'] . "\n";
echo "Network: " . $requestData['mobile_network'] . "\n";
echo "Amount: TZS " . number_format($requestData['amount']) . "\n";
echo "Reference: " . $requestData['reference'] . "\n";
echo "Description: " . $requestData['description'] . "\n\n";

// Make the request
$url = $config['base_url'] . '/' . $config['service_code'];
$result = makeRequest($url, $requestData, $headers);

echo "📤 Request URL: " . $url . "\n";
echo "📤 Request Headers:\n";
foreach ($headers as $header) {
    echo "  " . $header . "\n";
}
echo "\n📤 Request Body:\n";
echo json_encode($requestData, JSON_PRETTY_PRINT) . "\n\n";

echo "📥 Response:\n";
echo "Status Code: " . $result['status_code'] . "\n";

if ($result['error']) {
    echo "Error: " . $result['error'] . "\n";
} else {
    $responseData = json_decode($result['response'], true);
    if ($responseData) {
        echo "Response Body:\n";
        echo json_encode($responseData, JSON_PRETTY_PRINT) . "\n\n";
        
        // Interpret the response
        if ($result['status_code'] === 200) {
            echo "✅ SUCCESS: Money collection request processed successfully!\n";
            if (isset($responseData['data']['transactionId'])) {
                echo "Transaction ID: " . $responseData['data']['transactionId'] . "\n";
            }
            if (isset($responseData['data']['status'])) {
                echo "Status: " . $responseData['data']['status'] . "\n";
            }
            if (isset($responseData['data']['message'])) {
                echo "Message: " . $responseData['data']['message'] . "\n";
            }
        } else {
            echo "❌ ERROR: Request failed\n";
            if (isset($responseData['error'])) {
                echo "Error: " . $responseData['error'] . "\n";
            }
        }
    } else {
        echo "Raw Response: " . $result['response'] . "\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "📝 Notes:\n";
echo "- This is a sample request for Tigo money collection\n";
echo "- The customer will receive a USSD push notification\n";
echo "- Transaction status will be updated via webhook\n";
echo "- Monitor the transaction status using the reference number\n";
echo "- Sandbox mode returns mock responses for testing\n"; 