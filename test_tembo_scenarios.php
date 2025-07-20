<?php

/**
 * Comprehensive Test Script for Tembo Money Collection Service
 * 
 * This script tests various scenarios for the ESB API including:
 * - Health checks
 * - Authentication
 * - Valid requests
 * - Invalid requests
 * - Rate limiting
 * - Error handling
 * - Different mobile networks
 * - Various amounts and scenarios
 */

// Configuration
$config = [
    'base_url' => 'http://127.0.0.1:8000/api/esb',
    'api_key' => 'test_bank_key_8CGbIjJCMRWLjFll',
    'api_secret' => 'test_bank_secret_GtlfgLnQYUpcTwOry9LV1xvRcCVFO1Ci',
    'service_code' => 'MONEY_COLLECTION'
];

// Test scenarios
$scenarios = [
    'health_check' => [
        'name' => 'Health Check',
        'method' => 'GET',
        'endpoint' => '/health',
        'headers' => [],
        'data' => null,
        'expected_status' => 200
    ],
    
    'get_services' => [
        'name' => 'Get Available Services',
        'method' => 'GET',
        'endpoint' => '/services',
        'headers' => [
            'X-API-Key: ' . $config['api_key'],
            'X-API-Secret: ' . $config['api_secret']
        ],
        'data' => null,
        'expected_status' => 200
    ],
    
    'valid_airtel_request' => [
        'name' => 'Valid Airtel Money Collection',
        'method' => 'POST',
        'endpoint' => '/' . $config['service_code'],
        'headers' => [
            'X-API-Key: ' . $config['api_key'],
            'X-API-Secret: ' . $config['api_secret'],
            'Content-Type: application/json'
        ],
        'data' => [
            'customer_phone' => '255778342299',
            'mobile_network' => 'TZ-AIRTEL-C2B',
            'amount' => 1000,
            'description' => 'Test payment from ESB - Airtel',
            'reference' => 'TEST_AIRTEL_' . time(),
            'date' => date('Y-m-d H:i:s'),
            'webhook_url' => 'https://webhook.site/test-airtel'
        ],
        'expected_status' => 200
    ],
    
    'valid_tigo_request' => [
        'name' => 'Valid Tigo Money Collection',
        'method' => 'POST',
        'endpoint' => '/' . $config['service_code'],
        'headers' => [
            'X-API-Key: ' . $config['api_key'],
            'X-API-Secret: ' . $config['api_secret'],
            'Content-Type: application/json'
        ],
        'data' => [
            'customer_phone' => '255714123456',
            'mobile_network' => 'TZ-TIGO-C2B',
            'amount' => 2500,
            'description' => 'Test payment from ESB - Tigo',
            'reference' => 'TEST_TIGO_' . time(),
            'date' => date('Y-m-d H:i:s'),
            'webhook_url' => 'https://webhook.site/test-tigo'
        ],
        'expected_status' => 200
    ],
    
    'large_amount_request' => [
        'name' => 'Large Amount Request',
        'method' => 'POST',
        'endpoint' => '/' . $config['service_code'],
        'headers' => [
            'X-API-Key: ' . $config['api_key'],
            'X-API-Secret: ' . $config['api_secret'],
            'Content-Type: application/json'
        ],
        'data' => [
            'customer_phone' => '255778342299',
            'mobile_network' => 'TZ-AIRTEL-C2B',
            'amount' => 50000,
            'description' => 'Large amount test payment',
            'reference' => 'TEST_LARGE_' . time(),
            'date' => date('Y-m-d H:i:s'),
            'webhook_url' => 'https://webhook.site/test-large'
        ],
        'expected_status' => 200
    ],
    
    'small_amount_request' => [
        'name' => 'Small Amount Request',
        'method' => 'POST',
        'endpoint' => '/' . $config['service_code'],
        'headers' => [
            'X-API-Key: ' . $config['api_key'],
            'X-API-Secret: ' . $config['api_secret'],
            'Content-Type: application/json'
        ],
        'data' => [
            'customer_phone' => '255778342299',
            'mobile_network' => 'TZ-AIRTEL-C2B',
            'amount' => 100,
            'description' => 'Small amount test payment',
            'reference' => 'TEST_SMALL_' . time(),
            'date' => date('Y-m-d H:i:s'),
            'webhook_url' => 'https://webhook.site/test-small'
        ],
        'expected_status' => 200
    ],
    
    'missing_required_fields' => [
        'name' => 'Missing Required Fields',
        'method' => 'POST',
        'endpoint' => '/' . $config['service_code'],
        'headers' => [
            'X-API-Key: ' . $config['api_key'],
            'X-API-Secret: ' . $config['api_secret'],
            'Content-Type: application/json'
        ],
        'data' => [
            'customer_phone' => '255778342299',
            'amount' => 1000
            // Missing required fields
        ],
        'expected_status' => 400
    ],
    
    'invalid_phone_number' => [
        'name' => 'Invalid Phone Number',
        'method' => 'POST',
        'endpoint' => '/' . $config['service_code'],
        'headers' => [
            'X-API-Key: ' . $config['api_key'],
            'X-API-Secret: ' . $config['api_secret'],
            'Content-Type: application/json'
        ],
        'data' => [
            'customer_phone' => '12345', // Invalid phone number
            'mobile_network' => 'TZ-AIRTEL-C2B',
            'amount' => 1000,
            'description' => 'Test with invalid phone',
            'reference' => 'TEST_INVALID_PHONE_' . time(),
            'date' => date('Y-m-d H:i:s'),
            'webhook_url' => 'https://webhook.site/test-invalid-phone'
        ],
        'expected_status' => 400
    ],
    
    'invalid_mobile_network' => [
        'name' => 'Invalid Mobile Network',
        'method' => 'POST',
        'endpoint' => '/' . $config['service_code'],
        'headers' => [
            'X-API-Key: ' . $config['api_key'],
            'X-API-Secret: ' . $config['api_secret'],
            'Content-Type: application/json'
        ],
        'data' => [
            'customer_phone' => '255778342299',
            'mobile_network' => 'INVALID-NETWORK',
            'amount' => 1000,
            'description' => 'Test with invalid network',
            'reference' => 'TEST_INVALID_NETWORK_' . time(),
            'date' => date('Y-m-d H:i:s'),
            'webhook_url' => 'https://webhook.site/test-invalid-network'
        ],
        'expected_status' => 400
    ],
    
    'invalid_amount' => [
        'name' => 'Invalid Amount (Zero)',
        'method' => 'POST',
        'endpoint' => '/' . $config['service_code'],
        'headers' => [
            'X-API-Key: ' . $config['api_key'],
            'X-API-Secret: ' . $config['api_secret'],
            'Content-Type: application/json'
        ],
        'data' => [
            'customer_phone' => '255778342299',
            'mobile_network' => 'TZ-AIRTEL-C2B',
            'amount' => 0,
            'description' => 'Test with zero amount',
            'reference' => 'TEST_ZERO_AMOUNT_' . time(),
            'date' => date('Y-m-d H:i:s'),
            'webhook_url' => 'https://webhook.site/test-zero-amount'
        ],
        'expected_status' => 400
    ],
    
    'invalid_credentials' => [
        'name' => 'Invalid API Credentials',
        'method' => 'POST',
        'endpoint' => '/' . $config['service_code'],
        'headers' => [
            'X-API-Key: invalid_key',
            'X-API-Secret: invalid_secret',
            'Content-Type: application/json'
        ],
        'data' => [
            'customer_phone' => '255778342299',
            'mobile_network' => 'TZ-AIRTEL-C2B',
            'amount' => 1000,
            'description' => 'Test with invalid credentials',
            'reference' => 'TEST_INVALID_CREDS_' . time(),
            'date' => date('Y-m-d H:i:s'),
            'webhook_url' => 'https://webhook.site/test-invalid-creds'
        ],
        'expected_status' => 401
    ],
    
    'missing_credentials' => [
        'name' => 'Missing API Credentials',
        'method' => 'POST',
        'endpoint' => '/' . $config['service_code'],
        'headers' => [
            'Content-Type: application/json'
        ],
        'data' => [
            'customer_phone' => '255778342299',
            'mobile_network' => 'TZ-AIRTEL-C2B',
            'amount' => 1000,
            'description' => 'Test without credentials',
            'reference' => 'TEST_NO_CREDS_' . time(),
            'date' => date('Y-m-d H:i:s'),
            'webhook_url' => 'https://webhook.site/test-no-creds'
        ],
        'expected_status' => 401
    ],
    
    'invalid_service_code' => [
        'name' => 'Invalid Service Code',
        'method' => 'POST',
        'endpoint' => '/INVALID_SERVICE',
        'headers' => [
            'X-API-Key: ' . $config['api_key'],
            'X-API-Secret: ' . $config['api_secret'],
            'Content-Type: application/json'
        ],
        'data' => [
            'customer_phone' => '255778342299',
            'mobile_network' => 'TZ-AIRTEL-C2B',
            'amount' => 1000,
            'description' => 'Test with invalid service',
            'reference' => 'TEST_INVALID_SERVICE_' . time(),
            'date' => date('Y-m-d H:i:s'),
            'webhook_url' => 'https://webhook.site/test-invalid-service'
        ],
        'expected_status' => 404
    ],
    
    'malformed_json' => [
        'name' => 'Malformed JSON',
        'method' => 'POST',
        'endpoint' => '/' . $config['service_code'],
        'headers' => [
            'X-API-Key: ' . $config['api_key'],
            'X-API-Secret: ' . $config['api_secret'],
            'Content-Type: application/json'
        ],
        'data' => '{"invalid": json}', // Malformed JSON string
        'expected_status' => 400
    ]
];

// Test results storage
$results = [];

echo "🚀 Tembo Money Collection Service - Comprehensive Test Suite\n";
echo "============================================================\n\n";

// Run all scenarios
foreach ($scenarios as $key => $scenario) {
    echo "Testing: {$scenario['name']}\n";
    echo str_repeat("-", 50) . "\n";
    
    $result = runTest($config['base_url'] . $scenario['endpoint'], $scenario);
    $results[$key] = $result;
    
    // Display result
    displayResult($result, $scenario);
    echo "\n";
    
    // Add delay between tests to avoid rate limiting
    if ($key !== array_key_last($scenarios)) {
        sleep(1);
    }
}

// Summary
echo "📊 Test Summary\n";
echo "===============\n";
$passed = 0;
$failed = 0;

foreach ($results as $key => $result) {
    if ($result['status_code'] === $scenarios[$key]['expected_status']) {
        $passed++;
        echo "✅ {$scenarios[$key]['name']}: PASSED\n";
    } else {
        $failed++;
        echo "❌ {$scenarios[$key]['name']}: FAILED (Expected: {$scenarios[$key]['expected_status']}, Got: {$result['status_code']})\n";
    }
}

echo "\n📈 Results: {$passed} passed, {$failed} failed out of " . count($scenarios) . " tests\n";

if ($failed === 0) {
    echo "🎉 All tests passed! The ESB is working correctly.\n";
} else {
    echo "⚠️  Some tests failed. Check the details above.\n";
}

/**
 * Run a single test
 */
function runTest($url, $scenario)
{
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $scenario['method']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    // Set headers
    if (!empty($scenario['headers'])) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $scenario['headers']);
    }
    
    // Set data
    if ($scenario['data'] !== null) {
        if (is_array($scenario['data'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($scenario['data']));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $scenario['data']);
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'status_code' => $httpCode,
        'response' => $response,
        'data' => json_decode($response, true),
        'error' => $error,
        'url' => $url,
        'method' => $scenario['method']
    ];
}

/**
 * Display test result
 */
function displayResult($result, $scenario)
{
    $status = $result['status_code'] === $scenario['expected_status'] ? '✅ PASS' : '❌ FAIL';
    
    echo "Status: {$status}\n";
    echo "HTTP Code: {$result['status_code']} (Expected: {$scenario['expected_status']})\n";
    
    if ($result['error']) {
        echo "cURL Error: {$result['error']}\n";
    }
    
    if ($result['data']) {
        echo "Response:\n";
        echo json_encode($result['data'], JSON_PRETTY_PRINT) . "\n";
    } elseif ($result['response']) {
        echo "Response: {$result['response']}\n";
    }
    
    // Additional info for specific scenarios
    if (strpos($scenario['name'], 'Money Collection') !== false && $result['status_code'] === 200) {
        if (isset($result['data']['transaction_id'])) {
            echo "🎯 Transaction ID: {$result['data']['transaction_id']}\n";
        }
        if (isset($result['data']['reference'])) {
            echo "📝 Reference: {$result['data']['reference']}\n";
        }
    }
}

/**
 * Rate limit test (run multiple requests quickly)
 */
function runRateLimitTest($config)
{
    echo "\n🔥 Rate Limit Test\n";
    echo "==================\n";
    
    $successCount = 0;
    $rateLimitCount = 0;
    
    for ($i = 1; $i <= 60; $i++) {
        $data = [
            'customer_phone' => '255778342299',
            'mobile_network' => 'TZ-AIRTEL-C2B',
            'amount' => 100,
            'description' => "Rate limit test #{$i}",
            'reference' => "RATE_TEST_{$i}_" . time(),
            'date' => date('Y-m-d H:i:s'),
            'webhook_url' => 'https://webhook.site/rate-test'
        ];
        
        $result = makeRequest('POST', $config['base_url'] . '/' . $config['service_code'], $data, [
            'X-API-Key: ' . $config['api_key'],
            'X-API-Secret: ' . $config['api_secret'],
            'Content-Type: application/json'
        ]);
        
        if ($result['status_code'] === 200) {
            $successCount++;
        } elseif ($result['status_code'] === 429) {
            $rateLimitCount++;
            echo "Rate limit hit at request #{$i}\n";
            break;
        }
        
        // Small delay to avoid overwhelming the server
        usleep(100000); // 0.1 seconds
    }
    
    echo "Rate limit test completed: {$successCount} successful, {$rateLimitCount} rate limited\n";
}

// Uncomment the line below to run rate limit test
// runRateLimitTest($config); 