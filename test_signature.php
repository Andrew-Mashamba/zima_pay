<?php

echo "🔍 Signature Verification Test\n";
echo "==============================\n\n";

// Test data
$apiSecret = 'sample_client_secret_XYZ789GHI012';
$method = 'POST';
$uri = '/api/payment-links/generate-universal';
$contentType = 'application/json';
$timestamp = '1753015489';
$nonce = '687ce4c1c93b3';

$requestData = [
    'description' => 'Loan Repayment - Installment #3 of 12',
    'target' => 'individual',
    'customer_reference' => 'LOAN_2025_001',
    'customer_name' => 'Sarah Johnson',
    'customer_phone' => '255723456789',
    'customer_email' => 'sarah@email.com',
    'expires_at' => '2025-07-27T10:00:00Z',
    'items' => [
        [
            'type' => 'service',
            'product_service_reference' => 'LOAN_INST_003',
            'product_service_name' => 'Loan Installment',
            'amount' => 75000,
            'is_required' => true,
            'allow_partial' => false
        ]
    ]
];

$body = json_encode($requestData);
$bodyHash = hash('sha256', $body);

echo "📋 Test Parameters:\n";
echo "Method: {$method}\n";
echo "URI: {$uri}\n";
echo "Content-Type: {$contentType}\n";
echo "Timestamp: {$timestamp}\n";
echo "Nonce: {$nonce}\n";
echo "Body Hash: {$bodyHash}\n\n";

// Create canonical string (matching middleware format)
$canonicalString = implode("\n", [
    $method,
    $uri,
    $contentType,
    $timestamp,
    $nonce,
    $bodyHash
]);

echo "🔗 Canonical String:\n";
echo "===================\n";
echo $canonicalString . "\n\n";

// Generate HMAC signature
$signature = hash_hmac('sha256', $canonicalString, $apiSecret);

echo "🔐 Generated Signature:\n";
echo "======================\n";
echo $signature . "\n\n";

echo "🔍 Expected Signature (from previous request):\n";
echo "=============================================\n";
echo "0322e1dabb703ac7dc1b88bce6343cac9ad1ebd8574af32cdbe02c1c6d0c5320\n\n";

echo "✅ Match: " . ($signature === '0322e1dabb703ac7dc1b88bce6343cac9ad1ebd8574af32cdbe02c1c6d0c5320' ? 'YES' : 'NO') . "\n"; 