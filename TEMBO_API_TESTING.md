# 🚀 Tembo Money Collection Service - API Testing Guide

## 📋 Overview

This guide explains how to test the Tembo Money Collection Service through the ZIMA ESB system. The service allows you to collect money from mobile subscribers through USSD push requests.

## 🏗️ System Setup

### Registered Components

1. **Aggregator**: Tembo Plus
   - **Code**: `TEMBO`
   - **Endpoint**: `https://sandbox.temboplus.com/tembo/v1` (Live Sandbox Environment)
   - **API Key**: `bf71ba501b37d989db6224fd`
   - **API Secret**: `vd//lqSw67Nl08e7Y2YzWcs2EL+rAIImpl4U4uNHdQg=`

2. **Services**: 
   - **Money Collection** - `MONEY_COLLECTION` - `/collection` - `POST`
   - **Collection Balance** - `COLLECTION_BALANCE` - `/wallet/collection-balance` - `POST`
   - **Collection Statement** - `COLLECTION_STATEMENT` - `/wallet/collection-statement` - `POST`
   - **Payment Status** - `PAYMENT_STATUS` - `/collection/status` - `POST`

3. **Test Client**: Test Bank
   - **API Key**: `test_bank_key_8CGbIjJCMRWLjFll`
   - **API Secret**: `test_bank_secret_GtlfgLnQYUpcTwOry9LV1xvRcCVFO1Ci`

## 🔗 API Endpoints

### Base URL
```
http://127.0.0.1:8000/api/esb
```

### Available Endpoints

1. **Health Check**
   ```
   GET /api/esb/health
   ```

2. **Get Services**
   ```
   GET /api/esb/services
   Headers: X-API-Key, X-API-Secret
   ```

3. **Money Collection**
   ```
   POST /api/esb/MONEY_COLLECTION
   Headers: X-API-Key, X-API-Secret, Content-Type: application/json
   ```

4. **Collection Balance**
   ```
   POST /api/esb/COLLECTION_BALANCE
   Headers: X-API-Key, X-API-Secret, Content-Type: application/json
   ```

5. **Collection Statement**
   ```
   POST /api/esb/COLLECTION_STATEMENT
   Headers: X-API-Key, X-API-Secret, Content-Type: application/json
   ```

6. **Payment Status**
   ```
   POST /api/esb/PAYMENT_STATUS
   Headers: X-API-Key, X-API-Secret, Content-Type: application/json
   ```

## ⚠️ Important Notes

- **Live Integration**: This system connects to the actual Tembo Plus API endpoints
- **Sandbox Environment**: Currently using Tembo's sandbox environment for testing
- **Real Transactions**: All requests are sent to Tembo's live API (sandbox mode)
- **Production Ready**: Can be switched to production by updating the endpoint URL

## 🧪 Testing Examples

### 1. Health Check

```bash
curl -X GET http://127.0.0.1:8000/api/esb/health
```

**Expected Response:**
```json
{
  "status": "healthy",
  "timestamp": "2025-07-19T16:57:53.000000Z",
  "version": "1.0.0"
}
```

### 2. Get Available Services

```bash
curl -X GET http://127.0.0.1:8000/api/esb/services \
  -H "X-API-Key: test_bank_key_8CGbIjJCMRWLjFll" \
  -H "X-API-Secret: test_bank_secret_GtlfgLnQYUpcTwOry9LV1xvRcCVFO1Ci"
```

**Expected Response:**
```json
{
  "services": [
    {
      "code": "MONEY_COLLECTION",
      "name": "Mobile Money Collection",
      "description": "Collect money from mobile subscribers through USSD push requests",
      "aggregator": "Tembo Plus",
      "rate_limit": 50,
      "quota": 1000
    }
  ]
}
```

### 3. Money Collection Request

```bash
curl -X POST http://127.0.0.1:8000/api/esb/MONEY_COLLECTION \
  -H "X-API-Key: test_bank_key_8CGbIjJCMRWLjFll" \
  -H "X-API-Secret: test_bank_secret_GtlfgLnQYUpcTwOry9LV1xvRcCVFO1Ci" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_phone": "255778342299",
    "mobile_network": "TZ-AIRTEL-C2B",
    "amount": 1000,
    "description": "Test payment from ESB",
    "reference": "TEST_1234567890",
    "date": "2025-07-19 16:57:53",
    "webhook_url": "https://webhook.site/your-unique-url"
  }'
```

**Expected Response:**
```json
{
  "status": "PENDING_ACK",
  "reference": "TEST_1234567890",
  "transaction_id": "X50jcLD-U"
}
```

### 4. Collection Balance Request

```bash
curl -X POST http://127.0.0.1:8000/api/esb/COLLECTION_BALANCE \
  -H "X-API-Key: test_bank_key_8CGbIjJCMRWLjFll" \
  -H "X-API-Secret: test_bank_secret_GtlfgLnQYUpcTwOry9LV1xvRcCVFO1Ci" \
  -H "Content-Type: application/json"
```

**Expected Response:**
```json
{
  "available_balance": 10000,
  "current_balance": 10000,
  "account_number": "8000837333",
  "account_status": "ACTIVE",
  "account_name": "ABC TRADERS LTD - Collection"
}
```

### 5. Collection Statement Request

```bash
curl -X POST http://127.0.0.1:8000/api/esb/COLLECTION_STATEMENT \
  -H "X-API-Key: test_bank_key_8CGbIjJCMRWLjFll" \
  -H "X-API-Secret: test_bank_secret_GtlfgLnQYUpcTwOry9LV1xvRcCVFO1Ci" \
  -H "Content-Type: application/json" \
  -d '{
    "start_date": "2025-01-01",
    "end_date": "2025-01-31"
  }'
```

**Expected Response:**
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

### 6. Payment Status Request

```bash
curl -X POST http://127.0.0.1:8000/api/esb/PAYMENT_STATUS \
  -H "X-API-Key: test_bank_key_8CGbIjJCMRWLjFll" \
  -H "X-API-Secret: test_bank_secret_GtlfgLnQYUpcTwOry9LV1xvRcCVFO1Ci" \
  -H "Content-Type: application/json" \
  -d '{
    "transaction_id": "X50jcLD-U",
    "reference": "TEST_1234567890"
  }'
```

**Expected Response:**
```json
{
  "status": "PAYMENT_ACCEPTED",
  "transaction_id": "X50jcLD-U",
  "reference": "TEST_1234567890"
}
```

## 📊 Request/Response Mapping

### **Money Collection Service**

#### Client Request Format → Aggregator Format

| Client Field | Aggregator Field | Description |
|--------------|------------------|-------------|
| `customer_phone` | `msisdn` | Mobile number (255XXX123456) |
| `mobile_network` | `channel` | Network (TZ-TIGO-C2B, TZ-AIRTEL-C2B) |
| `amount` | `amount` | Amount to collect |
| `description` | `narration` | Transaction description |
| `reference` | `transactionRef` | Your reference |
| `date` | `transactionDate` | Transaction date (YYYY-MM-DD HH:mm:ss) |
| `webhook_url` | `callbackUrl` | Webhook URL |

#### Aggregator Response Format → Client Format

| Aggregator Field | Client Field | Description |
|------------------|--------------|-------------|
| `statusCode` | `status` | Transaction status |
| `transactionRef` | `reference` | Your reference |
| `transactionId` | `transaction_id` | Tembo transaction ID |

### **Collection Balance Service**

#### Client Request Format → Aggregator Format
No request body required.

#### Aggregator Response Format → Client Format

| Aggregator Field | Client Field | Description |
|------------------|--------------|-------------|
| `availableBalance` | `available_balance` | Available balance |
| `currentBalance` | `current_balance` | Current balance |
| `accountNo` | `account_number` | Account number |
| `accountStatus` | `account_status` | Account status |
| `accountName` | `account_name` | Account name |

### **Collection Statement Service**

#### Client Request Format → Aggregator Format

| Client Field | Aggregator Field | Description |
|--------------|------------------|-------------|
| `start_date` | `startDate` | Start date (YYYY-MM-DD) |
| `end_date` | `endDate` | End date (YYYY-MM-DD) |

#### Aggregator Response Format → Client Format

| Aggregator Field | Client Field | Description |
|------------------|--------------|-------------|
| `accountNo` | `account_number` | Account number |
| `debitOrCredit` | `transaction_type` | Transaction type (CR/DR) |
| `tranRefNo` | `transaction_reference` | Transaction reference |
| `narration` | `description` | Transaction description |
| `txnDate` | `transaction_date` | Transaction date |
| `valueDate` | `value_date` | Value date |
| `amountCredited` | `amount_credited` | Amount credited |
| `amountDebited` | `amount_debited` | Amount debited |
| `balance` | `balance` | Running balance |

### **Payment Status Service**

#### Client Request Format → Aggregator Format

| Client Field | Aggregator Field | Description |
|--------------|------------------|-------------|
| `transaction_id` | `transactionId` | Transaction ID from aggregator |
| `reference` | `transactionRef` | Your reference |

#### Aggregator Response Format → Client Format

| Aggregator Field | Client Field | Description |
|------------------|--------------|-------------|
| `statusCode` | `status` | Transaction status |
| `transactionId` | `transaction_id` | Transaction ID |
| `transactionRef` | `reference` | Your reference |

## 🔧 Data Transformations

The ESB automatically applies these transformations:

1. **Date Formatting**: Converts client date to `YYYY-MM-DD HH:mm:ss` format
2. **Network Uppercase**: Converts mobile network to uppercase
3. **Field Mapping**: Maps client fields to aggregator fields

## 🚨 Error Handling

### Common Error Responses

1. **Invalid Credentials (401)**
```json
{
  "status": "error",
  "message": "Invalid API credentials"
}
```

2. **Service Not Available (404)**
```json
{
  "status": "error",
  "message": "Service not available"
}
```

3. **Invalid Request Data (400)**
```json
{
  "status": "error",
  "message": "Invalid request data",
  "errors": {
    "customer_phone": ["The customer phone field is required."]
  }
}
```

4. **Rate Limit Exceeded (429)**
```json
{
  "status": "error",
  "message": "Rate limit exceeded"
}
```

## 📈 Monitoring & Analytics

### Dashboard Features

1. **Real-time Statistics**
   - Total transactions
   - Success rates
   - Response times
   - Error rates

2. **Transaction History**
   - Complete audit trail
   - Request/response data
   - Performance metrics

3. **Alert System**
   - Service health monitoring
   - Error rate alerts
   - Performance degradation alerts

## 🧪 Running Tests

### Option 1: Using the Test Script

```bash
# Start Laravel server
php artisan serve

# In another terminal, run the test script
php test_tembo_api.php
```

### Option 2: Using cURL

```bash
# Test health check
curl -X GET http://127.0.0.1:8000/api/esb/health

# Test money collection
curl -X POST http://127.0.0.1:8000/api/esb/MONEY_COLLECTION \
  -H "X-API-Key: test_bank_key_8CGbIjJCMRWLjFll" \
  -H "X-API-Secret: test_bank_secret_GtlfgLnQYUpcTwOry9LV1xvRcCVFO1Ci" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_phone": "255778342299",
    "mobile_network": "TZ-AIRTEL-C2B",
    "amount": 1000,
    "description": "Test payment",
    "reference": "TEST_123",
    "date": "2025-07-19 16:57:53",
    "webhook_url": "https://webhook.site/test"
  }'
```

### Option 3: Using Postman

1. **Health Check**
   - Method: `GET`
   - URL: `http://127.0.0.1:8000/api/esb/health`

2. **Get Services**
   - Method: `GET`
   - URL: `http://127.0.0.1:8000/api/esb/services`
   - Headers:
     - `X-API-Key`: `test_bank_key_8CGbIjJCMRWLjFll`
     - `X-API-Secret`: `test_bank_secret_GtlfgLnQYUpcTwOry9LV1xvRcCVFO1Ci`

3. **Money Collection**
   - Method: `POST`
   - URL: `http://127.0.0.1:8000/api/esb/MONEY_COLLECTION`
   - Headers:
     - `X-API-Key`: `test_bank_key_8CGbIjJCMRWLjFll`
     - `X-API-Secret`: `test_bank_secret_GtlfgLnQYUpcTwOry9LV1xvRcCVFO1Ci`
     - `Content-Type`: `application/json`
   - Body (raw JSON):
   ```json
   {
     "customer_phone": "255778342299",
     "mobile_network": "TZ-AIRTEL-C2B",
     "amount": 1000,
     "description": "Test payment from ESB",
     "reference": "TEST_1234567890",
     "date": "2025-07-19 16:57:53",
     "webhook_url": "https://webhook.site/your-unique-url"
   }
   ```

## 🔍 Troubleshooting

### Common Issues

1. **Server Not Running**
   - Ensure Laravel server is running: `php artisan serve`

2. **Database Issues**
   - Run migrations: `php artisan migrate`
   - Run seeder: `php artisan db:seed --class=TemboAggregatorSeeder`

3. **Authentication Errors**
   - Verify API key and secret are correct
   - Check that client is active in database

4. **Validation Errors**
   - Ensure all required fields are provided
   - Check field formats (phone number, date, etc.)

### Debug Information

Check the Laravel logs for detailed error information:
```bash
tail -f storage/logs/laravel.log
```

## 📞 Support

For issues or questions:
1. Check the Laravel logs
2. Verify database records
3. Test individual components
4. Review the ESB dashboard for monitoring data

---

**Note**: This is a sandbox environment. For production use, replace the sandbox URLs and credentials with production values. 