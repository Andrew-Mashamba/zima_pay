<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class PaymentLoggingService
{
    /**
     * Log payment processing start
     */
    public function logPaymentStart(array $data): void
    {
        Log::channel('payments')->info('Payment processing started', [
            'timestamp' => now()->toISOString(),
            'link_id' => $data['link_id'] ?? null,
            'short_code' => $data['short_code'] ?? null,
            'amount' => $data['amount'] ?? null,
            'mobile_network' => $data['mobile_network'] ?? null,
            'customer_phone' => $this->maskPhone($data['customer_phone'] ?? null),
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'session_id' => $data['session_id'] ?? null
        ]);
    }

    /**
     * Log payment validation
     */
    public function logPaymentValidation(array $data, bool $success, array $errors = []): void
    {
        $logData = [
            'timestamp' => now()->toISOString(),
            'link_id' => $data['link_id'] ?? null,
            'short_code' => $data['short_code'] ?? null,
            'success' => $success,
            'validation_errors' => $errors,
            'ip_address' => $data['ip_address'] ?? null
        ];

        if ($success) {
            Log::channel('payments')->info('Payment validation passed', $logData);
        } else {
            Log::channel('payment_errors')->warning('Payment validation failed', $logData);
        }
    }

    /**
     * Log aggregator request
     */
    public function logAggregatorRequest(array $data): void
    {
        Log::channel('third_party_api')->info('Aggregator request sent', [
            'timestamp' => now()->toISOString(),
            'transaction_id' => $data['transaction_id'] ?? null,
            'link_id' => $data['link_id'] ?? null,
            'aggregator_name' => $data['aggregator_name'] ?? null,
            'service_name' => $data['service_name'] ?? null,
            'url' => $data['url'] ?? null,
            'method' => $data['method'] ?? null,
            'request_headers' => $this->sanitizeHeaders($data['headers'] ?? []),
            'request_data' => $this->sanitizeData($data['data'] ?? []),
            'timeout' => $data['timeout'] ?? null,
            'attempt' => $data['attempt'] ?? 1
        ]);
    }

    /**
     * Log aggregator response
     */
    public function logAggregatorResponse(array $data): void
    {
        $logData = [
            'timestamp' => now()->toISOString(),
            'transaction_id' => $data['transaction_id'] ?? null,
            'link_id' => $data['link_id'] ?? null,
            'aggregator_name' => $data['aggregator_name'] ?? null,
            'service_name' => $data['service_name'] ?? null,
            'status_code' => $data['status_code'] ?? null,
            'response_time' => $data['response_time'] ?? null,
            'response_headers' => $this->sanitizeHeaders($data['response_headers'] ?? []),
            'response_data' => $this->sanitizeData($data['response_data'] ?? []),
            'success' => $data['success'] ?? false,
            'attempt' => $data['attempt'] ?? 1
        ];

        if ($data['success'] ?? false) {
            Log::channel('aggregator_responses')->info('Aggregator response received', $logData);
        } else {
            Log::channel('aggregator_responses')->warning('Aggregator error response', $logData);
        }
    }

    /**
     * Log payment success
     */
    public function logPaymentSuccess(array $data): void
    {
        Log::channel('payments')->info('Payment processed successfully', [
            'timestamp' => now()->toISOString(),
            'transaction_id' => $data['transaction_id'] ?? null,
            'link_id' => $data['link_id'] ?? null,
            'short_code' => $data['short_code'] ?? null,
            'amount' => $data['amount'] ?? null,
            'mobile_network' => $data['mobile_network'] ?? null,
            'customer_phone' => $this->maskPhone($data['customer_phone'] ?? null),
            'aggregator_transaction_id' => $data['aggregator_transaction_id'] ?? null,
            'response_time' => $data['response_time'] ?? null,
            'aggregator_status' => $data['aggregator_status'] ?? null,
            'ip_address' => $data['ip_address'] ?? null
        ]);
    }

    /**
     * Log payment error
     */
    public function logPaymentError(array $data, \Exception $exception = null): void
    {
        $logData = [
            'timestamp' => now()->toISOString(),
            'transaction_id' => $data['transaction_id'] ?? null,
            'link_id' => $data['link_id'] ?? null,
            'short_code' => $data['short_code'] ?? null,
            'amount' => $data['amount'] ?? null,
            'mobile_network' => $data['mobile_network'] ?? null,
            'customer_phone' => $this->maskPhone($data['customer_phone'] ?? null),
            'error_message' => $data['error_message'] ?? null,
            'error_type' => $data['error_type'] ?? null,
            'aggregator_status' => $data['aggregator_status'] ?? null,
            'response_time' => $data['response_time'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null
        ];

        if ($exception) {
            $logData['exception'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];
        }

        Log::channel('payment_errors')->error('Payment processing failed', $logData);
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(string $eventType, array $data): void
    {
        Log::channel('security_events')->warning('Security event detected', [
            'timestamp' => now()->toISOString(),
            'event_type' => $eventType,
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'endpoint' => $data['endpoint'] ?? null,
            'threats_detected' => $data['threats_detected'] ?? [],
            'risk_score' => $data['risk_score'] ?? 0,
            'severity' => $data['severity'] ?? 'medium',
            'link_id' => $data['link_id'] ?? null,
            'short_code' => $data['short_code'] ?? null
        ]);
    }

    /**
     * Log rate limiting event
     */
    public function logRateLimitExceeded(array $data): void
    {
        Log::channel('security_events')->warning('Rate limit exceeded', [
            'timestamp' => now()->toISOString(),
            'ip_address' => $data['ip_address'] ?? null,
            'endpoint' => $data['endpoint'] ?? null,
            'attempts' => $data['attempts'] ?? 0,
            'limit' => $data['limit'] ?? 0,
            'window' => $data['window'] ?? null,
            'link_id' => $data['link_id'] ?? null,
            'short_code' => $data['short_code'] ?? null
        ]);
    }

    /**
     * Log webhook notification
     */
    public function logWebhookNotification(array $data): void
    {
        $logData = [
            'timestamp' => now()->toISOString(),
            'transaction_id' => $data['transaction_id'] ?? null,
            'webhook_url' => $data['webhook_url'] ?? null,
            'status_code' => $data['status_code'] ?? null,
            'response_body' => $data['response_body'] ?? null,
            'success' => $data['success'] ?? false
        ];

        if ($data['success'] ?? false) {
            Log::channel('payments')->info('Webhook notification sent successfully', $logData);
        } else {
            Log::channel('payment_errors')->warning('Webhook notification failed', $logData);
        }
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStats(string $period = '24h'): array
    {
        $cacheKey = "payment_stats_{$period}";
        
        return Cache::remember($cacheKey, 300, function () use ($period) {
            $startTime = match($period) {
                '1h' => now()->subHour(),
                '24h' => now()->subDay(),
                '7d' => now()->subWeek(),
                '30d' => now()->subMonth(),
                default => now()->subDay()
            };

            // This would typically query your database
            // For now, return mock data
            return [
                'period' => $period,
                'total_payments' => rand(100, 1000),
                'successful_payments' => rand(80, 900),
                'failed_payments' => rand(10, 100),
                'total_amount' => rand(1000000, 10000000),
                'average_response_time' => rand(1000, 5000),
                'success_rate' => rand(85, 95)
            ];
        });
    }

    /**
     * Mask phone number for logging
     */
    private function maskPhone(?string $phone): ?string
    {
        if (!$phone || strlen($phone) < 4) {
            return $phone;
        }
        
        return substr($phone, 0, 6) . '****' . substr($phone, -2);
    }

    /**
     * Sanitize headers for logging
     */
    private function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'x-account-id', 'x-secret-key', 'x-api-key', 'x-api-secret'];
        $sanitized = [];
        
        foreach ($headers as $key => $value) {
            if (in_array(strtolower($key), $sensitiveHeaders)) {
                if (is_array($value)) {
                    $sanitized[$key] = array_map(function($v) {
                        return strlen($v) > 4 ? substr($v, 0, 2) . '****' . substr($v, -2) : '****';
                    }, $value);
                } else {
                    $sanitized[$key] = strlen($value) > 4 ? substr($value, 0, 2) . '****' . substr($value, -2) : '****';
                }
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Sanitize data for logging
     */
    private function sanitizeData($data): mixed
    {
        if (is_array($data)) {
            $sensitiveKeys = ['password', 'secret', 'token', 'key', 'api_key', 'api_secret', 'customer_phone'];
            $sanitized = [];
            
            foreach ($data as $key => $value) {
                if (in_array(strtolower($key), $sensitiveKeys)) {
                    if (is_string($value) && strlen($value) > 4) {
                        $sanitized[$key] = substr($value, 0, 2) . '****' . substr($value, -2);
                    } else {
                        $sanitized[$key] = '****';
                    }
                } else {
                    $sanitized[$key] = is_array($value) ? $this->sanitizeData($value) : $value;
                }
            }
            
            return $sanitized;
        }
        
        return $data;
    }
} 