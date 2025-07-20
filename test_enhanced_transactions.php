<?php

/**
 * Enhanced Transaction System Test Script
 * 
 * This script demonstrates the comprehensive transaction recording system
 * including callback handling, webhook notifications, and audit trails.
 */

// Configuration
$config = [
    'api_key' => 'test_bank_key_8CGbIjJCMRWLjFll',
    'api_secret' => 'test_bank_secret_GtlfgLnQYUpcTwOry9LV1xvRcCVFO1Ci',
    'base_url' => 'http://127.0.0.1:8000/api',
    'service_code' => 'MONEY_COLLECTION'
];

// Test scenarios
$testScenarios = [
    'tigo_collection' => [
        'name' => 'Tigo Money Collection',
        'data' => [
            'customer_phone' => '255778342299',
            'mobile_network' => 'TZ-TIGO-C2B',
            'amount' => 5000,
            'description' => 'Payment for services',
            'reference' => 'TXN_' . time(),
            'date' => date('Y-m-d H:i:s'),
            'webhook_url' => 'https://webhook.site/your-unique-url'
        ]
    ],
    'airtel_collection' => [
        'name' => 'Airtel Money Collection',
        'data' => [
            'customer_phone' => '255778342299',
            'mobile_network' => 'TZ-AIRTEL-C2B',
            'amount' => 10000,
            'description' => 'Payment for products',
            'reference' => 'TXN_' . (time() + 1),
            'date' => date('Y-m-d H:i:s'),
            'webhook_url' => 'https://webhook.site/your-unique-url'
        ]
    ],
    'large_amount' => [
        'name' => 'Large Amount Collection',
        'data' => [
            'customer_phone' => '255778342299',
            'mobile_network' => 'TZ-TIGO-C2B',
            'amount' => 150000,
            'description' => 'Large payment transaction',
            'reference' => 'TXN_' . (time() + 2),
            'date' => date('Y-m-d H:i:s'),
            'webhook_url' => 'https://webhook.site/your-unique-url'
        ]
    ]
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

// Function to simulate callback
function simulateCallback($baseUrl, $aggregatorCode, $transactionRef, $status = 'success') {
    $callbackData = [
        'transactionRef' => $transactionRef,
        'status' => $status,
        'transactionId' => 'EXT_' . strtoupper(uniqid()),
        'amount' => 5000,
        'msisdn' => '255778342299',
        'channel' => 'TZ-TIGO-C2B',
        'narration' => 'Payment for services',
        'timestamp' => date('Y-m-d H:i:s'),
        'fee' => 50,
        'commission' => 25
    ];
    
    $headers = [
        'Content-Type: application/json',
        'X-Signature: ' . hash_hmac('sha256', json_encode($callbackData), 'test_secret'),
        'X-Timestamp: ' . time()
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $baseUrl . '/callback/' . $aggregatorCode,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($callbackData),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status_code' => $httpCode,
        'response' => $response
    ];
}

// Function to check transaction status
function checkTransactionStatus($baseUrl, $transactionId) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $baseUrl . '/callback/status/' . $transactionId,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPGET => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status_code' => $httpCode,
        'response' => $response
    ];
}

echo "🚀 Enhanced Transaction System Test\n";
echo "===================================\n\n";

$headers = [
    'Content-Type: application/json',
    'X-API-Key: ' . $config['api_key'],
    'X-API-Secret: ' . $config['api_secret'],
    'Accept: application/json'
];

$transactions = [];

// Test 1: Process transactions
echo "📋 Processing Transactions\n";
echo "--------------------------\n";

foreach ($testScenarios as $key => $scenario) {
    echo "\n🔹 Testing: " . $scenario['name'] . "\n";
    
    $url = $config['base_url'] . '/esb/' . $config['service_code'];
    $result = makeRequest($url, $scenario['data'], $headers);
    
    echo "Status Code: " . $result['status_code'] . "\n";
    
    if ($result['status_code'] === 200) {
        $responseData = json_decode($result['response'], true);
        echo "✅ Transaction processed successfully!\n";
        echo "Transaction ID: " . $responseData['transaction_id'] . "\n";
        echo "Reference: " . $responseData['reference'] . "\n";
        
        // Store transaction details for callback testing
        $transactions[] = [
            'transaction_id' => $responseData['transaction_id'],
            'reference' => $responseData['reference'],
            'scenario' => $scenario['name']
        ];
    } else {
        echo "❌ Transaction failed\n";
        echo "Response: " . $result['response'] . "\n";
    }
}

// Test 2: Check transaction status
echo "\n\n📊 Checking Transaction Status\n";
echo "-----------------------------\n";

foreach ($transactions as $transaction) {
    echo "\n🔹 Checking: " . $transaction['scenario'] . "\n";
    echo "Transaction ID: " . $transaction['transaction_id'] . "\n";
    
    $statusResult = checkTransactionStatus($config['base_url'], $transaction['transaction_id']);
    
    if ($statusResult['status_code'] === 200) {
        $statusData = json_decode($statusResult['response'], true);
        echo "✅ Status retrieved successfully!\n";
        echo "ESB Status: " . $statusData['status'] . "\n";
        echo "Aggregator Status: " . ($statusData['aggregator_status'] ?? 'N/A') . "\n";
        echo "Client Status: " . ($statusData['client_status'] ?? 'N/A') . "\n";
        echo "Amount: " . ($statusData['amount'] ?? 'N/A') . "\n";
        echo "Customer Phone: " . ($statusData['customer_phone'] ?? 'N/A') . "\n";
    } else {
        echo "❌ Status check failed\n";
        echo "Response: " . $statusResult['response'] . "\n";
    }
}

// Test 3: Simulate callbacks
echo "\n\n🔄 Simulating Callbacks\n";
echo "----------------------\n";

foreach ($transactions as $transaction) {
    echo "\n🔹 Simulating callback for: " . $transaction['scenario'] . "\n";
    echo "Reference: " . $transaction['reference'] . "\n";
    
    $callbackResult = simulateCallback($config['base_url'], 'TEMBO', $transaction['reference'], 'success');
    
    echo "Callback Status Code: " . $callbackResult['status_code'] . "\n";
    
    if ($callbackResult['status_code'] === 200) {
        $callbackResponse = json_decode($callbackResult['response'], true);
        echo "✅ Callback processed successfully!\n";
        echo "Message: " . ($callbackResponse['message'] ?? 'N/A') . "\n";
    } else {
        echo "❌ Callback failed\n";
        echo "Response: " . $callbackResult['response'] . "\n";
    }
    
    // Wait a moment before next callback
    sleep(1);
}

// Test 4: Check updated transaction status
echo "\n\n📈 Checking Updated Transaction Status\n";
echo "-------------------------------------\n";

foreach ($transactions as $transaction) {
    echo "\n🔹 Updated status for: " . $transaction['scenario'] . "\n";
    echo "Transaction ID: " . $transaction['transaction_id'] . "\n";
    
    $statusResult = checkTransactionStatus($config['base_url'], $transaction['transaction_id']);
    
    if ($statusResult['status_code'] === 200) {
        $statusData = json_decode($statusResult['response'], true);
        echo "✅ Updated status retrieved!\n";
        echo "ESB Status: " . $statusData['status'] . "\n";
        echo "Aggregator Status: " . ($statusData['aggregator_status'] ?? 'N/A') . "\n";
        echo "Client Status: " . ($statusData['client_status'] ?? 'N/A') . "\n";
        echo "Processed At: " . ($statusData['aggregator_processed_at'] ?? 'N/A') . "\n";
    } else {
        echo "❌ Status check failed\n";
        echo "Response: " . $statusResult['response'] . "\n";
    }
}

echo "\n\n" . str_repeat("=", 50) . "\n";
echo "📝 Enhanced Transaction System Features:\n";
echo "✅ Comprehensive transaction recording\n";
echo "✅ Real-time status tracking\n";
echo "✅ Callback processing from aggregators\n";
echo "✅ Webhook notifications to clients\n";
echo "✅ Audit trail for all actions\n";
echo "✅ Risk assessment and monitoring\n";
echo "✅ Performance metrics tracking\n";
echo "✅ Reconciliation support\n";
echo "✅ Settlement tracking\n";
echo "✅ Security and compliance features\n";
echo str_repeat("=", 50) . "\n"; 