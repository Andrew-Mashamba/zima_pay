#!/usr/bin/env php
<?php
/**
 * Test script for POST /api/payment-links/generate-universal
 *
 * Usage: php test_generate_universal_payment_link.php [base_url]
 * Example: php test_generate_universal_payment_link.php http://127.0.0.1:8000
 */

$baseUrl = $argv[1] ?? 'http://127.0.0.1:8000';
$apiUrl = rtrim($baseUrl, '/') . '/api/payment-links/generate-universal';

$headers = [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-API-Key: sample_client_key_ABC123DEF456',
    'X-API-Secret: sample_client_secret_XYZ789GHI012',
];

$payload = [
    'description' => 'Saccos services',
    'target' => 'individual',
    'reference' => 'MEMBER2001',
    'customer_reference' => 'MEMBER2001',
    'customer_name' => 'Simon Mpembee',
    'customer_phone' => '255742099713',
    'customer_email' => 'mpembeesimon@email.com',
    'currency' => 'TZS',
    'expires_at' => '2026-12-31T10:00:00.000000Z',
    'max_uses' => null,
    'is_reusable' => false,
    'allow_partial_payment' => true,
    'allowed_networks' => [
        'TZ-AIRTEL-C2B',
        'TZ-TIGO-C2B',
        'TZ-MPESA-C2B',
        'TZ-HALOPESA-C2B',
    ],
    'items' => [
        [
            'type' => 'service',
            'product_service_reference' => 'SHARES_01',
            'product_service_name' => 'MANDATORY SHARES',
            'description' => null,
            'amount' => 200000,
            'is_required' => true,
            'allow_partial' => false,
            'minimum_amount' => null,
        ],
        [
            'type' => 'service',
            'product_service_reference' => 'DEPOSITS_07',
            'product_service_name' => 'DEPOSITS',
            'description' => null,
            'amount' => 500000,
            'is_required' => true,
            'allow_partial' => true,
            'minimum_amount' => null,
        ],
    ],
];

echo "🔗 POST {$apiUrl}\n";
echo "📋 Headers:\n";
foreach ($headers as $h) {
    echo "   {$h}\n";
}
echo "\n📦 Request body:\n";
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ cURL error: {$error}\n";
    exit(1);
}

echo "📡 HTTP Status: {$httpCode}\n\n";
echo "📥 Response:\n";

$decoded = json_decode($response, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

    if (isset($decoded['status']) && $decoded['status'] === 'success') {
        echo "\n✅ Success! Payment URL: " . ($decoded['data']['payment_url'] ?? 'N/A') . "\n";
    } elseif (isset($decoded['status']) && $decoded['status'] === 'error') {
        echo "\n❌ Error: " . ($decoded['message'] ?? 'Unknown') . "\n";
        exit(1);
    }
} else {
    echo $response . "\n";
}
