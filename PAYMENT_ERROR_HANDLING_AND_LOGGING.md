# Payment Error Handling and Logging System

## Overview

This document outlines the comprehensive error handling and logging system implemented for the ZIMA ESB payment processing platform. The system ensures graceful error handling, detailed logging of all third-party responses, and user-friendly error messages.

## 🎯 Key Features

### 1. **Graceful Error Handling**
- User-friendly error messages instead of raw JSON responses
- Proper HTTP status codes for different error types
- Automatic retry logic with exponential backoff
- Transaction rollback on failures

### 2. **Comprehensive Logging**
- Separate log channels for different types of events
- Sanitized sensitive data in logs
- Detailed third-party API request/response logging
- Security event tracking
- Performance metrics

### 3. **Real-time Monitoring**
- Command-line tools for log monitoring
- Color-coded log output
- Real-time log following capability

## 📁 Log Channels

### Payment Processing Logs
- **File**: `storage/logs/payments-YYYY-MM-DD.log`
- **Level**: Debug
- **Retention**: 30 days
- **Content**: Payment start, validation, success events

### Payment Error Logs
- **File**: `storage/logs/payment_errors-YYYY-MM-DD.log`
- **Level**: Warning
- **Retention**: 90 days
- **Content**: Payment failures, validation errors, exceptions

### Aggregator Response Logs
- **File**: `storage/logs/aggregator_responses-YYYY-MM-DD.log`
- **Level**: Debug
- **Retention**: 30 days
- **Content**: All third-party API responses

### Third-Party API Logs
- **File**: `storage/logs/third_party_api-YYYY-MM-DD.log`
- **Level**: Debug
- **Retention**: 30 days
- **Content**: API requests, responses, timeouts

### Security Event Logs
- **File**: `storage/logs/security_events-YYYY-MM-DD.log`
- **Level**: Info
- **Retention**: 365 days
- **Content**: Threat detection, rate limiting, security incidents

## 🔧 Error Message Mapping

### Technical to User-Friendly Messages

| Technical Error | User-Friendly Message |
|----------------|----------------------|
| `Aggregator returned status: 409` | "This payment has already been processed. Please check your payment status." |
| `Aggregator returned status: 400` | "Invalid payment information. Please check your details and try again." |
| `Aggregator returned status: 401` | "Payment service authentication failed. Please try again later." |
| `Aggregator returned status: 403` | "Payment service access denied. Please try again later." |
| `Aggregator returned status: 500` | "Payment service is temporarily unavailable. Please try again in a few minutes." |
| `Service temporarily unavailable` | "Payment service is temporarily unavailable. Please try again in a few minutes." |
| `Payment link not found` | "This payment link is no longer available or has expired." |
| `Payment link is not available for use` | "This payment link is no longer available for payments." |
| `Invalid payment data` | "Please check your payment information and try again." |
| `Customer information required` | "Please provide all required customer information." |
| `Invalid item code` | "Payment item information is invalid. Please try again." |
| `Invalid payment amount` | "Please enter a valid payment amount." |
| `Payment processing failed` | "Unable to process your payment at this time. Please try again." |

## 🛡️ Security Features

### Data Sanitization
- Phone numbers masked in logs (e.g., `255712****78`)
- API keys and secrets partially hidden
- Sensitive headers sanitized
- Customer information protected

### Threat Detection
- SQL injection detection
- XSS attack prevention
- Command injection blocking
- Rate limiting per IP
- Suspicious behavior monitoring

### Rate Limiting
- **Payment Processing**: 10 attempts per hour per IP
- **Payment Link Access**: 100 requests per hour per IP
- **API Endpoints**: Configurable per endpoint

## 📊 Logging Structure

### Payment Start Log
```json
{
  "timestamp": "2025-07-20T13:38:23.267501Z",
  "link_id": "test123",
  "short_code": "xib7Sxkn",
  "amount": 50000,
  "mobile_network": "TZ-TIGO-C2B",
  "customer_phone": "255712****78",
  "ip_address": "192.168.1.1",
  "user_agent": "Mozilla/5.0...",
  "session_id": "abc123"
}
```

### Aggregator Request Log
```json
{
  "timestamp": "2025-07-20T13:38:23.267501Z",
  "transaction_id": "TXN123",
  "link_id": "test123",
  "aggregator_name": "Tembo Plus",
  "service_name": "Mobile Money Collection",
  "url": "https://api.temboplus.com/collect",
  "method": "POST",
  "request_headers": {
    "Content-Type": "application/json",
    "x-account-id": "acc****ey",
    "x-secret-key": "sec****ey"
  },
  "request_data": {
    "amount": 50000,
    "customer_phone": "255712****78"
  },
  "timeout": 30,
  "attempt": 1
}
```

### Aggregator Response Log
```json
{
  "timestamp": "2025-07-20T13:38:24.123456Z",
  "transaction_id": "TXN123",
  "link_id": "test123",
  "aggregator_name": "Tembo Plus",
  "service_name": "Mobile Money Collection",
  "status_code": 200,
  "response_time": 0.856,
  "response_headers": {
    "Content-Type": "application/json"
  },
  "response_data": {
    "status": "success",
    "transaction_id": "TMB123456",
    "message": "Payment initiated successfully"
  },
  "success": true,
  "attempt": 1
}
```

## 🚀 Usage Examples

### Monitor Payment Logs
```bash
# Show last 50 payment logs
php artisan logs:monitor-payments

# Monitor payment errors in real-time
php artisan logs:monitor-payments --channel=payment_errors --follow

# Monitor aggregator responses
php artisan logs:monitor-payments --channel=aggregator_responses --lines=100

# Monitor security events
php artisan logs:monitor-payments --channel=security_events
```

### View Log Files Directly
```bash
# Payment logs
tail -f storage/logs/payments-2025-07-20.log

# Error logs
tail -f storage/logs/payment_errors-2025-07-20.log

# Aggregator responses
tail -f storage/logs/aggregator_responses-2025-07-20.log

# Security events
tail -f storage/logs/security_events-2025-07-20.log
```

## 🔍 Debugging Common Issues

### Payment Processing Failures
1. Check `payment_errors-YYYY-MM-DD.log` for detailed error information
2. Look for aggregator response status codes in `aggregator_responses-YYYY-MM-DD.log`
3. Verify payment link status and expiration
4. Check customer information validation

### Third-Party API Issues
1. Monitor `third_party_api-YYYY-MM-DD.log` for request/response details
2. Check network connectivity and timeouts
3. Verify API credentials and authentication
4. Review rate limiting and quota usage

### Security Incidents
1. Check `security_events-YYYY-MM-DD.log` for threat detection events
2. Review IP addresses and user agents
3. Monitor rate limiting violations
4. Check for suspicious patterns

## 📈 Performance Monitoring

### Response Time Tracking
- All API calls include response time measurement
- Performance metrics logged for analysis
- Slow response alerts (configurable thresholds)

### Success Rate Monitoring
- Payment success/failure ratios tracked
- Aggregator-specific performance metrics
- Error pattern analysis

### Resource Usage
- Memory usage monitoring
- Database query performance
- Cache hit/miss ratios

## 🔧 Configuration

### Log Levels
```php
// config/logging.php
'payments' => [
    'driver' => 'daily',
    'path' => storage_path('logs/payments.log'),
    'level' => 'debug',
    'days' => 30,
],
```

### Rate Limiting
```php
// Payment processing: 10 attempts per hour per IP
Cache::put($rateLimitKey, $processingCount + 1, 3600);
```

### Error Message Mapping
```php
// app/Http/Controllers/Public/PaymentController.php
private function getUserFriendlyErrorMessage(string $error): string
{
    $errorMap = [
        'Aggregator returned status: 409' => 'This payment has already been processed...',
        // ... more mappings
    ];
}
```

## 🛠️ Maintenance

### Log Rotation
- Daily log rotation automatically handled
- Old logs automatically deleted based on retention policy
- Manual log cleanup available

### Log Analysis
- Structured JSON logging for easy parsing
- Search and filter capabilities
- Export functionality for analysis

### Monitoring Alerts
- High error rate alerts
- Performance degradation notifications
- Security incident alerts

## 📞 Support

For issues with the payment system:
- Check logs first using the monitoring commands
- Review error patterns and frequencies
- Contact support with specific error details and transaction IDs

---

**Last Updated**: July 20, 2025
**Version**: 1.0
**Maintainer**: ZIMA ESB Development Team 