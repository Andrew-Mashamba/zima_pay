# ZIMA ESB API - Quick Reference Card

## Base URL
```
http://127.0.0.1:8000/api/esb
```

## Authentication Headers
```
X-API-Key: sample_client_key_ABC123DEF456
X-API-Secret: sample_client_secret_XYZ789GHI012
Content-Type: application/json
```

---

## 🚀 Working Endpoints

### 1. Money Collection (Primary Service)
**POST** `/MONEY_COLLECTION`

**Request:**
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

**Response:**
```json
{
  "status": "PENDING_ACK",
  "transaction_id": "bEHwhTw3RxoM",
  "reference": "TXN_123456789",
  "amount": 1000,
  "currency": "TZS"
}
```

### 2. Collection Balance
**POST** `/COLLECTION_BALANCE`

**Request:**
```json
{}
```

**Response:**
```json
{
  "status": "success",
  "balance": 174000,
  "currency": "TZS"
}
```

### 3. Collection Statement
**POST** `/COLLECTION_STATEMENT`

**Request:**
```json
{
  "startDate": "2025-07-13",
  "endDate": "2025-07-20"
}
```

**Response:**
```json
[
  {
    "transaction_reference": "TXN_123456789",
    "description": "Payment for services",
    "amount_credited": 1000,
    "balance": 174000
  }
]
```

---

## ⚠️ Under Investigation

### 4. Payment Status
**POST** `/PAYMENT_STATUS`

**Request:**
```json
{
  "reference": "TXN_123456789"
}
```

**Current Response (Error):**
```json
{
  "status": "error",
  "message": "Service temporarily unavailable"
}
```

---

## Mobile Network Codes

| Network | Code |
|---------|------|
| Airtel Tanzania | `TZ-AIRTEL-C2B` |
| M-Pesa Tanzania | `TZ-MPESA-C2B` |
| Tigo Tanzania | `TZ-TIGO-C2B` |
| Halopesa Tanzania | `TZ-HALOPESA-C2B` |

---

## Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `AUTH_001` | 401 | Invalid API credentials |
| `RATE_001` | 429 | Rate limit exceeded |
| `VALIDATION_001` | 400 | Invalid request data |
| `INTERNAL_001` | 500 | Internal server error |

---

## Rate Limits
- **100 requests per minute** per service
- **1 minute rolling window**

---

## Quick Test Commands

### Money Collection
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

### Collection Balance
```bash
curl -X POST "http://127.0.0.1:8000/api/esb/COLLECTION_BALANCE" \
  -H "X-API-Key: sample_client_key_ABC123DEF456" \
  -H "X-API-Secret: sample_client_secret_XYZ789GHI012" \
  -H "Content-Type: application/json" \
  -d '{}'
```

### Collection Statement
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

**Status:** ✅ Production Ready (3/4 services working)  
**Last Updated:** July 20, 2025 