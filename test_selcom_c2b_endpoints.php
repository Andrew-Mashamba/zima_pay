#!/usr/bin/env php
<?php
/**
 * Test Selcom C2B endpoints: lookup, validation, notification
 *
 * Usage: php test_selcom_c2b_endpoints.php [base_url]
 * Example: php test_selcom_c2b_endpoints.php http://127.0.0.1:8001
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$baseUrl = rtrim($argv[1] ?? 'http://127.0.0.1:8001', '/');
$c2bBase = $baseUrl . '/api/selcom/c2b';

$bearerToken = env('SELCOM_C2B_BEARER_TOKEN', '');
$headers = [
    'Content-Type: application/json',
    'Accept: application/json',
];
if ($bearerToken) {
    $headers[] = 'Authorization: Bearer ' . $bearerToken;
}

// Create a test transaction so lookup/validation/notification can find it
$utilityref = 'C2B_TEST_' . time();
$transid = 'TXN-' . time() . '-' . substr(md5(uniqid()), 0, 6);
$reference = 'REF-' . time();
$amount = 50000;
$msisdn = '255742099713';

$transaction = \App\Models\Transaction::create([
    'transaction_id' => $transid,
    'client_reference' => $utilityref,
    'client_id' => 3, // Sample Payment Gateway
    'aggregator_id' => 1,
    'service_id' => 1,
    'service_mapping_id' => 5,
    'amount' => $amount,
    'currency' => 'TZS',
    'customer_phone' => $msisdn,
    'customer_name' => 'Simon Mpembee',
    'status' => 'pending',
    'aggregator_status' => 'pending',
]);

echo "📋 Selcom C2B Endpoint Tests\n";
echo "=============================\n";
echo "Base URL: {$baseUrl}\n";
echo "Test utilityref: {$utilityref}\n";
echo "Test transid: {$transid}\n";
echo "Test amount: {$amount} TZS\n\n";

function doPost(string $url, array $headers, array $data): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $httpCode, 'body' => json_decode($response, true) ?: $response];
}

// --- TEST 1: Lookup ---
echo "1️⃣  LOOKUP\n";
echo "   POST {$c2bBase}/lookup\n";
$lookupData = [
    'utilityref' => $utilityref,
    'msisdn' => $msisdn,
    'transid' => $transid,
    'reference' => $reference,
];
$lookup = doPost($c2bBase . '/lookup', $headers, $lookupData);
echo "   HTTP {$lookup['code']}\n";
echo "   Response: " . json_encode($lookup['body'], JSON_PRETTY_PRINT) . "\n";
$lookupOk = $lookup['code'] === 200 && ($lookup['body']['resultcode'] ?? '') === '000';
echo $lookupOk ? "   ✅ PASS\n\n" : "   ❌ FAIL\n\n";

// --- TEST 2: Validation ---
echo "2️⃣  VALIDATION\n";
echo "   POST {$c2bBase}/validation\n";
$validationData = array_merge($lookupData, ['amount' => $amount]);
$validation = doPost($c2bBase . '/validation', $headers, $validationData);
echo "   HTTP {$validation['code']}\n";
echo "   Response: " . json_encode($validation['body'], JSON_PRETTY_PRINT) . "\n";
$validationOk = $validation['code'] === 200 && ($validation['body']['resultcode'] ?? '') === '000';
echo $validationOk ? "   ✅ PASS\n\n" : "   ❌ FAIL\n\n";

// --- TEST 3: Notification ---
echo "3️⃣  NOTIFICATION\n";
echo "   POST {$c2bBase}/notification\n";
$notificationData = array_merge($validationData, ['operator' => 'AIRTELMONEY', 'resultcode' => '000']);
$notification = doPost($c2bBase . '/notification', $headers, $notificationData);
echo "   HTTP {$notification['code']}\n";
echo "   Response: " . json_encode($notification['body'], JSON_PRETTY_PRINT) . "\n";
$notificationOk = $notification['code'] === 200 && ($notification['body']['resultcode'] ?? '') === '000';
echo $notificationOk ? "   ✅ PASS\n\n" : "   ❌ FAIL\n\n";

// --- Summary ---
echo "=============================\n";
$allOk = $lookupOk && $validationOk && $notificationOk;
echo $allOk ? "✅ All C2B endpoint tests PASSED\n" : "❌ Some tests FAILED\n";
exit($allOk ? 0 : 1);
