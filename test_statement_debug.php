<?php

/**
 * Debug Script: Check Raw Statement Response
 * 
 * This script checks the raw statement response from Tembo API
 * to understand the data structure and fix the mapping.
 */

// Tembo API Configuration
$temboConfig = [
    'base_url' => 'https://sandbox.temboplus.com/tembo/v1',
    'account_id' => 'bf71ba501b37d989db6224fd',
    'secret_key' => 'vd//lqSw67Nl08e7Y2YzWcs2EL+rAIImpl4U4uNHdQg='
];

echo "🔍 Debug: Raw Statement Response from Tembo\n";
echo "==========================================\n\n";

// Test direct statement call
echo "1. Direct Tembo Statement Call...\n";
$statementData = [
    'startDate' => date('Y-m-d', strtotime('-7 days')), // Last 7 days
    'endDate' => date('Y-m-d', strtotime('+1 day'))     // Include today
];

$directResponse = makeDirectTemboRequest(
    $temboConfig['base_url'] . '/wallet/collection-statement',
    'POST',
    $statementData,
    $temboConfig
);

echo "Status Code: " . $directResponse['status_code'] . "\n";
echo "Raw Response:\n";
echo json_encode($directResponse['data'], JSON_PRETTY_PRINT) . "\n\n";

// Test ESB statement call
echo "2. ESB Statement Call...\n";
$esbData = [
    'start_date' => date('Y-m-d', strtotime('-7 days')),
    'end_date' => date('Y-m-d', strtotime('+1 day'))
];

$esbResponse = makeEsbRequest(
    'http://127.0.0.1:8000/api/esb/COLLECTION_STATEMENT',
    'POST',
    $esbData,
    [
        'X-API-Key: test_bank_key_8CGbIjJCMRWLjFll',
        'X-API-Secret: test_bank_secret_GtlfgLnQYUpcTwOry9LV1xvRcCVFO1Ci',
        'Content-Type: application/json'
    ]
);

echo "Status Code: " . $esbResponse['status_code'] . "\n";
echo "ESB Response:\n";
echo json_encode($esbResponse['data'], JSON_PRETTY_PRINT) . "\n\n";

echo "✅ Debug completed!\n";

/**
 * Make direct request to Tembo API
 */
function makeDirectTemboRequest($url, $method, $data, $config)
{
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'User-Agent: ZIMA-ESB-Debug/1.0',
        'x-account-id: ' . $config['account_id'],
        'x-secret-key: ' . $config['secret_key'],
        'x-request-id: ' . uniqid()
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'status_code' => 0,
            'data' => ['error' => 'cURL Error: ' . $error]
        ];
    }
    
    $responseData = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $responseData = $response;
    }
    
    return [
        'status_code' => $httpCode,
        'data' => $responseData,
        'raw_response' => $response
    ];
}

/**
 * Make ESB request
 */
function makeEsbRequest($url, $method, $data, $headers)
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