# 🚀 Enhanced Transaction System Documentation

## 📋 Overview

The ZIMA ESB Enhanced Transaction System provides comprehensive transaction recording, real-time status tracking, callback processing, and webhook notifications for complete audit trails and reconciliation.

## 🏗️ System Architecture

### **Core Components:**

1. **Enhanced Transactions Table** - Comprehensive transaction recording
2. **Callback Controller** - Handles aggregator callbacks
3. **Webhook System** - Real-time client notifications
4. **Audit Trail** - Complete action tracking
5. **Risk Assessment** - Automated risk monitoring
6. **Performance Metrics** - Response time tracking

## 📊 Transaction Table Schema

### **Transaction Identification:**
- `transaction_id` - Unique ESB transaction ID
- `external_transaction_id` - Aggregator's transaction ID
- `aggregator_reference` - Aggregator's reference number
- `client_reference` - Client's reference number

### **Financial Details:**
- `amount` - Transaction amount (decimal)
- `currency` - Currency code (default: TZS)
- `fee_amount` - Processing fees
- `commission_amount` - Commission charges

### **Customer Information:**
- `customer_phone` - Customer mobile number
- `customer_name` - Customer name (if available)
- `mobile_network` - Network code (e.g., TZ-TIGO-C2B)
- `network_provider` - Provider name (e.g., Tigo)

### **Transaction Details:**
- `description` - Transaction description
- `narration` - Payment narration
- `channel` - Payment channel
- `payment_method` - Payment method

### **Request/Response Tracking:**
- `original_request` - Client's original request
- `transformed_request` - ESB transformed request
- `aggregator_request` - Request sent to aggregator
- `aggregator_response` - Raw aggregator response
- `transformed_response` - ESB transformed response
- `final_response` - Final response to client

### **Status Tracking:**
- `status` - ESB transaction status
- `aggregator_status` - Aggregator's status
- `client_status` - Client notification status
- `aggregator_processed_at` - When aggregator processed
- `client_notified_at` - When client was notified

### **Performance Metrics:**
- `esb_processing_time` - ESB processing time
- `aggregator_response_time` - Aggregator response time
- `total_processing_time` - Total processing time
- `retry_count` - Number of retry attempts
- `last_retry_at` - Last retry timestamp

### **Security & Compliance:**
- `request_signature` - Request signature
- `response_signature` - Response signature
- `checksum` - Data integrity check
- `is_encrypted` - Encryption flag

### **Webhook & Notifications:**
- `webhook_url` - Client webhook URL
- `webhook_attempts` - Webhook delivery attempts
- `last_webhook_sent_at` - Last webhook timestamp
- `webhook_responses` - Webhook response history

### **Reconciliation:**
- `is_reconciled` - Reconciliation status
- `reconciled_at` - Reconciliation timestamp
- `reconciled_by` - Who reconciled
- `reconciliation_notes` - Reconciliation notes

### **Audit & Compliance:**
- `created_by` - Who created the transaction
- `updated_by` - Who last updated
- `audit_trail` - Complete audit trail
- `session_id` - Session identifier
- `correlation_id` - Correlation ID
- `trace_id` - Trace ID for debugging

### **Risk Management:**
- `risk_level` - Risk assessment (low/medium/high)
- `is_suspicious` - Suspicious transaction flag
- `risk_notes` - Risk assessment notes
- `requires_manual_review` - Manual review flag

### **Settlement:**
- `is_settled` - Settlement status
- `settled_at` - Settlement timestamp
- `settlement_reference` - Settlement reference
- `settlement_amount` - Settlement amount

## 🔄 Callback System

### **Callback Endpoints:**
```
POST /api/callback/{aggregatorCode}
GET /api/callback/status/{transactionId}
```

### **Callback Processing:**
1. **Signature Validation** - Validates callback authenticity
2. **Transaction Lookup** - Finds transaction by reference
3. **Status Update** - Updates transaction status
4. **Data Extraction** - Extracts relevant information
5. **Webhook Notification** - Notifies client
6. **Audit Trail** - Records callback action

### **Supported Aggregators:**
- **TEMBO** - Tembo Plus Money Collection
- **MPESA** - M-Pesa (future)
- **AIRTEL** - Airtel Money (future)

## 📡 Webhook System

### **Webhook Data Format:**
```json
{
  "transaction_id": "TXN_687BD798723AB",
  "external_transaction_id": "EXT_ABC123",
  "client_reference": "TXN_1752946583",
  "status": "success",
  "aggregator_status": "success",
  "amount": 5000,
  "currency": "TZS",
  "customer_phone": "255778342299",
  "mobile_network": "TZ-TIGO-C2B",
  "description": "Payment for services",
  "timestamp": "2025-07-19T17:26:04.000000Z",
  "response": {
    "statusCode": "success",
    "message": "Transaction processed successfully"
  }
}
```

### **Webhook Delivery:**
- **Retry Logic** - Automatic retry on failure
- **Response Tracking** - Records webhook responses
- **Timeout Handling** - 10-second timeout
- **Error Logging** - Comprehensive error logging

## 🔍 Risk Assessment

### **Risk Levels:**
- **Low Risk** - Amount ≤ TZS 50,000
- **Medium Risk** - Amount TZS 50,001 - 100,000
- **High Risk** - Amount > TZS 100,000

### **Risk Factors:**
- Transaction amount
- Customer phone number patterns
- Network provider
- Transaction frequency
- Historical behavior

## 📈 Performance Monitoring

### **Metrics Tracked:**
- ESB processing time
- Aggregator response time
- Total processing time
- Request/response sizes
- Retry attempts
- Success/failure rates

### **Performance Alerts:**
- Slow response times
- High failure rates
- Aggregator timeouts
- Webhook delivery failures

## 🔐 Security Features

### **Authentication:**
- API key/secret validation
- Callback signature verification
- Request timestamp validation
- IP address tracking

### **Data Protection:**
- Request/response encryption
- Checksum validation
- Audit trail logging
- Secure webhook delivery

## 📊 Reconciliation Support

### **Reconciliation Process:**
1. **Transaction Matching** - Match ESB vs aggregator records
2. **Amount Verification** - Verify transaction amounts
3. **Status Reconciliation** - Align transaction statuses
4. **Fee Calculation** - Calculate and verify fees
5. **Settlement Tracking** - Track settlement status

### **Reconciliation Reports:**
- Daily reconciliation reports
- Unreconciled transactions
- Discrepancy reports
- Settlement summaries

## 🛠️ API Endpoints

### **Transaction Processing:**
```
POST /api/esb/{serviceCode}
```

### **Status Checking:**
```
GET /api/callback/status/{transactionId}
```

### **Health Check:**
```
GET /api/esb/health
```

### **Services List:**
```
GET /api/esb/services
```

## 📝 Usage Examples

### **1. Process Money Collection:**
```bash
curl -X POST http://127.0.0.1:8000/api/esb/MONEY_COLLECTION \
  -H "X-API-Key: your_api_key" \
  -H "X-API-Secret: your_api_secret" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_phone": "255778342299",
    "mobile_network": "TZ-TIGO-C2B",
    "amount": 5000,
    "description": "Payment for services",
    "reference": "TXN_123456",
    "date": "2025-07-19 17:26:04",
    "webhook_url": "https://your-webhook-url.com/callback"
  }'
```

### **2. Check Transaction Status:**
```bash
curl -X GET http://127.0.0.1:8000/api/callback/status/TXN_687BD798723AB
```

### **3. Simulate Callback:**
```bash
curl -X POST http://127.0.0.1:8000/api/callback/TEMBO \
  -H "Content-Type: application/json" \
  -H "X-Signature: your_signature" \
  -H "X-Timestamp: 1752946583" \
  -d '{
    "transactionRef": "TXN_123456",
    "status": "success",
    "transactionId": "EXT_ABC123",
    "amount": 5000,
    "msisdn": "255778342299"
  }'
```

## 🔧 Configuration

### **Environment Variables:**
```env
# Webhook Configuration
WEBHOOK_TIMEOUT=10
WEBHOOK_MAX_RETRIES=3

# Security Configuration
CALLBACK_SIGNATURE_REQUIRED=true
CALLBACK_TIMESTAMP_TOLERANCE=300

# Performance Configuration
AGGREGATOR_TIMEOUT=30
MAX_RETRY_ATTEMPTS=3
```

### **Database Indexes:**
- `external_transaction_id`
- `aggregator_reference`
- `client_reference`
- `customer_phone`
- `amount_created_at`
- `aggregator_status_created_at`
- `is_reconciled_created_at`
- `is_settled_created_at`
- `risk_level_created_at`
- `webhook_url_created_at`

## 📊 Monitoring & Alerts

### **Key Metrics:**
- Transaction success rate
- Average processing time
- Webhook delivery success rate
- Reconciliation accuracy
- Risk level distribution

### **Alert Types:**
- High failure rates
- Slow response times
- Unreconciled transactions
- Suspicious activity
- Webhook delivery failures

## 🚀 Production Deployment

### **Requirements:**
- Laravel 12+
- PHP 8.4+
- MySQL/PostgreSQL
- Redis (for caching)
- Queue worker (for webhooks)

### **Deployment Steps:**
1. Run migrations
2. Configure environment variables
3. Set up queue workers
4. Configure monitoring
5. Set up backup procedures
6. Test callback endpoints

## 🔍 Troubleshooting

### **Common Issues:**
1. **Transaction Not Found** - Check reference matching
2. **Webhook Failures** - Verify URL and timeout settings
3. **Callback Errors** - Check signature validation
4. **Performance Issues** - Monitor response times

### **Debug Tools:**
- Laravel logs (`storage/logs/laravel.log`)
- Transaction audit trail
- Webhook response history
- Performance metrics

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [ESB Best Practices](https://en.wikipedia.org/wiki/Enterprise_service_bus)
- [Webhook Security](https://webhooks.fyi/)
- [Payment Processing](https://stripe.com/docs/payments)

---

**🎉 The Enhanced Transaction System provides enterprise-grade transaction processing with comprehensive audit trails, real-time monitoring, and robust error handling for production-ready payment processing.** 