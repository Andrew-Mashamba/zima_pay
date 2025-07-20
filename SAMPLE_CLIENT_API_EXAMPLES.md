# 🏢 Sample Client API Examples - All Network Providers

## 📋 Client Information
- **Client Name**: Sample Payment Gateway
- **API Key**: `sample_client_key_ABC123DEF456`
- **API Secret**: `sample_client_secret_XYZ789GHI012`
- **Base URL**: `http://127.0.0.1:8000/api`

---

## 🔐 Common Headers (All Requests)

```json
{
  "X-API-Key": "sample_client_key_ABC123DEF456",
  "X-API-Secret": "sample_client_secret_XYZ789GHI012",
  "Content-Type": "application/json",
  "Accept": "application/json"
}
```

---

## 📱 1. AIRTEL MONEY (TZ-AIRTEL-C2B)

### **Request Body**
```json
{
  "customer_phone": "255692410353",
  "mobile_network": "TZ-AIRTEL-C2B",
  "amount": 1000,
  "description": "Payment for services",
  "reference": "SAMPLE_AIRTEL_001",
  "date": "2025-07-19 18:05:00",
  "webhook_url": "https://webhook.site/sample-client-webhook"
}
```

### **Expected Response**
```json
{
  "reference": "SAMPLE_AIRTEL_001",
  "status": "success",
  "message": "Transaction processed successfully",
  "transaction_id": "TXN_687BE3A61A35D",
  "amount": 1000,
  "currency": "TZS",
  "customer_phone": "255692410353",
  "mobile_network": "TZ-AIRTEL-C2B",
  "description": "Payment for services",
  "aggregator_status": "success",
  "processing_time": 0.5,
  "timestamp": "2025-07-19T18:27:50.107439Z",
  "webhook_sent": true,
  "aggregator_reference": "TBO_687BE3A61A4BC",
  "network_provider": "Airtel Money",
  "risk_level": "low",
  "commission": 25,
  "transaction_fee": 50
}
```

### **Alternative Amounts**
```json
{
  "customer_phone": "255692410353",
  "mobile_network": "TZ-AIRTEL-C2B",
  "amount": 25000,
  "description": "Medium payment for services",
  "reference": "SAMPLE_AIRTEL_002",
  "date": "2025-07-19 18:05:00",
  "webhook_url": "https://webhook.site/sample-client-webhook"
}
```

```json
{
  "customer_phone": "255692410353",
  "mobile_network": "TZ-AIRTEL-C2B",
  "amount": 150000,
  "description": "Large payment for services",
  "reference": "SAMPLE_AIRTEL_003",
  "date": "2025-07-19 18:05:00",
  "webhook_url": "https://webhook.site/sample-client-webhook"
}
```

---

## 📱 2. TIGO PESA (TZ-TIGO-C2B)

### **Request Body**
```json
{
  "customer_phone": "255788342299",
  "mobile_network": "TZ-TIGO-C2B",
  "amount": 1000,
  "description": "Payment for services",
  "reference": "SAMPLE_TIGO_001",
  "date": "2025-07-19 18:05:00",
  "webhook_url": "https://webhook.site/sample-client-webhook"
}
```

### **Expected Response**
```json
{
  "reference": "SAMPLE_TIGO_001",
  "status": "success",
  "message": "Transaction processed successfully",
  "transaction_id": "TXN_687BE3A61A35D",
  "amount": 1000,
  "currency": "TZS",
  "customer_phone": "255788342299",
  "mobile_network": "TZ-TIGO-C2B",
  "description": "Payment for services",
  "aggregator_status": "success",
  "processing_time": 0.5,
  "timestamp": "2025-07-19T18:27:50.107439Z",
  "webhook_sent": true,
  "aggregator_reference": "TBO_687BE3A61A4BC",
  "network_provider": "Tigo Pesa",
  "risk_level": "low",
  "commission": 25,
  "transaction_fee": 50
}
```

### **Alternative Amounts**
```json
{
  "customer_phone": "255788342299",
  "mobile_network": "TZ-TIGO-C2B",
  "amount": 5000,
  "description": "Utility bill payment",
  "reference": "SAMPLE_TIGO_002",
  "date": "2025-07-19 18:05:00",
  "webhook_url": "https://webhook.site/sample-client-webhook"
}
```

```json
{
  "customer_phone": "255788342299",
  "mobile_network": "TZ-TIGO-C2B",
  "amount": 15000,
  "description": "Monthly subscription payment",
  "reference": "SAMPLE_TIGO_003",
  "date": "2025-07-19 18:05:00",
  "webhook_url": "https://webhook.site/sample-client-webhook"
}
```

---

## 📱 3. M-PESA (TZ-MPESA-C2B)

### **Request Body**
```json
{
  "customer_phone": "255755123456",
  "mobile_network": "TZ-MPESA-C2B",
  "amount": 1000,
  "description": "Payment for services",
  "reference": "SAMPLE_MPESA_001",
  "date": "2025-07-19 18:05:00",
  "webhook_url": "https://webhook.site/sample-client-webhook"
}
```

### **Expected Response**
```json
{
  "reference": "SAMPLE_MPESA_001",
  "status": "success",
  "message": "Transaction processed successfully",
  "transaction_id": "TXN_687BE3A61A35D",
  "amount": 1000,
  "currency": "TZS",
  "customer_phone": "255755123456",
  "mobile_network": "TZ-MPESA-C2B",
  "description": "Payment for services",
  "aggregator_status": "success",
  "processing_time": 0.5,
  "timestamp": "2025-07-19T18:27:50.107439Z",
  "webhook_sent": true,
  "aggregator_reference": "TBO_687BE3A61A4BC",
  "network_provider": "M-Pesa",
  "risk_level": "low",
  "commission": 25,
  "transaction_fee": 50
}
```

### **Alternative Amounts**
```json
{
  "customer_phone": "255755123456",
  "mobile_network": "TZ-MPESA-C2B",
  "amount": 25000,
  "description": "Medium payment for services",
  "reference": "SAMPLE_MPESA_002",
  "date": "2025-07-19 18:05:00",
  "webhook_url": "https://webhook.site/sample-client-webhook"
}
```

```json
{
  "customer_phone": "255755123456",
  "mobile_network": "TZ-MPESA-C2B",
  "amount": 150000,
  "description": "Large payment for services",
  "reference": "SAMPLE_MPESA_003",
  "date": "2025-07-19 18:05:00",
  "webhook_url": "https://webhook.site/sample-client-webhook"
}
```

---

## 📱 4. HALOPESA (TZ-HALOPESA-C2B)

### **Request Body**
```json
{
  "customer_phone": "255623456789",
  "mobile_network": "TZ-HALOPESA-C2B",
  "amount": 1000,
  "description": "Payment for services",
  "reference": "SAMPLE_HALO_001",
  "date": "2025-07-19 18:05:00",
  "webhook_url": "https://webhook.site/sample-client-webhook"
}
```

### **Expected Response**
```json
{
  "reference": "SAMPLE_HALO_001",
  "status": "success",
  "message": "Transaction processed successfully",
  "transaction_id": "TXN_687BE3A61A35D",
  "amount": 1000,
  "currency": "TZS",
  "customer_phone": "255623456789",
  "mobile_network": "TZ-HALOPESA-C2B",
  "description": "Payment for services",
  "aggregator_status": "success",
  "processing_time": 0.5,
  "timestamp": "2025-07-19T18:27:50.107439Z",
  "webhook_sent": true,
  "aggregator_reference": "TBO_687BE3A61A4BC",
  "network_provider": "HaloPesa",
  "risk_level": "low",
  "commission": 25,
  "transaction_fee": 50
}
```

### **Alternative Amounts**
```json
{
  "customer_phone": "255623456789",
  "mobile_network": "TZ-HALOPESA-C2B",
  "amount": 5000,
  "description": "Utility bill payment",
  "reference": "SAMPLE_HALO_002",
  "date": "2025-07-19 18:05:00",
  "webhook_url": "https://webhook.site/sample-client-webhook"
}
```

```json
{
  "customer_phone": "255623456789",
  "mobile_network": "TZ-HALOPESA-C2B",
  "amount": 15000,
  "description": "Monthly subscription payment",
  "reference": "SAMPLE_HALO_003",
  "date": "2025-07-19 18:05:00",
  "webhook_url": "https://webhook.site/sample-client-webhook"
}
```

---

## 🔄 Transaction Status Check

### **Headers**
```json
{
  "X-API-Key": "sample_client_key_ABC123DEF456",
  "X-API-Secret": "sample_client_secret_XYZ789GHI012",
  "Accept": "application/json"
}
```

### **Request URL**
```
GET http://127.0.0.1:8000/api/esb/transaction/{transaction_id}
```

### **Expected Response**
```json
{
  "transaction": {
    "transaction_id": "TXN_1234567890ABCDEF",
    "client_reference": "SAMPLE_AIRTEL_001",
    "amount": 1000,
    "currency": "TZS",
    "customer_phone": "255692410353",
    "mobile_network": "TZ-AIRTEL-C2B",
    "description": "Payment for services",
    "status": "completed",
    "aggregator_status": "success",
    "client_status": "success",
    "webhook_sent": true,
    "created_at": "2025-07-19T18:05:00.000000Z",
    "updated_at": "2025-07-19T18:05:30.000000Z"
  }
}
```

---

## ❌ Error Response Examples

### **Invalid API Credentials (401)**
```json
{
  "status": "error",
  "error_code": "AUTH_001",
  "message": "Invalid API credentials",
  "details": "The provided API key or secret is invalid or inactive",
  "timestamp": "2025-07-19T18:34:59.621766Z",
  "request_id": "req_687be55397cf8",
  "suggestions": [
    "Verify your API key and secret are correct",
    "Ensure your client account is active",
    "Check that you are using the correct authentication headers"
  ]
}
```

### **Invalid Request Data (400)**
```json
{
  "status": "error",
  "error_code": "VALIDATION_001",
  "message": "Invalid request data",
  "details": "One or more required fields are missing or invalid",
  "timestamp": "2025-07-19T18:35:09.523958Z",
  "request_id": "req_687be55d7fee4",
  "errors": {
    "amount": ["The amount field must be at least 100."]
  },
  "validation_rules": {
    "customer_phone": {
      "required": true,
      "format": "255XXXXXXXXX (9 digits after 255)",
      "example": "255692410353"
    },
    "mobile_network": {
      "required": true,
      "allowed_values": ["TZ-AIRTEL-C2B", "TZ-TIGO-C2B", "TZ-MPESA-C2B", "TZ-HALOPESA-C2B"]
    },
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
    },
    "reference": {
      "required": true,
      "type": "string",
      "max_length": 100
    },
    "date": {
      "required": true,
      "format": "Y-m-d H:i:s",
      "example": "2025-07-19 18:05:00"
    },
    "webhook_url": {
      "required": true,
      "type": "URL",
      "example": "https://webhook.site/your-webhook"
    }
  },
  "suggestions": [
    "Review the validation errors below",
    "Ensure all required fields are provided",
    "Check field formats and value ranges"
  ]
}
```

### **Rate Limit Exceeded (429)**
```json
{
  "status": "error",
  "error_code": "RATE_001",
  "message": "Rate limit exceeded",
  "details": "You have exceeded the allowed number of requests for this service",
  "timestamp": "2025-07-19T18:35:20.123456Z",
  "request_id": "req_687be55d8abc1",
  "rate_limit_info": {
    "limit": 100,
    "window": "1 minute",
    "reset_time": "2025-07-19T18:36:20.123456Z"
  },
  "suggestions": [
    "Reduce the frequency of your requests",
    "Implement request throttling in your application",
    "Contact support if you need a higher rate limit"
  ]
}
```

### **Service Not Available (404)**
```json
{
  "status": "error",
  "error_code": "SERVICE_001",
  "message": "Service not available",
  "details": "The requested service is not available for your client account",
  "timestamp": "2025-07-19T18:35:30.654321Z",
  "request_id": "req_687be55d9def2",
  "service_info": {
    "requested_service": "MONEY_COLLECTION",
    "available_services": ["MONEY_COLLECTION"]
  },
  "suggestions": [
    "Verify the service code is correct",
    "Contact support to enable this service for your account",
    "Check your service mapping configuration"
  ]
}
```

### **Internal Server Error (500)**
```json
{
  "status": "error",
  "error_code": "INTERNAL_001",
  "message": "Internal server error",
  "details": "An unexpected error occurred while processing your request",
  "timestamp": "2025-07-19T18:35:40.987654Z",
  "request_id": "req_687be55da1234",
  "suggestions": [
    "Try your request again in a few moments",
    "If the problem persists, contact support with the request ID",
    "Check our service status page for any ongoing issues"
  ]
}
```

---

## 📞 Webhook Notification Example

### **Webhook URL**: `https://webhook.site/sample-client-webhook`

### **Webhook Payload**
```json
{
  "transaction_id": "TXN_1234567890ABCDEF",
  "reference": "SAMPLE_AIRTEL_001",
  "status": "completed",
  "amount": 1000,
  "currency": "TZS",
  "customer_phone": "255692410353",
  "mobile_network": "TZ-AIRTEL-C2B",
  "description": "Payment for services",
  "timestamp": "2025-07-19T18:05:30.000000Z",
  "aggregator_reference": "TBO_REF_123456",
  "webhook_sent": true
}
```

---

## 🧪 Testing Commands (cURL)

### **Airtel Money Test**
```bash
curl -X POST http://127.0.0.1:8000/api/esb/MONEY_COLLECTION \
  -H "X-API-Key: sample_client_key_ABC123DEF456" \
  -H "X-API-Secret: sample_client_secret_XYZ789GHI012" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_phone": "255692410353",
    "mobile_network": "TZ-AIRTEL-C2B",
    "amount": 1000,
    "description": "Payment for services",
    "reference": "SAMPLE_AIRTEL_001",
    "date": "2025-07-19 18:05:00",
    "webhook_url": "https://webhook.site/sample-client-webhook"
  }'
```

### **Tigo Pesa Test**
```bash
curl -X POST http://127.0.0.1:8000/api/esb/MONEY_COLLECTION \
  -H "X-API-Key: sample_client_key_ABC123DEF456" \
  -H "X-API-Secret: sample_client_secret_XYZ789GHI012" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_phone": "255788342299",
    "mobile_network": "TZ-TIGO-C2B",
    "amount": 1000,
    "description": "Payment for services",
    "reference": "SAMPLE_TIGO_001",
    "date": "2025-07-19 18:05:00",
    "webhook_url": "https://webhook.site/sample-client-webhook"
  }'
```

### **M-Pesa Test**
```bash
curl -X POST http://127.0.0.1:8000/api/esb/MONEY_COLLECTION \
  -H "X-API-Key: sample_client_key_ABC123DEF456" \
  -H "X-API-Secret: sample_client_secret_XYZ789GHI012" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_phone": "255755123456",
    "mobile_network": "TZ-MPESA-C2B",
    "amount": 1000,
    "description": "Payment for services",
    "reference": "SAMPLE_MPESA_001",
    "date": "2025-07-19 18:05:00",
    "webhook_url": "https://webhook.site/sample-client-webhook"
  }'
```

### **HaloPesa Test**
```bash
curl -X POST http://127.0.0.1:8000/api/esb/MONEY_COLLECTION \
  -H "X-API-Key: sample_client_key_ABC123DEF456" \
  -H "X-API-Secret: sample_client_secret_XYZ789GHI012" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_phone": "255623456789",
    "mobile_network": "TZ-HALOPESA-C2B",
    "amount": 1000,
    "description": "Payment for services",
    "reference": "SAMPLE_HALO_001",
    "date": "2025-07-19 18:05:00",
    "webhook_url": "https://webhook.site/sample-client-webhook"
  }'
```

---

## 📋 Field Descriptions

### **Request Fields**
| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| `customer_phone` | string | ✅ | Customer phone number (255 format) | "255692410353" |
| `mobile_network` | string | ✅ | Mobile network code | "TZ-AIRTEL-C2B" |
| `amount` | integer | ✅ | Transaction amount (TZS) | 1000 |
| `description` | string | ✅ | Transaction description | "Payment for services" |
| `reference` | string | ✅ | Client reference number | "SAMPLE_AIRTEL_001" |
| `date` | string | ✅ | Transaction date (Y-m-d H:i:s) | "2025-07-19 18:05:00" |
| `webhook_url` | string | ✅ | Webhook notification URL | "https://webhook.site/webhook" |

### **Success Response Fields**
| Field | Type | Description | Example |
|-------|------|-------------|---------|
| `reference` | string | Client reference number | "SAMPLE_AIRTEL_001" |
| `status` | string | Transaction status | "success" |
| `message` | string | Status message | "Transaction processed successfully" |
| `transaction_id` | string | ZIMA ESB transaction ID | "TXN_687BE3A61A35D" |
| `amount` | integer | Transaction amount | 1000 |
| `currency` | string | Transaction currency | "TZS" |
| `customer_phone` | string | Customer phone number | "255692410353" |
| `mobile_network` | string | Mobile network code | "TZ-AIRTEL-C2B" |
| `description` | string | Transaction description | "Payment for services" |
| `aggregator_status` | string | Aggregator response status | "success" |
| `processing_time` | float | Processing time in seconds | 0.5 |
| `timestamp` | string | Transaction timestamp | "2025-07-19T18:27:50.107439Z" |
| `webhook_sent` | boolean | Webhook notification sent | true |
| `aggregator_reference` | string | Aggregator reference ID | "TBO_687BE3A61A4BC" |
| `network_provider` | string | Human-readable network name | "Airtel Money" |
| `risk_level` | string | Transaction risk assessment | "low" |
| `commission` | float | Commission amount (TZS) | 25 |
| `transaction_fee` | integer | Transaction fee (TZS) | 50 |

### **Error Response Fields**
| Field | Type | Description | Example |
|-------|------|-------------|---------|
| `status` | string | Response status | "error" |
| `error_code` | string | Unique error code | "AUTH_001" |
| `message` | string | Error message | "Invalid API credentials" |
| `details` | string | Detailed error description | "The provided API key or secret is invalid" |
| `timestamp` | string | Error timestamp | "2025-07-19T18:34:59.621766Z" |
| `request_id` | string | Unique request identifier | "req_687be55397cf8" |
| `errors` | object | Validation errors (400 only) | `{"amount": ["The amount field must be at least 100."]}` |
| `validation_rules` | object | Field validation rules (400 only) | See validation example above |
| `rate_limit_info` | object | Rate limit details (429 only) | `{"limit": 100, "window": "1 minute"}` |
| `service_info` | object | Service information (404 only) | `{"requested_service": "MONEY_COLLECTION"}` |
| `suggestions` | array | Helpful suggestions | `["Verify your API key", "Contact support"]` |

---

## 🎯 Network Codes Reference

| Network | Code | Phone Prefix | Test Number |
|---------|------|--------------|-------------|
| **Airtel Money** | `TZ-AIRTEL-C2B` | 25569 | 0692410353 |
| **Tigo Pesa** | `TZ-TIGO-C2B` | 25578 | 0788342299 |
| **M-Pesa** | `TZ-MPESA-C2B` | 25575 | 0755123456 |
| **HaloPesa** | `TZ-HALOPESA-C2B` | 25562 | 0623456789 |

---

*Ready for testing in Postman or any API client!* 🚀 