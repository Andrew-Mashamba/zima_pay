# ZIMA ESB - Tembo Money Collection API Documentation

## Overview

The ZIMA ESB (Enterprise Service Bus) provides a unified API interface for Tembo Money Collection services. This documentation covers the working endpoints for mobile money collection, balance checking, and transaction history.

**Base URL:** `http://127.0.0.1:8000/api/esb`

**API Version:** 1.0.0

---

## Authentication

All API requests require authentication using API key and secret in the request headers.

### Headers
```
X-API-Key: your_api_key_here
X-API-Secret: your_api_secret_here
Content-Type: application/json
```

### Example
```bash
curl -X POST "http://127.0.0.1:8000/api/esb/MONEY_COLLECTION" \
  -H "X-API-Key: sample_client_key_ABC123DEF456" \
  -H "X-API-Secret: sample_client_secret_XYZ789GHI012" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_phone": "255712345678",
    "mobile_network": "TZ-AIRTEL-C2B",
    "amount": 1000,
    "description": "Payment for services",
    "reference": "TXN_123456789",
    "date": "2025-07-20 10:30:00",
    "webhook_url": "https://your-webhook.com/callback"
  }'
```

---

## 1. Money Collection (Primary Service)

Initiates a mobile money collection transaction from a customer's phone number.

### Endpoint
```
POST /MONEY_COLLECTION
```

### Request Body

| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| `customer_phone` | string | ✅ | Customer's phone number (with country code) | `"255712345678"` |
| `mobile_network` | string | ✅ | Mobile network provider | `"TZ-AIRTEL-C2B"` |
| `amount` | integer | ✅ | Transaction amount in TZS | `1000` |
| `description` | string | ✅ | Transaction description | `"Payment for services"` |
| `reference` | string | ✅ | Unique transaction reference | `"TXN_123456789"` |
| `date` | string | ✅ | Transaction date (YYYY-MM-DD HH:mm:ss) | `"2025-07-20 10:30:00"` |
| `webhook_url` | string | ❌ | Webhook URL for status updates | `"https://your-webhook.com/callback"` |

### Request Example
```json
{
  "customer_phone": "255712345678",
  "mobile_network": "TZ-AIRTEL-C2B",
  "amount": 1000,
  "description": "Payment for services",
  "reference": "TXN_123456789",
  "date": "2025-07-20 10:30:00",
  "webhook_url": "https://your-webhook.com/callback"
}
```

### Successful Response (200)
```json
{
  "status": "PENDING_ACK",
  "message": "Transaction processed successfully",
  "transaction_id": "bEHwhTw3RxoM",
  "reference": "TXN_123456789",
  "amount": 1000,
  "currency": "TZS",
  "customer_phone": "255712345678",
  "mobile_network": "TZ-AIRTEL-C2B",
  "description": "Payment for services",
  "aggregator_status": "success",
  "processing_time": 2.5,
  "timestamp": "2025-07-20T08:35:12.038173Z",
  "webhook_sent": false,
  "aggregator_reference": "TXN_REF_123",
  "network_provider": "AIRTEL",
  "risk_level": "low",
  "commission": 50,
  "transaction_fee": 0
}
```

### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `status` | string | Transaction status (`PENDING_ACK`, `SUCCESS`, `FAILED`) |
| `message` | string | Human-readable status message |
| `transaction_id` | string | Unique ESB transaction ID |
| `reference` | string | Your provided transaction reference |
| `amount` | integer | Transaction amount |
| `currency` | string | Transaction currency (always "TZS") |
| `customer_phone` | string | Customer's phone number |
| `mobile_network` | string | Mobile network used |
| `aggregator_status` | string | Tembo API response status |
| `processing_time` | float | Request processing time in seconds |
| `timestamp` | string | ISO timestamp of the request |
| `webhook_sent` | boolean | Whether webhook was sent |
| `aggregator_reference` | string | Tembo's transaction reference |
| `network_provider` | string | Network provider name |
| `risk_level` | string | Risk assessment level |
| `commission` | integer | Commission amount |
| `transaction_fee` | integer | Transaction fee amount |

### Error Responses

#### 400 - Bad Request
```json
{
  "status": "error",
  "error_code": "VALIDATION_001",
  "message": "Invalid request data",
  "details": "One or more required fields are missing or invalid",
  "timestamp": "2025-07-20T08:35:12.038173Z",
  "request_id": "req_687caa111d738",
  "errors": {
    "customer_phone": ["The customer phone field is required."],
    "amount": ["The amount must be at least 100."]
  },
  "validation_rules": {
    "customer_phone": "required|string|regex:/^255[0-9]{9}$/",
    "amount": "required|integer|min:100|max:1000000"
  },
  "suggestions": [
    "Review the validation errors below",
    "Ensure all required fields are provided",
    "Check field formats and value ranges"
  ]
}
```

#### 401 - Unauthorized
```json
{
  "status": "error",
  "error_code": "AUTH_001",
  "message": "Invalid API credentials",
  "details": "The provided API key or secret is invalid or inactive",
  "timestamp": "2025-07-20T08:35:12.038173Z",
  "request_id": "req_687caa111d738",
  "suggestions": [
    "Verify your API key and secret are correct",
    "Ensure your client account is active",
    "Check that you are using the correct authentication headers"
  ]
}
```

#### 429 - Rate Limit Exceeded
```json
{
  "status": "error",
  "error_code": "RATE_001",
  "message": "Rate limit exceeded",
  "details": "You have exceeded the allowed number of requests for this service",
  "timestamp": "2025-07-20T08:35:12.038173Z",
  "request_id": "req_687caa111d738",
  "rate_limit_info": {
    "limit": 100,
    "window": "1 minute",
    "reset_time": "2025-07-20T08:36:12.038173Z"
  },
  "suggestions": [
    "Reduce the frequency of your requests",
    "Implement request throttling in your application",
    "Contact support if you need a higher rate limit"
  ]
}
```

---

## 2. Collection Balance

Retrieves the current collection account balance.

### Endpoint
```
POST /COLLECTION_BALANCE
```

### Request Body
No request body required (empty JSON object).

### Request Example
```json
{}
```

### Successful Response (200)
```json
{
  "status": "success",
  "message": "Transaction processed successfully",
  "transaction_id": null,
  "reference": null,
  "amount": null,
  "currency": "TZS",
  "customer_phone": null,
  "mobile_network": null,
  "description": null,
  "aggregator_status": "success",
  "processing_time": 1.2,
  "timestamp": "2025-07-20T08:35:13.284249Z",
  "webhook_sent": false,
  "aggregator_reference": null,
  "network_provider": null,
  "risk_level": "low",
  "commission": null,
  "transaction_fee": null,
  "balance": 174000
}
```

### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `status` | string | Always "success" |
| `balance` | integer | Current account balance in TZS |
| `currency` | string | Account currency (always "TZS") |
| `aggregator_status` | string | Tembo API response status |
| `processing_time` | float | Request processing time in seconds |
| `timestamp` | string | ISO timestamp of the request |

### Error Responses

Same error responses as Money Collection (400, 401, 429).

---

## 3. Collection Statement

Retrieves transaction history for a specified date range.

### Endpoint
```
POST /COLLECTION_STATEMENT
```

### Request Body

| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| `startDate` | string | ✅ | Start date (YYYY-MM-DD) | `"2025-07-13"` |
| `endDate` | string | ✅ | End date (YYYY-MM-DD) | `"2025-07-20"` |

### Request Example
```json
{
  "startDate": "2025-07-13",
  "endDate": "2025-07-20"
}
```

### Successful Response (200)
```json
[
  {
    "account_number": "9000725736",
    "transaction_type": "CR",
    "transaction_reference": "TXN_123456789",
    "description": "Payment for services",
    "transaction_date": "2025-07-20 10:30:00",
    "amount_credited": 1000,
    "amount_debited": 0,
    "balance": 174000,
    "cba_reference": null
  },
  {
    "account_number": "9000725736",
    "transaction_type": "CR",
    "transaction_reference": "TXN_987654321",
    "description": "Another payment",
    "transaction_date": "2025-07-19 15:45:30",
    "amount_credited": 2000,
    "amount_debited": 0,
    "balance": 173000,
    "cba_reference": null
  }
]
```

### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `account_number` | string | Collection account number |
| `transaction_type` | string | Transaction type (`CR` for credit, `DR` for debit) |
| `transaction_reference` | string | Transaction reference number |
| `description` | string | Transaction description |
| `transaction_date` | string | Transaction date and time |
| `amount_credited` | integer | Amount credited to account |
| `amount_debited` | integer | Amount debited from account |
| `balance` | integer | Account balance after transaction |
| `cba_reference` | string | CBA reference (if applicable) |

### Error Responses

Same error responses as Money Collection (400, 401, 429).

---

## 4. Payment Status ⚠️

**Note: This service is currently under investigation and may return errors.**

### Endpoint
```
POST /PAYMENT_STATUS
```

### Request Body

| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| `reference` | string | ✅ | Transaction reference to check | `"TXN_123456789"` |

### Request Example
```json
{
  "reference": "TXN_123456789"
}
```

### Successful Response (200) - Expected Format
```json
{
  "status": "success",
  "message": "Transaction status retrieved successfully",
  "transaction_id": "bEHwhTw3RxoM",
  "reference": "TXN_123456789",
  "amount": 1000,
  "currency": "TZS",
  "customer_phone": "255712345678",
  "mobile_network": "TZ-AIRTEL-C2B",
  "description": "Payment for services",
  "aggregator_status": "success",
  "processing_time": 1.5,
  "timestamp": "2025-07-20T08:35:12.038173Z",
  "webhook_sent": false,
  "aggregator_reference": "TXN_REF_123",
  "network_provider": "AIRTEL",
  "risk_level": "low",
  "commission": 50,
  "transaction_fee": 0
}
```

### Current Error Response (500)
```json
{
  "status": "error",
  "message": "Service temporarily unavailable",
  "transaction_id": "TXN_EHfWEjm21dMho9LL"
}
```

---

## Mobile Network Codes

| Network | Code | Description |
|---------|------|-------------|
| Airtel Tanzania | `TZ-AIRTEL-C2B` | Airtel Customer to Business |
| M-Pesa Tanzania | `TZ-MPESA-C2B` | M-Pesa Customer to Business |
| Tigo Tanzania | `TZ-TIGO-C2B` | Tigo Customer to Business |
| Halopesa Tanzania | `TZ-HALOPESA-C2B` | Halopesa Customer to Business |

---

## Rate Limits

- **Default Limit:** 100 requests per minute per service
- **Window:** 1 minute rolling window
- **Headers:** Rate limit information is included in error responses

---

## Webhook Notifications

When a `webhook_url` is provided in Money Collection requests, the system will send status updates to your webhook endpoint.

### Webhook Payload Example
```json
{
  "transaction_id": "bEHwhTw3RxoM",
  "reference": "TXN_123456789",
  "status": "SUCCESS",
  "amount": 1000,
  "currency": "TZS",
  "customer_phone": "255712345678",
  "mobile_network": "TZ-AIRTEL-C2B",
  "description": "Payment for services",
  "timestamp": "2025-07-20T08:35:12.038173Z",
  "aggregator_reference": "TXN_REF_123"
}
```

---

## Error Codes

| Code | Description |
|------|-------------|
| `AUTH_001` | Invalid API credentials |
| `RATE_001` | Rate limit exceeded |
| `SERVICE_001` | Service not available |
| `VALIDATION_001` | Invalid request data |
| `INTERNAL_001` | Internal server error |

---

## Testing

### cURL Examples

#### Money Collection
```bash
curl -X POST "http://127.0.0.1:8000/api/esb/MONEY_COLLECTION" \
  -H "X-API-Key: sample_client_key_ABC123DEF456" \
  -H "X-API-Secret: sample_client_secret_XYZ789GHI012" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_phone": "255712345678",
    "mobile_network": "TZ-AIRTEL-C2B",
    "amount": 1000,
    "description": "Test payment",
    "reference": "TEST_123",
    "date": "2025-07-20 10:30:00"
  }'
```

#### Collection Balance
```bash
curl -X POST "http://127.0.0.1:8000/api/esb/COLLECTION_BALANCE" \
  -H "X-API-Key: sample_client_key_ABC123DEF456" \
  -H "X-API-Secret: sample_client_secret_XYZ789GHI012" \
  -H "Content-Type: application/json" \
  -d '{}'
```

#### Collection Statement
```bash
curl -X POST "http://127.0.0.1:8000/api/esb/COLLECTION_STATEMENT" \
  -H "X-API-Key: sample_client_key_ABC123DEF456" \
  -H "X-API-Secret: sample_client_secret_XYZ789GHI012" \
  -H "Content-Type: application/json" \
  -d '{
    "startDate": "2025-07-13",
    "endDate": "2025-07-20"
  }'
```

---

## Support

For technical support or questions about this API:

- **Email:** support@zima-esb.com
- **Documentation:** https://docs.zima-esb.com
- **Status Page:** https://status.zima-esb.com

---

**Last Updated:** July 20, 2025  
**API Version:** 1.0.0  
**Status:** Production Ready ✅ 