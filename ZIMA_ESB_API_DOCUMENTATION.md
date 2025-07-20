# 📚 ZIMA ESB API Documentation

## 🎯 Overview

The ZIMA Enterprise Service Bus (ESB) API provides a unified interface for integrating with multiple mobile money networks in Tanzania. This API enables clients to process payments, check transaction status, and receive real-time notifications through webhooks.

### **Supported Mobile Networks**
- **Airtel Money** (TZ-AIRTEL-C2B)
- **Tigo Pesa** (TZ-TIGO-C2B)
- **M-Pesa** (TZ-MPESA-C2B)
- **HaloPesa** (TZ-HALOPESA-C2B)

### **Base URL**
```
Production: https://api.zimaesb.com/api
Sandbox: https://sandbox.zimaesb.com/api
Local: http://127.0.0.1:8000/api
```

---

## 🔐 Authentication

### **API Key Authentication**

All API requests require authentication using API Key and Secret headers:

```http
X-API-Key: your_api_key_here
X-API-Secret: your_api_secret_here
```

### **Getting API Credentials**

1. Contact ZIMA ESB support team
2. Provide your business details and integration requirements
3. Receive your unique API credentials
4. Test in sandbox environment before going live

### **Security Best Practices**

- ✅ Keep your API credentials secure and never expose them in client-side code
- ✅ Use HTTPS for all production requests
- ✅ Rotate API keys regularly
- ✅ Monitor API usage and set up alerts for unusual activity
- ✅ Implement proper error handling and retry logic

---

## 📋 API Endpoints

### **1. Money Collection Service**

#### **Endpoint**
```http
POST /api/esb/MONEY_COLLECTION
```

#### **Description**
Initiate a money collection transaction from a customer's mobile money account.

#### **Headers**
```http
Content-Type: application/json
X-API-Key: your_api_key_here
X-API-Secret: your_api_secret_here
Accept: application/json
```

#### **Request Body**
```json
{
  "customer_phone": "255778342299",
  "mobile_network": "TZ-AIRTEL-C2B",
  "amount": 5000,
  "description": "Payment for services",
  "reference": "TXN_123456",
  "date": "2025-07-19 17:26:04",
  "webhook_url": "https://your-webhook-url.com/callback"
}
```

#### **Response**
```json
{
  "status": "PENDING_ACK",
  "reference": "TXN_123456",
  "transaction_id": "X50jcLD-U"
}
```

---

### **2. Collection Balance Service**

#### **Endpoint**
```http
POST /api/esb/COLLECTION_BALANCE
```

#### **Description**
Retrieve the balance of your collection account.

#### **Headers**
```http
Content-Type: application/json
X-API-Key: your_api_key_here
X-API-Secret: your_api_secret_here
Accept: application/json
```

#### **Request Body**
No request body required.

#### **Response**
```json
{
  "available_balance": 10000,
  "current_balance": 10000,
  "account_number": "8000837333",
  "account_status": "ACTIVE",
  "account_name": "ABC TRADERS LTD - Collection"
}
```

---

### **3. Collection Statement Service**

#### **Endpoint**
```http
POST /api/esb/COLLECTION_STATEMENT
```

#### **Description**
Retrieve the statement of your collection account for a specified date range.

#### **Headers**
```http
Content-Type: application/json
X-API-Key: your_api_key_here
X-API-Secret: your_api_secret_here
Accept: application/json
```

#### **Request Body**
```json
{
  "start_date": "2025-01-01",
  "end_date": "2025-01-31"
}
```

#### **Response**
```json
[
  {
    "account_number": "6682002103",
    "transaction_type": "CR",
    "transaction_reference": "P50ZEXA2014301CI",
    "description": "CURRENT MONTH SALARY",
    "transaction_date": "2025-01-22",
    "value_date": "2025-01-22",
    "amount_credited": 2232745.15,
    "amount_debited": null,
    "balance": 2232745.15
  },
  {
    "account_number": "668200980",
    "transaction_type": "CR",
    "transaction_reference": "P50ZEXA2014301CI",
    "description": "RENT PAYMENT FOR JAN 2025",
    "transaction_date": "2025-01-22",
    "value_date": "2025-01-22",
    "amount_credited": 1950000.00,
    "amount_debited": null,
    "balance": 2132745.15
  }
]
```

---

### **4. Payment Status Service**

#### **Endpoint**
```http
POST /api/esb/PAYMENT_STATUS
```

#### **Description**
Check the current status of a USSD push transaction.

#### **Headers**
```http
Content-Type: application/json
X-API-Key: your_api_key_here
X-API-Secret: your_api_secret_here
Accept: application/json
```

#### **Request Body**
```json
{
  "transaction_id": "X50jcLD-U",
  "reference": "TXN_123456"
}
```

#### **Response**
```json
{
  "status": "PAYMENT_ACCEPTED",
  "transaction_id": "X50jcLD-U",
  "reference": "TXN_123456"
}
```

#### **Request Parameters**

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `customer_phone` | string | ✅ | Customer's phone number (Tanzania format) | "255692410353" |
| `mobile_network` | string | ✅ | Mobile money network code | "TZ-AIRTEL-C2B" |
| `amount` | integer | ✅ | Transaction amount in TZS | 1000 |
| `description` | string | ✅ | Transaction description | "Payment for services" |
| `reference` | string | ✅ | Unique transaction reference | "TXN_123456" |
| `date` | string | ✅ | Transaction date (Y-m-d H:i:s) | "2025-07-19 17:26:04" |
| `webhook_url` | string | ✅ | Webhook URL for notifications | "https://your-webhook-url.com/callback" |

#### **Mobile Network Codes**

| Network | Code | Description |
|---------|------|-------------|
| **Airtel Money** | `TZ-AIRTEL-C2B` | Airtel Money Customer to Business |
| **Tigo Pesa** | `TZ-TIGO-C2B` | Tigo Pesa Customer to Business |
| **M-Pesa** | `TZ-MPESA-C2B` | M-Pesa Customer to Business |
| **HaloPesa** | `TZ-HALOPESA-C2B` | HaloPesa Customer to Business |

#### **Amount Limits**

| Category | Minimum | Maximum | Description |
|----------|---------|---------|-------------|
| **Small** | TZS 100 | TZS 1,000 | Low-value transactions |
| **Medium** | TZS 1,001 | TZS 50,000 | Standard transactions |
| **Large** | TZS 50,001 | TZS 1,000,000 | High-value transactions |

#### **Success Response (200)**
```json
{
  "status": "success",
  "reference": "TXN_123456",
  "transaction_id": "TXN_687BD8CDE13CD",
  "message": "Transaction initiated successfully"
}
```

#### **Error Response (400)**
```json
{
  "status": "error",
  "message": "Invalid request data",
  "errors": {
    "customer_phone": ["The customer phone field is required."],
    "mobile_network": ["The selected mobile network is invalid."]
  }
}
```

#### **Error Response (401)**
```json
{
  "status": "error",
  "message": "Unauthorized. Invalid API credentials."
}
```

#### **Error Response (500)**
```json
{
  "status": "error",
  "message": "Internal server error. Please try again later."
}
```

---

### **2. Transaction Status Check**

#### **Endpoint**
```http
GET /api/esb/transaction/{transaction_id}
```

#### **Description**
Check the current status of a transaction.

#### **Headers**
```http
X-API-Key: your_api_key_here
X-API-Secret: your_api_secret_here
Accept: application/json
```

#### **Path Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `transaction_id` | string | ✅ | Transaction ID from the initial request |

#### **Success Response (200)**
```json
{
  "status": "success",
  "transaction": {
    "id": 1,
    "transaction_id": "TXN_687BD8CDE13CD",
    "reference": "TXN_123456",
    "client_id": 1,
    "service_id": 1,
    "aggregator_id": 1,
    "customer_phone": "255692410353",
    "mobile_network": "TZ-AIRTEL-C2B",
    "amount": 1000,
    "description": "Payment for services",
    "status": "completed",
    "aggregator_reference": "EXT_ABC123",
    "aggregator_response": "Success",
    "webhook_url": "https://your-webhook-url.com/callback",
    "webhook_sent": true,
    "webhook_response": "200 OK",
    "created_at": "2025-07-19T17:26:04.000000Z",
    "updated_at": "2025-07-19T17:26:10.000000Z"
  }
}
```

#### **Transaction Status Values**

| Status | Description |
|--------|-------------|
| `pending` | Transaction initiated, waiting for customer action |
| `processing` | Transaction being processed by aggregator |
| `completed` | Transaction completed successfully |
| `failed` | Transaction failed |
| `cancelled` | Transaction cancelled by customer |
| `expired` | Transaction expired |

---

### **3. Callback Status Check**

#### **Endpoint**
```http
GET /api/callback/status/{transaction_id}
```

#### **Description**
Check the callback processing status for a transaction.

#### **Headers**
```http
X-API-Key: your_api_key_here
X-API-Secret: your_api_secret_here
Accept: application/json
```

#### **Success Response (200)**
```json
{
  "status": "success",
  "callback_processed": true,
  "callback_timestamp": "2025-07-19T17:26:10.000000Z",
  "aggregator_reference": "EXT_ABC123",
  "aggregator_status": "success"
}
```

---

## 🔄 Webhook Notifications

### **Webhook Endpoint Setup**

When you initiate a transaction, provide a webhook URL where you'll receive real-time notifications about transaction status changes.

### **Webhook Payload Format**

```json
{
  "transaction_id": "TXN_687BD8CDE13CD",
  "reference": "TXN_123456",
  "status": "completed",
  "amount": 1000,
  "customer_phone": "255692410353",
  "mobile_network": "TZ-AIRTEL-C2B",
  "description": "Payment for services",
  "aggregator_reference": "EXT_ABC123",
  "aggregator_response": "Success",
  "timestamp": "2025-07-19T17:26:10.000000Z",
  "signature": "hmac_signature_here"
}
```

### **Webhook Headers**

```http
Content-Type: application/json
X-ZIMA-Signature: hmac_signature_here
X-ZIMA-Timestamp: 1752947170
```

### **Webhook Verification**

To verify webhook authenticity, calculate HMAC-SHA256:

```php
$signature = hash_hmac('sha256', $payload, $webhook_secret);
```

### **Webhook Response**

Your webhook endpoint should respond with:

```http
HTTP/1.1 200 OK
Content-Type: application/json

{
  "status": "received"
}
```

### **Webhook Retry Logic**

- **Retry Attempts**: 3 attempts
- **Retry Intervals**: 5 minutes, 15 minutes, 1 hour
- **Retry Conditions**: HTTP 4xx/5xx responses, network timeouts

---

## 📱 Mobile Network Integration

### **Phone Number Format**

All phone numbers must be in Tanzania international format:

```
255 + 9 digits
Example: 255692410353 (for 0692410353)
```

### **Network-Specific Requirements**

#### **Airtel Money (TZ-AIRTEL-C2B)**
- **Prefix**: 069, 068
- **Format**: 25569XXXXXXX or 25568XXXXXXX
- **Daily Limit**: TZS 1,000,000
- **Transaction Limit**: TZS 1,000,000

#### **Tigo Pesa (TZ-TIGO-C2B)**
- **Prefix**: 071, 065, 067
- **Format**: 25571XXXXXXX, 25565XXXXXXX, or 25567XXXXXXX
- **Daily Limit**: TZS 1,000,000
- **Transaction Limit**: TZS 1,000,000

#### **M-Pesa (TZ-MPESA-C2B)**
- **Prefix**: 074, 075, 076
- **Format**: 25574XXXXXXX, 25575XXXXXXX, or 25576XXXXXXX
- **Daily Limit**: TZS 1,000,000
- **Transaction Limit**: TZS 1,000,000

#### **HaloPesa (TZ-HALOPESA-C2B)**
- **Prefix**: 062, 063
- **Format**: 25562XXXXXXX or 25563XXXXXXX
- **Daily Limit**: TZS 1,000,000
- **Transaction Limit**: TZS 1,000,000

---

## 🛠️ Code Examples

### **PHP Example**

```php
<?php

class ZimaEsbClient {
    private $apiKey;
    private $apiSecret;
    private $baseUrl;
    
    public function __construct($apiKey, $apiSecret, $baseUrl = 'https://api.zimaesb.com/api') {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->baseUrl = $baseUrl;
    }
    
    public function initiateMoneyCollection($data) {
        $url = $this->baseUrl . '/esb/MONEY_COLLECTION';
        
        $headers = [
            'Content-Type: application/json',
            'X-API-Key: ' . $this->apiKey,
            'X-API-Secret: ' . $this->apiSecret,
            'Accept: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'status_code' => $httpCode,
            'response' => json_decode($response, true)
        ];
    }
    
    public function checkTransactionStatus($transactionId) {
        $url = $this->baseUrl . '/esb/transaction/' . $transactionId;
        
        $headers = [
            'X-API-Key: ' . $this->apiKey,
            'X-API-Secret: ' . $this->apiSecret,
            'Accept: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'status_code' => $httpCode,
            'response' => json_decode($response, true)
        ];
    }
}

// Usage Example
$client = new ZimaEsbClient('your_api_key', 'your_api_secret');

$transactionData = [
    'customer_phone' => '255692410353',
    'mobile_network' => 'TZ-AIRTEL-C2B',
    'amount' => 1000,
    'description' => 'Payment for services',
    'reference' => 'TXN_' . time(),
    'date' => date('Y-m-d H:i:s'),
    'webhook_url' => 'https://your-webhook-url.com/callback'
];

$result = $client->initiateMoneyCollection($transactionData);

if ($result['status_code'] === 200) {
    echo "Transaction initiated: " . $result['response']['transaction_id'];
} else {
    echo "Error: " . $result['response']['message'];
}
?>
```

### **JavaScript/Node.js Example**

```javascript
const axios = require('axios');

class ZimaEsbClient {
    constructor(apiKey, apiSecret, baseUrl = 'https://api.zimaesb.com/api') {
        this.apiKey = apiKey;
        this.apiSecret = apiSecret;
        this.baseUrl = baseUrl;
    }
    
    async initiateMoneyCollection(data) {
        try {
            const response = await axios.post(
                `${this.baseUrl}/esb/MONEY_COLLECTION`,
                data,
                {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-API-Key': this.apiKey,
                        'X-API-Secret': this.apiSecret,
                        'Accept': 'application/json'
                    },
                    timeout: 30000
                }
            );
            
            return {
                status_code: response.status,
                response: response.data
            };
        } catch (error) {
            return {
                status_code: error.response?.status || 500,
                response: error.response?.data || { message: 'Network error' }
            };
        }
    }
    
    async checkTransactionStatus(transactionId) {
        try {
            const response = await axios.get(
                `${this.baseUrl}/esb/transaction/${transactionId}`,
                {
                    headers: {
                        'X-API-Key': this.apiKey,
                        'X-API-Secret': this.apiSecret,
                        'Accept': 'application/json'
                    },
                    timeout: 30000
                }
            );
            
            return {
                status_code: response.status,
                response: response.data
            };
        } catch (error) {
            return {
                status_code: error.response?.status || 500,
                response: error.response?.data || { message: 'Network error' }
            };
        }
    }
}

// Usage Example
const client = new ZimaEsbClient('your_api_key', 'your_api_secret');

const transactionData = {
    customer_phone: '255692410353',
    mobile_network: 'TZ-AIRTEL-C2B',
    amount: 1000,
    description: 'Payment for services',
    reference: `TXN_${Date.now()}`,
    date: new Date().toISOString().slice(0, 19).replace('T', ' '),
    webhook_url: 'https://your-webhook-url.com/callback'
};

async function processTransaction() {
    const result = await client.initiateMoneyCollection(transactionData);
    
    if (result.status_code === 200) {
        console.log('Transaction initiated:', result.response.transaction_id);
        
        // Check status after 5 seconds
        setTimeout(async () => {
            const statusResult = await client.checkTransactionStatus(result.response.transaction_id);
            console.log('Transaction status:', statusResult.response);
        }, 5000);
    } else {
        console.log('Error:', result.response.message);
    }
}

processTransaction();
```

### **Python Example**

```python
import requests
import json
from datetime import datetime

class ZimaEsbClient:
    def __init__(self, api_key, api_secret, base_url='https://api.zimaesb.com/api'):
        self.api_key = api_key
        self.api_secret = api_secret
        self.base_url = base_url
    
    def initiate_money_collection(self, data):
        url = f"{self.base_url}/esb/MONEY_COLLECTION"
        
        headers = {
            'Content-Type': 'application/json',
            'X-API-Key': self.api_key,
            'X-API-Secret': self.api_secret,
            'Accept': 'application/json'
        }
        
        try:
            response = requests.post(
                url,
                json=data,
                headers=headers,
                timeout=30,
                verify=True
            )
            
            return {
                'status_code': response.status_code,
                'response': response.json()
            }
        except requests.exceptions.RequestException as e:
            return {
                'status_code': 500,
                'response': {'message': f'Network error: {str(e)}'}
            }
    
    def check_transaction_status(self, transaction_id):
        url = f"{self.base_url}/esb/transaction/{transaction_id}"
        
        headers = {
            'X-API-Key': self.api_key,
            'X-API-Secret': self.api_secret,
            'Accept': 'application/json'
        }
        
        try:
            response = requests.get(
                url,
                headers=headers,
                timeout=30,
                verify=True
            )
            
            return {
                'status_code': response.status_code,
                'response': response.json()
            }
        except requests.exceptions.RequestException as e:
            return {
                'status_code': 500,
                'response': {'message': f'Network error: {str(e)}'}
            }

# Usage Example
client = ZimaEsbClient('your_api_key', 'your_api_secret')

transaction_data = {
    'customer_phone': '255692410353',
    'mobile_network': 'TZ-AIRTEL-C2B',
    'amount': 1000,
    'description': 'Payment for services',
    'reference': f'TXN_{int(datetime.now().timestamp())}',
    'date': datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
    'webhook_url': 'https://your-webhook-url.com/callback'
}

result = client.initiate_money_collection(transaction_data)

if result['status_code'] == 200:
    print(f"Transaction initiated: {result['response']['transaction_id']}")
    
    # Check status after 5 seconds
    import time
    time.sleep(5)
    
    status_result = client.check_transaction_status(result['response']['transaction_id'])
    print(f"Transaction status: {status_result['response']}")
else:
    print(f"Error: {result['response']['message']}")
```

---

## 🚨 Error Handling

### **Common Error Codes**

| HTTP Code | Error Type | Description | Solution |
|-----------|------------|-------------|----------|
| **400** | Bad Request | Invalid request data | Check request parameters |
| **401** | Unauthorized | Invalid API credentials | Verify API key and secret |
| **403** | Forbidden | Insufficient permissions | Contact support |
| **404** | Not Found | Resource not found | Check transaction ID |
| **422** | Validation Error | Data validation failed | Check error details |
| **429** | Too Many Requests | Rate limit exceeded | Implement backoff |
| **500** | Server Error | Internal server error | Retry later |

### **Error Response Format**

```json
{
  "status": "error",
  "message": "Error description",
  "errors": {
    "field_name": ["Specific error message"]
  },
  "code": "ERROR_CODE",
  "timestamp": "2025-07-19T17:26:04.000000Z"
}
```

### **Best Practices for Error Handling**

1. **Always check HTTP status codes**
2. **Implement exponential backoff for retries**
3. **Log all errors for debugging**
4. **Handle network timeouts gracefully**
5. **Validate responses before processing**

---

## 📊 Rate Limits

### **Default Limits**

| Endpoint | Rate Limit | Window |
|----------|------------|--------|
| **Money Collection** | 100 requests | Per minute |
| **Status Check** | 1000 requests | Per minute |
| **Callback Status** | 1000 requests | Per minute |

### **Rate Limit Headers**

```http
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1752947200
```

### **Rate Limit Exceeded Response**

```json
{
  "status": "error",
  "message": "Rate limit exceeded. Please try again later.",
  "retry_after": 60
}
```

---

## 🔒 Security

### **Data Encryption**

- **In Transit**: TLS 1.2+ encryption
- **At Rest**: AES-256 encryption
- **API Keys**: HMAC-SHA256 signatures

### **IP Whitelisting**

For enhanced security, you can whitelist your IP addresses:

1. Contact support with your IP addresses
2. Provide business justification
3. IP addresses will be whitelisted within 24 hours

### **Webhook Security**

- **Signature Verification**: Always verify webhook signatures
- **Timestamp Validation**: Check webhook timestamps
- **HTTPS Only**: Use HTTPS for webhook endpoints

---

## 📞 Support

### **Contact Information**

- **Email**: support@zimaesb.com
- **Phone**: +255 22 123 4567
- **Hours**: Monday - Friday, 8:00 AM - 6:00 PM EAT

### **Support Channels**

1. **Technical Support**: For API integration issues
2. **Business Support**: For account and billing questions
3. **Emergency Support**: For critical production issues

### **Documentation Resources**

- **API Reference**: This document
- **SDK Downloads**: Available on GitHub
- **Postman Collection**: Import for testing
- **Webhook Testing**: Use webhook.site for development

---

## 📋 Integration Checklist

### **Pre-Integration**

- [ ] Contact ZIMA ESB support team
- [ ] Receive API credentials
- [ ] Set up webhook endpoint
- [ ] Configure IP whitelisting (optional)
- [ ] Set up monitoring and logging

### **Development**

- [ ] Implement API client library
- [ ] Add error handling and retry logic
- [ ] Test in sandbox environment
- [ ] Implement webhook verification
- [ ] Add transaction status polling

### **Testing**

- [ ] Test all mobile networks
- [ ] Test various amount categories
- [ ] Test error scenarios
- [ ] Test webhook notifications
- [ ] Test callback processing

### **Production**

- [ ] Switch to production API endpoints
- [ ] Update webhook URLs
- [ ] Monitor transaction success rates
- [ ] Set up alerts for failures
- [ ] Implement reconciliation processes

---

## 🎯 Quick Start Guide

### **Step 1: Get API Credentials**
```bash
# Contact support@zimaesb.com
# Receive your API key and secret
```

### **Step 2: Test Integration**
```bash
# Use the provided code examples
# Test with sandbox environment
# Verify webhook notifications
```

### **Step 3: Go Live**
```bash
# Switch to production endpoints
# Monitor transaction success rates
# Set up monitoring and alerts
```

---

**🎉 Welcome to ZIMA ESB! We're here to help you integrate mobile money payments seamlessly into your application.** 