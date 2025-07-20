# ZIMA ESB - Payment Link Generation API Documentation

## Overview

The ZIMA ESB Payment Link Generation service allows clients to create shareable payment links that customers can use to pay for goods and services. This service integrates seamlessly with the existing ESB infrastructure and supports all mobile money networks.

**Base URL:** `http://127.0.0.1:8000/api/payment-links`

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

---

## 1. Generate Payment Link

Creates a new payment link that customers can use to make payments.

### Endpoint
```
POST /generate
```

### Request Body

| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| `amount` | decimal | ✅ | Payment amount in TZS | `5000.00` |
| `description` | string | ✅ | Payment description | `"Payment for online services"` |
| `reference` | string | ❌ | Client's reference (auto-generated if not provided) | `"ORDER_123456"` |
| `currency` | string | ❌ | Currency code (default: TZS) | `"TZS"` |
| `customer_phone` | string | ❌ | Customer's phone number | `"255712345678"` |
| `customer_name` | string | ❌ | Customer's name | `"John Doe"` |
| `customer_email` | string | ❌ | Customer's email | `"john@example.com"` |
| `allowed_networks` | array | ❌ | Allowed mobile networks | `["TZ-AIRTEL-C2B", "TZ-MPESA-C2B"]` |
| `allow_partial_payment` | boolean | ❌ | Allow partial payments | `true` |
| `minimum_amount` | decimal | ❌ | Minimum payment amount | `1000.00` |
| `maximum_amount` | decimal | ❌ | Maximum payment amount | `20000.00` |
| `expires_at` | string | ❌ | Expiry date (ISO 8601) | `"2025-07-27T10:00:00Z"` |
| `max_uses` | integer | ❌ | Maximum number of uses | `5` |
| `is_reusable` | boolean | ❌ | Can be used multiple times | `false` |
| `is_public` | boolean | ❌ | Publicly accessible | `true` |
| `webhook_url` | string | ❌ | Webhook URL for notifications | `"https://your-webhook.com/callback"` |
| `success_url` | string | ❌ | Success redirect URL | `"https://your-site.com/success"` |
| `failure_url` | string | ❌ | Failure redirect URL | `"https://your-site.com/failure"` |
| `cancel_url` | string | ❌ | Cancel redirect URL | `"https://your-site.com/cancel"` |
| `metadata` | object | ❌ | Additional metadata | `{"campaign": "Summer Sale"}` |

### Request Example

#### Basic Payment Link
```json
{
  "amount": 5000.00,
  "description": "Payment for online services",
  "reference": "ORDER_123456",
  "currency": "TZS",
  "customer_name": "John Doe",
  "customer_email": "john@example.com",
  "expires_at": "2025-07-27T10:00:00Z",
  "max_uses": 5,
  "is_reusable": false,
  "webhook_url": "https://your-webhook.com/callback"
}
```

#### Partial Payment Link
```json
{
  "amount": 10000.00,
  "description": "Donation for charity",
  "reference": "DONATION_789",
  "currency": "TZS",
  "allow_partial_payment": true,
  "minimum_amount": 1000.00,
  "maximum_amount": 20000.00,
  "expires_at": "2025-08-20T10:00:00Z",
  "max_uses": 10,
  "is_reusable": true,
  "allowed_networks": ["TZ-AIRTEL-C2B", "TZ-MPESA-C2B"],
  "metadata": {
    "campaign": "Charity Drive 2025",
    "category": "donation"
  }
}
```

#### Expiring Payment Link
```json
{
  "amount": 2500.00,
  "description": "Event ticket payment",
  "reference": "TICKET_456",
  "currency": "TZS",
  "expires_at": "2025-07-21T10:00:00Z",
  "max_uses": 1,
  "is_reusable": false,
  "allowed_networks": ["TZ-TIGO-C2B", "TZ-HALOPESA-C2B"],
  "metadata": {
    "event": "Tech Conference 2025",
    "ticket_type": "standard"
  }
}
```

### Successful Response (201)
```json
{
  "status": "success",
  "message": "Payment link generated successfully",
  "data": {
    "link_id": "LINK_VvHIoVruwPUrEKSt",
    "short_code": "xw735E5k",
    "payment_url": "http://127.0.0.1:8000/pay/xw735E5k",
    "qr_code_data": "http://127.0.0.1:8000/pay/xw735E5k",
    "amount": 5000.00,
    "currency": "TZS",
    "description": "Payment for online services",
    "expires_at": "2025-07-27T10:00:00.000000Z",
    "max_uses": 5,
    "is_reusable": false,
    "allowed_networks": [
      "TZ-AIRTEL-C2B",
      "TZ-TIGO-C2B",
      "TZ-MPESA-C2B",
      "TZ-HALOPESA-C2B"
    ],
    "created_at": "2025-07-20T09:09:17.000000Z"
  },
  "timestamp": "2025-07-20T09:09:17.000000Z",
  "request_id": "req_687caa111d738"
}
```

### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `link_id` | string | Unique payment link identifier |
| `short_code` | string | Short code for the payment URL |
| `payment_url` | string | Full payment URL for customers |
| `qr_code_data` | string | QR code data for the payment URL |
| `amount` | decimal | Payment amount |
| `currency` | string | Payment currency |
| `description` | string | Payment description |
| `expires_at` | string | Expiry date (ISO 8601) |
| `max_uses` | integer | Maximum number of uses |
| `is_reusable` | boolean | Whether link can be reused |
| `allowed_networks` | array | Allowed mobile networks |
| `created_at` | string | Creation timestamp |

### Error Responses

#### 400 - Bad Request
```json
{
  "status": "error",
  "error_code": "VALIDATION_001",
  "message": "Invalid request data",
  "details": "One or more required fields are missing or invalid",
  "timestamp": "2025-07-20T09:09:17.000000Z",
  "request_id": "req_687caa111d738",
  "errors": {
    "amount": ["The amount must be at least 100."],
    "description": ["The description field is required."]
  },
  "validation_rules": {
    "amount": {
      "required": true,
      "type": "numeric",
      "min": 100,
      "max": 1000000,
      "currency": "TZS"
    },
    "description": {
      "required": true,
      "type": "string",
      "max_length": 255
    }
  },
  "suggestions": [
    "Review the validation errors below",
    "Ensure all required fields are provided",
    "Check field formats and value ranges"
  ]
}
```

---

## 2. List Payment Links

Retrieves a paginated list of payment links for the authenticated client.

### Endpoint
```
GET /
```

### Query Parameters

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `status` | string | ❌ | Filter by status | `"active"` |
| `date_from` | string | ❌ | Filter from date (YYYY-MM-DD) | `"2025-07-01"` |
| `date_to` | string | ❌ | Filter to date (YYYY-MM-DD) | `"2025-07-20"` |
| `per_page` | integer | ❌ | Items per page (1-100) | `20` |

### Request Example
```bash
curl -X GET "http://127.0.0.1:8000/api/payment-links?status=active&per_page=10" \
  -H "X-API-Key: your_api_key_here" \
  -H "X-API-Secret: your_api_secret_here"
```

### Successful Response (200)
```json
{
  "status": "success",
  "message": "Payment links retrieved successfully",
  "data": [
    {
      "id": 1,
      "link_id": "LINK_VvHIoVruwPUrEKSt",
      "short_code": "xw735E5k",
      "client_reference": "ORDER_123456",
      "amount": "5000.00",
      "currency": "TZS",
      "description": "Payment for online services",
      "status": "active",
      "views_count": 5,
      "current_uses": 2,
      "max_uses": 5,
      "created_at": "2025-07-20T09:09:17.000000Z",
      "expires_at": "2025-07-27T10:00:00.000000Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 10,
    "total": 25
  },
  "timestamp": "2025-07-20T09:09:17.000000Z",
  "request_id": "req_687caa111d738"
}
```

---

## 3. Get Payment Link Statistics

Retrieves detailed statistics for a specific payment link.

### Endpoint
```
GET /{linkId}/stats
```

### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `linkId` | string | ✅ | Payment link ID |

### Request Example
```bash
curl -X GET "http://127.0.0.1:8000/api/payment-links/LINK_VvHIoVruwPUrEKSt/stats" \
  -H "X-API-Key: your_api_key_here" \
  -H "X-API-Secret: your_api_secret_here"
```

### Successful Response (200)
```json
{
  "status": "success",
  "message": "Payment link statistics retrieved successfully",
  "data": {
    "link_id": "LINK_VvHIoVruwPUrEKSt",
    "short_code": "xw735E5k",
    "status": "active",
    "amount": "5000.00",
    "currency": "TZS",
    "views_count": 15,
    "current_uses": 3,
    "max_uses": 5,
    "total_collected": "15000.00",
    "successful_transactions_count": 3,
    "conversion_rate": 20.0,
    "created_at": "2025-07-20T09:09:17.000000Z",
    "expires_at": "2025-07-27T10:00:00.000000Z",
    "last_viewed_at": "2025-07-20T14:30:00.000000Z"
  },
  "timestamp": "2025-07-20T09:09:17.000000Z",
  "request_id": "req_687caa111d738"
}
```

### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `link_id` | string | Payment link identifier |
| `short_code` | string | Short code |
| `status` | string | Link status |
| `amount` | decimal | Payment amount |
| `currency` | string | Payment currency |
| `views_count` | integer | Number of times viewed |
| `current_uses` | integer | Current number of uses |
| `max_uses` | integer | Maximum allowed uses |
| `total_collected` | decimal | Total amount collected |
| `successful_transactions_count` | integer | Number of successful transactions |
| `conversion_rate` | decimal | Conversion rate (views to payments) |
| `created_at` | string | Creation timestamp |
| `expires_at` | string | Expiry timestamp |
| `last_viewed_at` | string | Last viewed timestamp |

---

## 4. Cancel Payment Link

Cancels an active payment link.

### Endpoint
```
DELETE /{linkId}
```

### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `linkId` | string | ✅ | Payment link ID |

### Request Example
```bash
curl -X DELETE "http://127.0.0.1:8000/api/payment-links/LINK_VvHIoVruwPUrEKSt" \
  -H "X-API-Key: your_api_key_here" \
  -H "X-API-Secret: your_api_secret_here"
```

### Successful Response (200)
```json
{
  "status": "success",
  "message": "Payment link cancelled successfully",
  "timestamp": "2025-07-20T09:09:17.000000Z",
  "request_id": "req_687caa111d738"
}
```

---

## Public Payment Pages

### Payment Page URL
```
GET /pay/{shortCode}
```

### Payment Processing
```
POST /pay/{shortCode}/process
```

### Success Page
```
GET /pay/{shortCode}/success
```

### Failure Page
```
GET /pay/{shortCode}/failure
```

### Cancelled Page
```
GET /pay/{shortCode}/cancelled
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

## Payment Link Statuses

| Status | Description |
|--------|-------------|
| `active` | Link is active and can be used |
| `expired` | Link has expired |
| `cancelled` | Link has been cancelled |
| `completed` | Link has reached maximum uses |

---

## Use Cases

### 1. E-commerce Payments
```json
{
  "amount": 15000.00,
  "description": "Payment for Order #12345",
  "reference": "ORDER_12345",
  "expires_at": "2025-07-22T10:00:00Z",
  "max_uses": 1,
  "is_reusable": false,
  "webhook_url": "https://your-store.com/webhook/payment"
}
```

### 2. Event Ticket Sales
```json
{
  "amount": 5000.00,
  "description": "Tech Conference 2025 Ticket",
  "reference": "TICKET_456",
  "expires_at": "2025-07-21T10:00:00Z",
  "max_uses": 1,
  "is_reusable": false,
  "metadata": {
    "event": "Tech Conference 2025",
    "ticket_type": "standard"
  }
}
```

### 3. Donation Campaigns
```json
{
  "amount": 10000.00,
  "description": "Charity Donation",
  "reference": "DONATION_789",
  "allow_partial_payment": true,
  "minimum_amount": 1000.00,
  "maximum_amount": 50000.00,
  "expires_at": "2025-08-20T10:00:00Z",
  "max_uses": 100,
  "is_reusable": true,
  "metadata": {
    "campaign": "Charity Drive 2025",
    "category": "donation"
  }
}
```

### 4. Subscription Payments
```json
{
  "amount": 25000.00,
  "description": "Monthly Subscription",
  "reference": "SUB_001",
  "expires_at": "2025-07-25T10:00:00Z",
  "max_uses": 1,
  "is_reusable": false,
  "webhook_url": "https://your-app.com/webhook/subscription"
}
```

---

## Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `AUTH_001` | 401 | Invalid API credentials |
| `VALIDATION_001` | 400 | Invalid request data |
| `PAYMENT_LINK_001` | 400 | Payment link generation failed |
| `PAYMENT_LINK_002` | 404 | Payment link not found |
| `PAYMENT_LINK_003` | 404 | Payment link cancellation failed |
| `INTERNAL_001` | 500 | Internal server error |

---

## Rate Limits

- **Default Limit:** 100 requests per minute per client
- **Window:** 1 minute rolling window
- **Headers:** Rate limit information is included in error responses

---

## Webhook Notifications

When a payment is processed through a payment link, webhook notifications are sent to the configured webhook URL.

### Webhook Payload Example
```json
{
  "event": "payment.processed",
  "payment_link_id": "LINK_VvHIoVruwPUrEKSt",
  "payment_link_short_code": "xw735E5k",
  "transaction_id": "TXN_abc123def456",
  "reference": "ORDER_123456",
  "amount": 5000.00,
  "currency": "TZS",
  "customer_phone": "255712345678",
  "mobile_network": "TZ-AIRTEL-C2B",
  "status": "SUCCESS",
  "timestamp": "2025-07-20T09:09:17.000000Z"
}
```

---

## Testing

### cURL Examples

#### Generate Payment Link
```bash
curl -X POST "http://127.0.0.1:8000/api/payment-links/generate" \
  -H "X-API-Key: sample_client_key_ABC123DEF456" \
  -H "X-API-Secret: sample_client_secret_XYZ789GHI012" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 5000.00,
    "description": "Test payment",
    "reference": "TEST_123",
    "expires_at": "2025-07-27T10:00:00Z",
    "max_uses": 5
  }'
```

#### List Payment Links
```bash
curl -X GET "http://127.0.0.1:8000/api/payment-links?status=active&per_page=10" \
  -H "X-API-Key: sample_client_key_ABC123DEF456" \
  -H "X-API-Secret: sample_client_secret_XYZ789GHI012"
```

#### Get Payment Link Stats
```bash
curl -X GET "http://127.0.0.1:8000/api/payment-links/LINK_VvHIoVruwPUrEKSt/stats" \
  -H "X-API-Key: sample_client_key_ABC123DEF456" \
  -H "X-API-Secret: sample_client_secret_XYZ789GHI012"
```

#### Cancel Payment Link
```bash
curl -X DELETE "http://127.0.0.1:8000/api/payment-links/LINK_VvHIoVruwPUrEKSt" \
  -H "X-API-Key: sample_client_key_ABC123DEF456" \
  -H "X-API-Secret: sample_client_secret_XYZ789GHI012"
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