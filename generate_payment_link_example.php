<?php

require_once 'vendor/autoload.php';

echo "🔗 Universal Payment Link Generation Request Examples\n";
echo "====================================================\n\n";

// Client configuration with secure credentials
$clientConfig = [
    'name' => 'Sample Payment Gateway',
    'api_key' => 'sample_client_key_ABC123DEF456',
    'api_secret' => 'sample_client_secret_XYZ789GHI012',
    'base_url' => 'http://127.0.0.1:8000/api'
];

echo "📋 Client Configuration:\n";
echo "   Name: {$clientConfig['name']}\n";
echo "   API Key: {$clientConfig['api_key']}\n";
echo "   Base URL: {$clientConfig['base_url']}\n\n";

// Function to generate HMAC signature
function generateHmacSignature($data, $secret) {
    $canonicalData = json_encode($data, JSON_UNESCAPED_SLASHES);
    return hash_hmac('sha256', $canonicalData, $secret);
}

// Function to make authenticated request
function makeAuthenticatedRequest($url, $data, $apiKey, $apiSecret) {
    $timestamp = time();
    $nonce = uniqid();
    
    // Create signature payload
    $signaturePayload = [
        'method' => 'POST',
        'url' => $url,
        'timestamp' => $timestamp,
        'nonce' => $nonce,
        'data' => $data
    ];
    
    $signature = generateHmacSignature($signaturePayload, $apiSecret);
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-API-Key: ' . $apiKey,
        'X-Timestamp: ' . $timestamp,
        'X-Nonce: ' . $nonce,
        'X-Signature: ' . $signature
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status_code' => $httpCode,
        'response' => json_decode($response, true),
        'raw_response' => $response
    ];
}

echo "🔐 Example 1: Individual Payment Link (Pre-filled Customer Data)\n";
echo "================================================================\n";

$individualLinkData = [
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

echo "📤 Request Data:\n";
echo json_encode($individualLinkData, JSON_PRETTY_PRINT);
echo "\n\n";

echo "🔗 Request URL: {$clientConfig['base_url']}/payment-links/generate-universal\n";
echo "🔐 Authentication Headers:\n";
echo "   X-API-Key: {$clientConfig['api_key']}\n";
echo "   X-Timestamp: [current_timestamp]\n";
echo "   X-Nonce: [unique_nonce]\n";
echo "   X-Signature: [hmac_sha256_signature]\n\n";

// Make the request
$url = $clientConfig['base_url'] . '/payment-links/generate-universal';
$result = makeAuthenticatedRequest($url, $individualLinkData, $clientConfig['api_key'], $clientConfig['api_secret']);

echo "📥 Response (HTTP {$result['status_code']}):\n";
if ($result['response']) {
    echo json_encode($result['response'], JSON_PRETTY_PRINT);
} else {
    echo $result['raw_response'];
}
echo "\n\n";

echo "🔐 Example 2: Public Payment Link (No Pre-filled Customer Data)\n";
echo "===============================================================\n";

$publicLinkData = [
    'description' => 'Sunday Service Donation - St. Mary\'s Church',
    'target' => 'public',
    'customer_reference' => 'CHURCH_SADAKA_001',
    'expires_at' => '2025-08-19T10:00:00Z',
    'items' => [
        [
            'type' => 'service',
            'product_service_reference' => 'DONATION_GENERAL',
            'product_service_name' => 'General Donation',
            'amount' => 10000,
            'is_required' => false,
            'allow_partial' => true,
            'minimum_amount' => 5000
        ],
        [
            'type' => 'service',
            'product_service_reference' => 'DONATION_BUILDING',
            'product_service_name' => 'Building Fund',
            'amount' => 5000,
            'is_required' => false,
            'allow_partial' => true,
            'minimum_amount' => 2000
        ]
    ]
];

echo "📤 Request Data:\n";
echo json_encode($publicLinkData, JSON_PRETTY_PRINT);
echo "\n\n";

echo "🔐 Example 3: Multi-Item Business Invoice\n";
echo "=========================================\n";

$multiItemLinkData = [
    'description' => 'Business Services Invoice #2025-001',
    'target' => 'individual',
    'customer_reference' => 'INVOICE_2025_001',
    'customer_name' => 'Business Corp Ltd',
    'customer_phone' => '255789123456',
    'customer_email' => 'accounts@businesscorp.com',
    'expires_at' => '2025-08-03T10:00:00Z',
    'items' => [
        [
            'type' => 'service',
            'product_service_reference' => 'CONSULT_001',
            'product_service_name' => 'Business Strategy Consultation',
            'amount' => 150000,
            'is_required' => true,
            'allow_partial' => true,
            'minimum_amount' => 30000
        ],
        [
            'type' => 'product',
            'product_service_reference' => 'REPORT_001',
            'product_service_name' => 'Market Research Report',
            'amount' => 100000,
            'is_required' => true,
            'allow_partial' => true,
            'minimum_amount' => 20000
        ]
    ]
];

echo "📤 Request Data:\n";
echo json_encode($multiItemLinkData, JSON_PRETTY_PRINT);
echo "\n\n";

echo "🔐 Example 4: cURL Command for Individual Payment Link\n";
echo "======================================================\n";

$timestamp = time();
$nonce = uniqid();
$signaturePayload = [
    'method' => 'POST',
    'url' => $clientConfig['base_url'] . '/payment-links/generate-universal',
    'timestamp' => $timestamp,
    'nonce' => $nonce,
    'data' => $individualLinkData
];
$signature = generateHmacSignature($signaturePayload, $clientConfig['api_secret']);

echo "curl -X POST '{$clientConfig['base_url']}/payment-links/generate-universal' \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -H 'Accept: application/json' \\\n";
echo "  -H 'X-API-Key: {$clientConfig['api_key']}' \\\n";
echo "  -H 'X-Timestamp: {$timestamp}' \\\n";
echo "  -H 'X-Nonce: {$nonce}' \\\n";
echo "  -H 'X-Signature: {$signature}' \\\n";
echo "  -d '" . json_encode($individualLinkData) . "'\n\n";

echo "🔐 Example 5: JavaScript/Node.js Request\n";
echo "========================================\n";

echo "const crypto = require('crypto');\n";
echo "const axios = require('axios');\n\n";

echo "const clientConfig = {\n";
echo "  apiKey: '{$clientConfig['api_key']}',\n";
echo "  apiSecret: '{$clientConfig['api_secret']}',\n";
echo "  baseUrl: '{$clientConfig['base_url']}'\n";
echo "};\n\n";

echo "function generateHmacSignature(data, secret) {\n";
echo "  const canonicalData = JSON.stringify(data);\n";
echo "  return crypto.createHmac('sha256', secret).update(canonicalData).digest('hex');\n";
echo "}\n\n";

echo "async function generateUniversalPaymentLink(linkData) {\n";
echo "  const timestamp = Math.floor(Date.now() / 1000);\n";
echo "  const nonce = Math.random().toString(36).substring(2);\n";
echo "  \n";
echo "  const signaturePayload = {\n";
echo "    method: 'POST',\n";
echo "    url: clientConfig.baseUrl + '/payment-links/generate-universal',\n";
echo "    timestamp: timestamp,\n";
echo "    nonce: nonce,\n";
echo "    data: linkData\n";
echo "  };\n";
echo "  \n";
echo "  const signature = generateHmacSignature(signaturePayload, clientConfig.apiSecret);\n";
echo "  \n";
echo "  const headers = {\n";
echo "    'Content-Type': 'application/json',\n";
echo "    'Accept': 'application/json',\n";
echo "    'X-API-Key': clientConfig.apiKey,\n";
echo "    'X-Timestamp': timestamp,\n";
echo "    'X-Nonce': nonce,\n";
echo "    'X-Signature': signature\n";
echo "  };\n";
echo "  \n";
echo "  try {\n";
echo "    const response = await axios.post(\n";
echo "      clientConfig.baseUrl + '/payment-links/generate-universal',\n";
echo "      linkData,\n";
echo "      { headers: headers }\n";
echo "    );\n";
echo "    return response.data;\n";
echo "  } catch (error) {\n";
echo "    console.error('Error generating payment link:', error.response?.data || error.message);\n";
echo "    throw error;\n";
echo "  }\n";
echo "}\n\n";

echo "// Usage example\n";
echo "const linkData = " . json_encode($individualLinkData, JSON_PRETTY_PRINT) . ";\n";
echo "generateUniversalPaymentLink(linkData).then(result => {\n";
echo "  console.log('Payment link generated:', result);\n";
echo "}).catch(error => {\n";
echo "  console.error('Failed to generate payment link:', error);\n";
echo "});\n\n";

echo "🔐 Example 6: Python Request\n";
echo "============================\n";

echo "import requests\n";
echo "import hashlib\n";
echo "import hmac\n";
echo "import json\n";
echo "import time\n";
echo "import uuid\n\n";

echo "client_config = {\n";
echo "    'api_key': '{$clientConfig['api_key']}',\n";
echo "    'api_secret': '{$clientConfig['api_secret']}',\n";
echo "    'base_url': '{$clientConfig['base_url']}'\n";
echo "}\n\n";

echo "def generate_hmac_signature(data, secret):\n";
echo "    canonical_data = json.dumps(data, separators=(',', ':'))\n";
echo "    return hmac.new(\n";
echo "        secret.encode('utf-8'),\n";
echo "        canonical_data.encode('utf-8'),\n";
echo "        hashlib.sha256\n";
echo "    ).hexdigest()\n\n";

echo "def generate_universal_payment_link(link_data):\n";
echo "    timestamp = int(time.time())\n";
echo "    nonce = str(uuid.uuid4())\n";
echo "    \n";
echo "    signature_payload = {\n";
echo "        'method': 'POST',\n";
echo "        'url': client_config['base_url'] + '/payment-links/generate-universal',\n";
echo "        'timestamp': timestamp,\n";
echo "        'nonce': nonce,\n";
echo "        'data': link_data\n";
echo "    }\n";
echo "    \n";
echo "    signature = generate_hmac_signature(signature_payload, client_config['api_secret'])\n";
echo "    \n";
echo "    headers = {\n";
echo "        'Content-Type': 'application/json',\n";
echo "        'Accept': 'application/json',\n";
echo "        'X-API-Key': client_config['api_key'],\n";
echo "        'X-Timestamp': str(timestamp),\n";
echo "        'X-Nonce': nonce,\n";
echo "        'X-Signature': signature\n";
echo "    }\n";
echo "    \n";
echo "    try:\n";
echo "        response = requests.post(\n";
echo "            client_config['base_url'] + '/payment-links/generate-universal',\n";
echo "            json=link_data,\n";
echo "            headers=headers\n";
echo "        )\n";
echo "        response.raise_for_status()\n";
echo "        return response.json()\n";
echo "    except requests.exceptions.RequestException as e:\n";
echo "        print(f'Error generating payment link: {e}')\n";
echo "        raise\n\n";

echo "# Usage example\n";
echo "link_data = " . json_encode($individualLinkData, JSON_PRETTY_PRINT) . "\n";
echo "try:\n";
echo "    result = generate_universal_payment_link(link_data)\n";
echo "    print('Payment link generated:', result)\n";
echo "except Exception as e:\n";
echo "    print('Failed to generate payment link:', e)\n\n";

echo "🔐 Security Features in Request:\n";
echo "===============================\n";
echo "✅ HMAC-SHA256 signature authentication\n";
echo "✅ Timestamp validation (prevents replay attacks)\n";
echo "✅ Nonce uniqueness (prevents duplicate requests)\n";
echo "✅ API key validation\n";
echo "✅ Request data integrity verification\n";
echo "✅ Rate limiting protection\n";
echo "✅ IP blocking for malicious sources\n";
echo "✅ Threat detection monitoring\n\n";

echo "📋 Expected Response Format:\n";
echo "============================\n";
echo "{\n";
echo "  \"status\": \"success\",\n";
echo "  \"message\": \"Universal payment link generated successfully\",\n";
echo "  \"data\": {\n";
echo "    \"link_id\": \"LINK_ABC123DEF456\",\n";
echo "    \"short_code\": \"AbC123Xy\",\n";
echo "    \"payment_url\": \"http://127.0.0.1:8000/pay/AbC123Xy\",\n";
echo "    \"qr_code_data\": \"http://127.0.0.1:8000/pay/AbC123Xy\",\n";
echo "    \"target_type\": \"individual\",\n";
echo "    \"is_public\": false,\n";
echo "    \"description\": \"Loan Repayment - Installment #3 of 12\",\n";
echo "    \"total_amount\": 75000,\n";
echo "    \"currency\": \"TZS\",\n";
echo "    \"customer_reference\": \"LOAN_2025_001\",\n";
echo "    \"customer_name\": \"Sarah Johnson\",\n";
echo "    \"customer_phone\": \"255723456789\",\n";
echo "    \"customer_email\": \"sarah@email.com\",\n";
echo "    \"items\": [\n";
echo "      {\n";
echo "        \"item_code\": \"ITEM_001\",\n";
echo "        \"type\": \"service\",\n";
echo "        \"product_service_reference\": \"LOAN_INST_003\",\n";
echo "        \"product_service_name\": \"Loan Installment\",\n";
echo "        \"amount\": 75000,\n";
echo "        \"paid_amount\": 0,\n";
echo "        \"remaining_amount\": 75000,\n";
echo "        \"payment_percentage\": 0,\n";
echo "        \"status\": \"pending\",\n";
echo "        \"is_required\": true,\n";
echo "        \"allow_partial\": false\n";
echo "      }\n";
echo "    ],\n";
echo "    \"expires_at\": \"2025-07-27T10:00:00.000000Z\",\n";
echo "    \"max_uses\": 1,\n";
echo "    \"current_uses\": 0,\n";
echo "    \"is_reusable\": false,\n";
echo "    \"allowed_networks\": [\"TZ-MPESA-C2B\", \"TZ-AIRTEL-C2B\", \"TZ-TIGO-C2B\", \"TZ-HALOPESA-C2B\"],\n";
echo "    \"created_at\": \"2025-07-20T12:09:44.000000Z\"\n";
echo "  },\n";
echo "  \"timestamp\": \"2025-07-20T12:09:44.342794Z\",\n";
echo "  \"request_id\": \"req_687cdc8853b40\"\n";
echo "}\n\n";

echo "🚀 Ready to generate universal payment links!\n";
echo "   All requests are protected with military-grade security.\n"; 