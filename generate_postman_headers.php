<?php

echo "🔗 Postman Header Generator for Universal Payment Link API\n";
echo "==========================================================\n\n";

// Configuration
$apiKey = 'sample_client_key_ABC123DEF456';
$apiSecret = 'sample_client_secret_XYZ789GHI012';
$baseUrl = 'http://127.0.0.1:8000/api/payment-links/generate-universal';

// Sample request data
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

// Generate authentication values
$timestamp = time();
$nonce = uniqid();

// Create canonical string (matching middleware format)
$method = 'POST';
$uri = '/api/payment-links/generate-universal';
$contentType = 'application/json';
$body = json_encode($requestData);
$bodyHash = hash('sha256', $body);

$canonicalString = implode("\n", [
    $method,
    $uri,
    $contentType,
    $timestamp,
    $nonce,
    $bodyHash
]);

// Generate HMAC signature
$signature = hash_hmac('sha256', $canonicalString, $apiSecret);

echo "📋 Request URL:\n";
echo "POST {$baseUrl}\n\n";

echo "🔐 Headers (Copy these to Postman):\n";
echo "====================================\n";
echo "Content-Type: application/json\n";
echo "Accept: application/json\n";
echo "X-API-Key: {$apiKey}\n";
echo "X-Timestamp: {$timestamp}\n";
echo "X-Nonce: {$nonce}\n";
echo "X-Signature: {$signature}\n\n";

echo "📤 Request Body (JSON):\n";
echo "=======================\n";
echo json_encode($requestData, JSON_PRETTY_PRINT);
echo "\n\n";

echo "🔗 cURL Command:\n";
echo "================\n";
echo "curl -X POST '{$baseUrl}' \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -H 'Accept: application/json' \\\n";
echo "  -H 'X-API-Key: {$apiKey}' \\\n";
echo "  -H 'X-Timestamp: {$timestamp}' \\\n";
echo "  -H 'X-Nonce: {$nonce}' \\\n";
echo "  -H 'X-Signature: {$signature}' \\\n";
echo "  -d '" . json_encode($requestData) . "'\n\n";

echo "🔍 Signature Details:\n";
echo "=====================\n";
echo "Method: {$method}\n";
echo "URI: {$uri}\n";
echo "Content-Type: {$contentType}\n";
echo "Timestamp: {$timestamp}\n";
echo "Nonce: {$nonce}\n";
echo "Body Hash: {$bodyHash}\n";
echo "Canonical String:\n{$canonicalString}\n";
echo "Signature: {$signature}\n\n";

echo "📝 Postman Pre-request Script (Optional):\n";
echo "==========================================\n";
echo "// Add this to Postman Pre-request Script tab to auto-generate headers\n";
echo "const crypto = require('crypto');\n\n";
echo "function generateHmacSignature(data, secret) {\n";
echo "  return crypto.createHmac('sha256', secret).update(data).digest('hex');\n";
echo "}\n\n";
echo "const apiKey = '{$apiKey}';\n";
echo "const apiSecret = '{$apiSecret}';\n";
echo "const timestamp = Math.floor(Date.now() / 1000);\n";
echo "const nonce = Math.random().toString(36).substring(2);\n";
echo "const requestData = " . json_encode($requestData) . ";\n\n";
echo "const method = 'POST';\n";
echo "const uri = '/api/payment-links/generate-universal';\n";
echo "const contentType = 'application/json';\n";
echo "const body = JSON.stringify(requestData);\n";
echo "const bodyHash = crypto.createHash('sha256').update(body).digest('hex');\n\n";
echo "const canonicalString = [method, uri, contentType, timestamp, nonce, bodyHash].join('\\n');\n";
echo "const signature = generateHmacSignature(canonicalString, apiSecret);\n\n";
echo "pm.environment.set('timestamp', timestamp);\n";
echo "pm.environment.set('nonce', nonce);\n";
echo "pm.environment.set('signature', signature);\n";
echo "pm.environment.set('api_key', apiKey);\n\n";

echo "✅ Ready to test in Postman!\n";
echo "   Copy the headers and body above to your Postman request.\n";
echo "   The signature is valid for this specific request data.\n"; 