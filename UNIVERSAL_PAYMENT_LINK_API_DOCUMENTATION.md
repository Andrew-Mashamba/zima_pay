# 🔗 Universal Payment Link API Documentation

## 📋 Overview

The Universal Payment Link API provides a secure, flexible way to generate payment links for various use cases including individual payments, public donations, business invoices, and more. All requests are protected with military-grade security features.

**Base URL:** `http://127.0.0.1:8000/api`

---

## 🔐 Authentication

All API requests require HMAC-SHA256 signature authentication with the following headers:

### Required Headers
```
Content-Type: application/json
Accept: application/json
X-API-Key: [your_api_key]
X-Timestamp: [current_unix_timestamp]
X-Nonce: [unique_nonce]
X-Signature: [hmac_sha256_signature]
```

### Signature Generation
```php
function generateHmacSignature($data, $secret) {
    $canonicalData = json_encode($data, JSON_UNESCAPED_SLASHES);
    return hash_hmac('sha256', $canonicalData, $secret);
}

$signaturePayload = [
    'method' => 'POST',
    'url' => $url,
    'timestamp' => $timestamp,
    'nonce' => $nonce,
    'data' => $requestData
];
$signature = generateHmacSignature($signaturePayload, $apiSecret);
```

---

## 🚀 API Endpoints

### 1. Generate Universal Payment Link

**Endpoint:** `POST /payment-links/generate-universal`

**Description:** Creates a new universal payment link for individual or public payments.

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `description` | string | ✅ | Payment description (max 255 chars) |
| `target` | string | ✅ | `"individual"` or `"public"` |
| `customer_reference` | string | ❌ | Client's internal reference |
| `customer_name` | string | ✅* | Customer name (required for individual) |
| `customer_phone` | string | ✅* | Customer phone (255XXXXXXXXX format, required for individual) |
| `customer_email` | string | ❌ | Customer email address |
| `expires_at` | string | ❌ | Expiration date (ISO 8601 format) |
| `max_uses` | integer | ❌ | Maximum number of uses (1-1000) |
| `webhook_url` | string | ❌ | Webhook URL for payment notifications |
| `success_url` | string | ❌ | Redirect URL on successful payment |
| `failure_url` | string | ❌ | Redirect URL on failed payment |
| `cancel_url` | string | ❌ | Redirect URL on cancelled payment |
| `items` | array | ✅ | Array of payment items |

#### Items Array Structure

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `type` | string | ✅ | `"service"` or `"product"` |
| `product_service_reference` | string | ❌ | Internal reference for the item |
| `product_service_name` | string | ✅ | Display name for the item |
| `amount` | numeric | ✅ | Item amount (100-1,000,000 TZS) |
| `description` | string | ❌ | Item description |
| `is_required` | boolean | ❌ | Whether item is required (default: true) |
| `allow_partial` | boolean | ❌ | Allow partial payment (default: false) |
| `minimum_amount` | numeric | ❌ | Minimum payment amount |
| `quantity` | integer | ❌ | Item quantity (default: 1) |
| `subcategory` | string | ❌ | Item subcategory |

#### Example Requests

**Individual Payment Link:**
```json
{
  "description": "Loan Repayment - Installment #3 of 12",
  "target": "individual",
  "customer_reference": "LOAN_2025_001",
  "customer_name": "Sarah Johnson",
  "customer_phone": "255723456789",
  "customer_email": "sarah@email.com",
  "expires_at": "2025-07-27T10:00:00Z",
  "items": [
    {
      "type": "service",
      "product_service_reference": "LOAN_INST_003",
      "product_service_name": "Loan Installment",
      "amount": 75000,
      "is_required": true,
      "allow_partial": false
    }
  ]
}
```

**Public Payment Link:**
```json
{
  "description": "Sunday Service Donation - St. Mary's Church",
  "target": "public",
  "customer_reference": "CHURCH_SADAKA_001",
  "expires_at": "2025-08-19T10:00:00Z",
  "items": [
    {
      "type": "service",
      "product_service_reference": "DONATION_GENERAL",
      "product_service_name": "General Donation",
      "amount": 10000,
      "is_required": false,
      "allow_partial": true,
      "minimum_amount": 5000
    },
    {
      "type": "service",
      "product_service_reference": "DONATION_BUILDING",
      "product_service_name": "Building Fund",
      "amount": 5000,
      "is_required": false,
      "allow_partial": true,
      "minimum_amount": 2000
    }
  ]
}
```

**Multi-Item Business Invoice:**
```json
{
  "description": "Business Services Invoice #2025-001",
  "target": "individual",
  "customer_reference": "INVOICE_2025_001",
  "customer_name": "Business Corp Ltd",
  "customer_phone": "255789123456",
  "customer_email": "accounts@businesscorp.com",
  "expires_at": "2025-08-03T10:00:00Z",
  "items": [
    {
      "type": "service",
      "product_service_reference": "CONSULT_001",
      "product_service_name": "Business Strategy Consultation",
      "amount": 150000,
      "is_required": true,
      "allow_partial": true,
      "minimum_amount": 30000
    },
    {
      "type": "product",
      "product_service_reference": "REPORT_001",
      "product_service_name": "Market Research Report",
      "amount": 100000,
      "is_required": true,
      "allow_partial": true,
      "minimum_amount": 20000
    }
  ]
}
```

#### Success Response (201 Created)
```json
{
  "status": "success",
  "message": "Universal payment link generated successfully",
  "data": {
    "link_id": "LINK_ABC123DEF456",
    "short_code": "AbC123Xy",
    "payment_url": "http://127.0.0.1:8000/pay/AbC123Xy",
    "qr_code_data": "http://127.0.0.1:8000/pay/AbC123Xy",
    "target_type": "individual",
    "is_public": false,
    "description": "Loan Repayment - Installment #3 of 12",
    "total_amount": 75000,
    "currency": "TZS",
    "customer_reference": "LOAN_2025_001",
    "customer_name": "Sarah Johnson",
    "customer_phone": "255723456789",
    "customer_email": "sarah@email.com",
    "items": [
      {
        "item_code": "ITEM_001",
        "type": "service",
        "product_service_reference": "LOAN_INST_003",
        "product_service_name": "Loan Installment",
        "description": null,
        "amount": 75000,
        "paid_amount": 0,
        "remaining_amount": 75000,
        "payment_percentage": 0,
        "status": "pending",
        "is_required": true,
        "allow_partial": false,
        "minimum_amount": null,
        "quantity": 1,
        "subcategory": null
      }
    ],
    "expires_at": "2025-07-27T10:00:00.000000Z",
    "max_uses": 1,
    "current_uses": 0,
    "is_reusable": false,
    "allowed_networks": ["TZ-MPESA-C2B", "TZ-AIRTEL-C2B", "TZ-TIGO-C2B", "TZ-HALOPESA-C2B"],
    "webhook_url": null,
    "success_url": null,
    "failure_url": null,
    "cancel_url": null,
    "created_at": "2025-07-20T12:09:44.000000Z"
  },
  "timestamp": "2025-07-20T12:09:44.342794Z",
  "request_id": "req_687cdc8853b40"
}
```

#### Error Responses

**Authentication Error (401):**
```json
{
  "status": "error",
  "error_code": "AUTH_001",
  "message": "Authentication failed",
  "details": "Invalid API key or secret",
  "timestamp": "2025-07-20T12:18:51.918227Z",
  "request_id": "req_687cdeabe030e"
}
```

**Validation Error (400):**
```json
{
  "status": "error",
  "error_code": "VALIDATION_001",
  "message": "Validation failed",
  "details": "Request data validation failed",
  "timestamp": "2025-07-20T12:18:51.918227Z",
  "request_id": "req_687cdeabe030e",
  "errors": {
    "customer_name": ["The customer name field is required when target is individual."],
    "customer_phone": ["The customer phone field is required when target is individual."]
  },
  "suggestions": [
    "For individual targets, customer_name and customer_phone are required",
    "Phone number must be in format: 255XXXXXXXXX",
    "Ensure all required fields are provided"
  ]
}
```

---

### 2. Get Universal Payment Link Details

**Endpoint:** `GET /payment-links/universal/{shortCode}`

**Description:** Retrieves details of a universal payment link by its short code.

#### Path Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `shortCode` | string | The short code of the payment link |

#### Success Response (200 OK)
```json
{
  "status": "success",
  "message": "Payment link details retrieved successfully",
  "data": {
    "link_id": "LINK_ABC123DEF456",
    "short_code": "AbC123Xy",
    "payment_url": "http://127.0.0.1:8000/pay/AbC123Xy",
    "qr_code_data": "http://127.0.0.1:8000/pay/AbC123Xy",
    "target_type": "individual",
    "is_public": false,
    "description": "Loan Repayment - Installment #3 of 12",
    "total_amount": 75000,
    "currency": "TZS",
    "customer_reference": "LOAN_2025_001",
    "customer_name": "Sarah Johnson",
    "customer_phone": "255723456789",
    "customer_email": "sarah@email.com",
    "items": [
      {
        "item_code": "ITEM_001",
        "type": "service",
        "product_service_reference": "LOAN_INST_003",
        "product_service_name": "Loan Installment",
        "description": null,
        "amount": 75000,
        "paid_amount": 25000,
        "remaining_amount": 50000,
        "payment_percentage": 33.33,
        "status": "partial",
        "is_required": true,
        "allow_partial": false,
        "minimum_amount": null,
        "quantity": 1,
        "subcategory": null
      }
    ],
    "expires_at": "2025-07-27T10:00:00.000000Z",
    "max_uses": 1,
    "current_uses": 1,
    "is_reusable": false,
    "allowed_networks": ["TZ-MPESA-C2B", "TZ-AIRTEL-C2B", "TZ-TIGO-C2B", "TZ-HALOPESA-C2B"],
    "webhook_url": null,
    "success_url": null,
    "failure_url": null,
    "cancel_url": null,
    "created_at": "2025-07-20T12:09:44.000000Z"
  },
  "timestamp": "2025-07-20T12:09:44.342794Z",
  "request_id": "req_687cdc8853b40"
}
```

#### Error Response (404 Not Found)
```json
{
  "status": "error",
  "error_code": "NOT_FOUND_001",
  "message": "Payment link not found",
  "details": "The requested payment link does not exist or is not accessible",
  "timestamp": "2025-07-20T12:18:51.918227Z",
  "request_id": "req_687cdeabe030e"
}
```

---

### 3. Get Universal Payment Link Statistics

**Endpoint:** `GET /payment-links/universal/{shortCode}/stats`

**Description:** Retrieves payment statistics for a universal payment link.

#### Path Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `shortCode` | string | The short code of the payment link |

#### Success Response (200 OK)
```json
{
  "status": "success",
  "message": "Payment link statistics retrieved successfully",
  "data": {
    "link_id": "LINK_ABC123DEF456",
    "short_code": "AbC123Xy",
    "total_amount": 75000,
    "paid_amount": 25000,
    "remaining_amount": 50000,
    "payment_percentage": 33.33,
    "total_transactions": 1,
    "successful_transactions": 1,
    "failed_transactions": 0,
    "pending_transactions": 0,
    "cancelled_transactions": 0,
    "payment_methods": {
      "TZ-MPESA-C2B": {
        "count": 1,
        "amount": 25000
      }
    },
    "payment_timeline": [
      {
        "date": "2025-07-20",
        "transactions": 1,
        "amount": 25000
      }
    ],
    "item_statistics": [
      {
        "item_code": "ITEM_001",
        "product_service_name": "Loan Installment",
        "total_amount": 75000,
        "paid_amount": 25000,
        "remaining_amount": 50000,
        "payment_percentage": 33.33,
        "status": "partial"
      }
    ],
    "created_at": "2025-07-20T12:09:44.000000Z",
    "last_payment_at": "2025-07-20T12:15:30.000000Z"
  },
  "timestamp": "2025-07-20T12:18:51.918227Z",
  "request_id": "req_687cdeabe030e"
}
```

---

## 🔐 Security Features

### Military-Grade Security Implementation

1. **HMAC-SHA256 Authentication**
   - All requests must be signed with HMAC-SHA256
   - Signature includes method, URL, timestamp, nonce, and request data
   - Prevents request tampering and replay attacks

2. **Timestamp Validation**
   - Requests must include current Unix timestamp
   - Server validates timestamp to prevent replay attacks
   - Configurable time window for timestamp acceptance

3. **Nonce Uniqueness**
   - Each request must include a unique nonce
   - Prevents duplicate request processing
   - Nonce validation with configurable window

4. **Rate Limiting**
   - Configurable rate limits per API key
   - IP-based rate limiting for additional protection
   - Automatic blocking of excessive requests

5. **IP Blocking**
   - Automatic blocking of malicious IP addresses
   - Threat detection and response system
   - Configurable IP whitelist/blacklist

6. **Request Validation**
   - Comprehensive input validation
   - SQL injection prevention
   - XSS protection
   - Data sanitization

---

## 📱 Code Examples

### PHP Example
```php
<?php
require_once 'vendor/autoload.php';

function generateHmacSignature($data, $secret) {
    $canonicalData = json_encode($data, JSON_UNESCAPED_SLASHES);
    return hash_hmac('sha256', $canonicalData, $secret);
}

function makeAuthenticatedRequest($url, $data, $apiKey, $apiSecret) {
    $timestamp = time();
    $nonce = uniqid();
    
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
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status_code' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

// Usage
$clientConfig = [
    'api_key' => 'your_api_key',
    'api_secret' => 'your_api_secret',
    'base_url' => 'http://127.0.0.1:8000/api'
];

$linkData = [
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

$url = $clientConfig['base_url'] . '/payment-links/generate-universal';
$result = makeAuthenticatedRequest($url, $linkData, $clientConfig['api_key'], $clientConfig['api_secret']);

if ($result['status_code'] === 201) {
    echo "Payment link generated: " . $result['response']['data']['payment_url'];
} else {
    echo "Error: " . $result['response']['message'];
}
?>
```

### JavaScript/Node.js Example
```javascript
const crypto = require('crypto');
const axios = require('axios');

const clientConfig = {
  apiKey: 'your_api_key',
  apiSecret: 'your_api_secret',
  baseUrl: 'http://127.0.0.1:8000/api'
};

function generateHmacSignature(data, secret) {
  const canonicalData = JSON.stringify(data);
  return crypto.createHmac('sha256', secret).update(canonicalData).digest('hex');
}

async function generateUniversalPaymentLink(linkData) {
  const timestamp = Math.floor(Date.now() / 1000);
  const nonce = Math.random().toString(36).substring(2);
  
  const signaturePayload = {
    method: 'POST',
    url: clientConfig.baseUrl + '/payment-links/generate-universal',
    timestamp: timestamp,
    nonce: nonce,
    data: linkData
  };
  
  const signature = generateHmacSignature(signaturePayload, clientConfig.apiSecret);
  
  const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-API-Key': clientConfig.apiKey,
    'X-Timestamp': timestamp,
    'X-Nonce': nonce,
    'X-Signature': signature
  };
  
  try {
    const response = await axios.post(
      clientConfig.baseUrl + '/payment-links/generate-universal',
      linkData,
      { headers: headers }
    );
    return response.data;
  } catch (error) {
    console.error('Error generating payment link:', error.response?.data || error.message);
    throw error;
  }
}

// Usage
const linkData = {
  description: 'Loan Repayment - Installment #3 of 12',
  target: 'individual',
  customer_reference: 'LOAN_2025_001',
  customer_name: 'Sarah Johnson',
  customer_phone: '255723456789',
  customer_email: 'sarah@email.com',
  expires_at: '2025-07-27T10:00:00Z',
  items: [
    {
      type: 'service',
      product_service_reference: 'LOAN_INST_003',
      product_service_name: 'Loan Installment',
      amount: 75000,
      is_required: true,
      allow_partial: false
    }
  ]
};

generateUniversalPaymentLink(linkData)
  .then(result => {
    console.log('Payment link generated:', result.data.payment_url);
  })
  .catch(error => {
    console.error('Failed to generate payment link:', error);
  });
```

### Python Example
```python
import requests
import hashlib
import hmac
import json
import time
import uuid

client_config = {
    'api_key': 'your_api_key',
    'api_secret': 'your_api_secret',
    'base_url': 'http://127.0.0.1:8000/api'
}

def generate_hmac_signature(data, secret):
    canonical_data = json.dumps(data, separators=(',', ':'))
    return hmac.new(
        secret.encode('utf-8'),
        canonical_data.encode('utf-8'),
        hashlib.sha256
    ).hexdigest()

def generate_universal_payment_link(link_data):
    timestamp = int(time.time())
    nonce = str(uuid.uuid4())
    
    signature_payload = {
        'method': 'POST',
        'url': client_config['base_url'] + '/payment-links/generate-universal',
        'timestamp': timestamp,
        'nonce': nonce,
        'data': link_data
    }
    
    signature = generate_hmac_signature(signature_payload, client_config['api_secret'])
    
    headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-API-Key': client_config['api_key'],
        'X-Timestamp': str(timestamp),
        'X-Nonce': nonce,
        'X-Signature': signature
    }
    
    try:
        response = requests.post(
            client_config['base_url'] + '/payment-links/generate-universal',
            json=link_data,
            headers=headers
        )
        response.raise_for_status()
        return response.json()
    except requests.exceptions.RequestException as e:
        print(f'Error generating payment link: {e}')
        raise

# Usage
link_data = {
    "description": "Loan Repayment - Installment #3 of 12",
    "target": "individual",
    "customer_reference": "LOAN_2025_001",
    "customer_name": "Sarah Johnson",
    "customer_phone": "255723456789",
    "customer_email": "sarah@email.com",
    "expires_at": "2025-07-27T10:00:00Z",
    "items": [
        {
            "type": "service",
            "product_service_reference": "LOAN_INST_003",
            "product_service_name": "Loan Installment",
            "amount": 75000,
            "is_required": True,
            "allow_partial": False
        }
    ]
}

try:
    result = generate_universal_payment_link(link_data)
    print('Payment link generated:', result['data']['payment_url'])
except Exception as e:
    print('Failed to generate payment link:', e)
```

### cURL Example
```bash
curl -X POST 'http://127.0.0.1:8000/api/payment-links/generate-universal' \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -H 'X-API-Key: your_api_key' \
  -H 'X-Timestamp: 1753013931' \
  -H 'X-Nonce: 687cdeabe04eb' \
  -H 'X-Signature: 83a3820f5094de8c0883cf6a3c95627cfe5d04dac6792af5707b5d3a585847aa' \
  -d '{
    "description": "Loan Repayment - Installment #3 of 12",
    "target": "individual",
    "customer_reference": "LOAN_2025_001",
    "customer_name": "Sarah Johnson",
    "customer_phone": "255723456789",
    "customer_email": "sarah@email.com",
    "expires_at": "2025-07-27T10:00:00Z",
    "items": [
      {
        "type": "service",
        "product_service_reference": "LOAN_INST_003",
        "product_service_name": "Loan Installment",
        "amount": 75000,
        "is_required": true,
        "allow_partial": false
      }
    ]
  }'
```

---

## 📊 Error Codes

| Error Code | HTTP Status | Description |
|------------|-------------|-------------|
| `AUTH_001` | 401 | Authentication failed - Invalid API key or secret |
| `AUTH_002` | 401 | Signature validation failed |
| `AUTH_003` | 401 | Timestamp expired or invalid |
| `AUTH_004` | 401 | Nonce already used |
| `VALIDATION_001` | 400 | Request validation failed |
| `VALIDATION_002` | 400 | Required fields missing |
| `VALIDATION_003` | 400 | Invalid field format |
| `RATE_LIMIT_001` | 429 | Rate limit exceeded |
| `NOT_FOUND_001` | 404 | Payment link not found |
| `GENERATION_001` | 400 | Payment link generation failed |
| `INTERNAL_001` | 500 | Internal server error |

---

## 🔄 Webhook Notifications

When a webhook URL is provided, the system will send POST notifications for payment events:

### Payment Success Webhook
```json
{
  "event": "payment.success",
  "link_id": "LINK_ABC123DEF456",
  "short_code": "AbC123Xy",
  "transaction_id": "TXN_XYZ789",
  "amount": 25000,
  "currency": "TZS",
  "payment_method": "TZ-MPESA-C2B",
  "customer_phone": "255723456789",
  "timestamp": "2025-07-20T12:15:30.000000Z"
}
```

### Payment Failure Webhook
```json
{
  "event": "payment.failed",
  "link_id": "LINK_ABC123DEF456",
  "short_code": "AbC123Xy",
  "transaction_id": "TXN_XYZ789",
  "amount": 25000,
  "currency": "TZS",
  "payment_method": "TZ-MPESA-C2B",
  "error_code": "PAYMENT_DECLINED",
  "error_message": "Insufficient funds",
  "timestamp": "2025-07-20T12:15:30.000000Z"
}
```

---

## 📋 Best Practices

1. **Security**
   - Keep API keys secure and never expose them in client-side code
   - Use HTTPS in production environments
   - Implement proper error handling for authentication failures
   - Validate all response data before processing

2. **Rate Limiting**
   - Implement exponential backoff for retry logic
   - Monitor rate limit headers in responses
   - Cache successful responses when appropriate

3. **Error Handling**
   - Always check HTTP status codes
   - Parse error messages for user-friendly display
   - Log errors for debugging purposes
   - Implement fallback mechanisms

4. **Data Validation**
   - Validate phone numbers in 255XXXXXXXXX format
   - Ensure amounts are within acceptable ranges (100-1,000,000 TZS)
   - Validate email addresses before sending
   - Check expiration dates are in the future

5. **Testing**
   - Test with both individual and public targets
   - Verify webhook notifications work correctly
   - Test error scenarios and edge cases
   - Validate signature generation across different platforms

---

## 🚀 Getting Started

1. **Obtain API Credentials**
   - Contact your system administrator for API key and secret
   - Ensure your client account is active and configured

2. **Test Authentication**
   - Use the provided code examples to test basic connectivity
   - Verify signature generation works correctly

3. **Generate Your First Payment Link**
   - Start with a simple individual payment link
   - Test the complete flow from generation to payment

4. **Implement Webhooks**
   - Set up webhook endpoints to receive payment notifications
   - Test webhook delivery and processing

5. **Go Live**
   - Switch to production environment
   - Monitor logs and webhook deliveries
   - Implement proper error handling and monitoring

---

## 📞 Support

For technical support or questions about the API:

- **Email:** support@zimaesb.com
- **Documentation:** https://docs.zimaesb.com
- **Status Page:** https://status.zimaesb.com

---

*Last updated: July 20, 2025* 