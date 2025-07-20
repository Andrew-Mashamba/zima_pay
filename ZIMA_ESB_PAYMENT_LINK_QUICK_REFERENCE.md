# ZIMA ESB Payment Link Service - Quick Reference Card

## Base URL
```
http://127.0.0.1:8000/api/payment-links
```

## Authentication Headers
```
X-API-Key: sample_client_key_ABC123DEF456
X-API-Secret: sample_client_secret_XYZ789GHI012
Content-Type: application/json
```

---

## 🚀 Core Endpoints

### 1. Generate Payment Link
**POST** `/generate`

**Request:**
```json
{
  "amount": 5000.00,
  "description": "Payment for online services",
  "reference": "ORDER_123456",
  "currency": "TZS",
  "expires_at": "2025-07-27T10:00:00Z",
  "max_uses": 5,
  "is_reusable": false,
  "webhook_url": "https://your-webhook.com/callback"
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "link_id": "LINK_VvHIoVruwPUrEKSt",
    "short_code": "xw735E5k",
    "payment_url": "http://127.0.0.1:8000/pay/xw735E5k",
    "amount": 5000.00,
    "currency": "TZS"
  }
}
```

### 2. List Payment Links
**GET** `/`

**Query Parameters:**
- `status` - Filter by status (active, expired, cancelled, completed)
- `date_from` - Filter from date (YYYY-MM-DD)
- `date_to` - Filter to date (YYYY-MM-DD)
- `per_page` - Items per page (1-100)

### 3. Get Payment Link Stats
**GET** `/{linkId}/stats`

**Response:**
```json
{
  "status": "success",
  "data": {
    "link_id": "LINK_VvHIoVruwPUrEKSt",
    "views_count": 15,
    "current_uses": 3,
    "max_uses": 5,
    "total_collected": "15000.00",
    "conversion_rate": 20.0
  }
}
```

### 4. Cancel Payment Link
**DELETE** `/{linkId}`

---

## 📱 Public Payment Pages

### Payment Page
```
GET /pay/{shortCode}
```

### Payment Processing
```
POST /pay/{shortCode}/process
```

### Success/Failure/Cancelled Pages
```
GET /pay/{shortCode}/success
GET /pay/{shortCode}/failure
GET /pay/{shortCode}/cancelled
```

---

## 💡 Use Case Examples

### E-commerce Payment
```json
{
  "amount": 15000.00,
  "description": "Payment for Order #12345",
  "reference": "ORDER_12345",
  "expires_at": "2025-07-22T10:00:00Z",
  "max_uses": 1,
  "is_reusable": false
}
```

### Donation Campaign
```json
{
  "amount": 10000.00,
  "description": "Charity Donation",
  "allow_partial_payment": true,
  "minimum_amount": 1000.00,
  "maximum_amount": 50000.00,
  "expires_at": "2025-08-20T10:00:00Z",
  "max_uses": 100,
  "is_reusable": true
}
```

### Event Ticket
```json
{
  "amount": 5000.00,
  "description": "Tech Conference 2025 Ticket",
  "expires_at": "2025-07-21T10:00:00Z",
  "max_uses": 1,
  "is_reusable": false
}
```

---

## 🔧 Advanced Features

### Partial Payments
```json
{
  "allow_partial_payment": true,
  "minimum_amount": 1000.00,
  "maximum_amount": 20000.00
}
```

### Network Restrictions
```json
{
  "allowed_networks": ["TZ-AIRTEL-C2B", "TZ-MPESA-C2B"]
}
```

### Custom Redirects
```json
{
  "success_url": "https://your-site.com/success",
  "failure_url": "https://your-site.com/failure",
  "cancel_url": "https://your-site.com/cancel"
}
```

### Metadata
```json
{
  "metadata": {
    "campaign": "Summer Sale",
    "category": "ecommerce",
    "customer_id": "12345"
  }
}
```

---

## 📊 Payment Link Statuses

| Status | Description |
|--------|-------------|
| `active` | Link is active and can be used |
| `expired` | Link has expired |
| `cancelled` | Link has been cancelled |
| `completed` | Link has reached maximum uses |

---

## 🔗 Mobile Network Codes

| Network | Code |
|---------|------|
| Airtel Tanzania | `TZ-AIRTEL-C2B` |
| M-Pesa Tanzania | `TZ-MPESA-C2B` |
| Tigo Tanzania | `TZ-TIGO-C2B` |
| Halopesa Tanzania | `TZ-HALOPESA-C2B` |

---

## ⚡ Quick Test Commands

### Generate Payment Link
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

### List Payment Links
```bash
curl -X GET "http://127.0.0.1:8000/api/payment-links?status=active&per_page=10" \
  -H "X-API-Key: sample_client_key_ABC123DEF456" \
  -H "X-API-Secret: sample_client_secret_XYZ789GHI012"
```

### Get Payment Link Stats
```bash
curl -X GET "http://127.0.0.1:8000/api/payment-links/LINK_VvHIoVruwPUrEKSt/stats" \
  -H "X-API-Key: sample_client_key_ABC123DEF456" \
  -H "X-API-Secret: sample_client_secret_XYZ789GHI012"
```

### Cancel Payment Link
```bash
curl -X DELETE "http://127.0.0.1:8000/api/payment-links/LINK_VvHIoVruwPUrEKSt" \
  -H "X-API-Key: sample_client_key_ABC123DEF456" \
  -H "X-API-Secret: sample_client_secret_XYZ789GHI012"
```

---

## 🎯 Key Benefits

✅ **Easy Integration** - Simple REST API  
✅ **Flexible Configuration** - Partial payments, expiry, usage limits  
✅ **Multiple Networks** - Support for all Tanzanian mobile money networks  
✅ **Analytics** - View counts, conversion rates, usage statistics  
✅ **Webhook Support** - Real-time payment notifications  
✅ **Public Pages** - Ready-to-use payment pages for customers  
✅ **QR Code Ready** - Generate QR codes for easy sharing  

---

**Status:** ✅ Production Ready  
**Last Updated:** July 20, 2025  
**API Version:** 1.0.0 