<?php

namespace App\Services;

use App\Models\ServiceMapping;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class EsbService
{
    /**
     * Process request through ESB
     */
    public function processRequest(ServiceMapping $serviceMapping, array $requestData, Transaction $transaction)
    {
        $startTime = microtime(true);
        
        try {
            // Log the incoming request
            Log::info('ESB Processing started', [
                'transaction_id' => $transaction->transaction_id,
                'service_mapping_id' => $serviceMapping->id,
                'service_name' => $serviceMapping->service->name,
                'aggregator_name' => $serviceMapping->aggregator->name,
                'request_data' => $this->sanitizeLogData($requestData)
            ]);
            
            // 1. Transform request data
            $transformedRequest = $serviceMapping->transformRequest($requestData);
            
            // Debug: Log the transformed request
            Log::info('ESB Request transformation', [
                'transaction_id' => $transaction->transaction_id,
                'original' => $this->sanitizeLogData($requestData),
                'transformed' => $this->sanitizeLogData($transformedRequest),
                'original_callback_url' => $requestData['webhook_url'] ?? 'not_set',
                'transformed_callback_url' => $transformedRequest['callbackUrl'] ?? $transformedRequest['webhook_url'] ?? 'not_found'
            ]);
            
            // 2. Prepare aggregator request
            $aggregatorRequest = $this->prepareAggregatorRequest($serviceMapping, $transformedRequest);
            
            // Debug: Log the aggregator request
            Log::info('ESB Aggregator request prepared', [
                'transaction_id' => $transaction->transaction_id,
                'url' => $aggregatorRequest['url'],
                'method' => $aggregatorRequest['method'],
                'headers' => $this->sanitizeHeaders($aggregatorRequest['headers']),
                'data' => $this->sanitizeLogData($aggregatorRequest['data'] ?? null),
                'callback_url_being_sent' => $aggregatorRequest['data']['callbackUrl'] ?? $aggregatorRequest['data']['webhook_url'] ?? 'not_found'
            ]);
            
            // 3. Send request to aggregator with retry logic
            $aggregatorResponse = $this->sendToAggregator($aggregatorRequest, $serviceMapping);

            if (empty($aggregatorResponse['success'])) {
                $detail = '';
                if (!empty($aggregatorResponse['data']) && is_array($aggregatorResponse['data'])) {
                    $detail = json_encode($aggregatorResponse['data']);
                } elseif (!empty($aggregatorResponse['error'])) {
                    $detail = (string) $aggregatorResponse['error'];
                }
                $http = $aggregatorResponse['status_code'] ?? '';
                throw new \Exception(
                    trim('Aggregator request failed (HTTP ' . $http . '): ' . $detail)
                );
            }
            
            // Log the aggregator response
            Log::info('ESB Aggregator response received', [
                'transaction_id' => $transaction->transaction_id,
                'success' => $aggregatorResponse['success'],
                'status_code' => $aggregatorResponse['status_code'],
                'response_data' => $this->sanitizeLogData($aggregatorResponse['data']),
                'response_headers' => $this->sanitizeHeaders($aggregatorResponse['headers'] ?? []),
                'response_time' => microtime(true) - $startTime
            ]);
            
            // 4. Transform response back to client format
            $transformedResponse = $this->transformAggregatorResponse($serviceMapping, $aggregatorResponse['data']);

            // Fill response with request values when aggregator does not return them (e.g. Tembo omits amount)
            $transformedResponse = $this->fillResponseFromRequest($transformedResponse, $requestData);

            // 5. Calculate response time
            $responseTime = microtime(true) - $startTime;
            
            // Log the final response
            Log::info('ESB Response transformation', [
                'transaction_id' => $transaction->transaction_id,
                'aggregator_response' => $this->sanitizeLogData($aggregatorResponse['data']),
                'transformed_response' => $this->sanitizeLogData($transformedResponse),
                'response_time' => $responseTime
            ]);

            // NOTE: Webhook notification is NOT sent here because the transaction is still pending.
            // The webhook will be sent by CallbackController when the aggregator (Tembo) calls back
            // with the final payment status (success/failed/cancelled).

            // Log successful processing
            Log::info('ESB Processing completed successfully', [
                'transaction_id' => $transaction->transaction_id,
                'service_mapping_id' => $serviceMapping->id,
                'response_time' => $responseTime,
                'aggregator_status' => $aggregatorResponse['status_code']
            ]);
            
            return [
                'success' => true,
                'response' => $transformedResponse,
                'status_code' => 200,
                'response_time' => $responseTime,
                'aggregator_response' => $aggregatorResponse
            ];
            
        } catch (\Exception $e) {
            $responseTime = microtime(true) - $startTime;
            
            // Log detailed error information
            Log::error('ESB Processing Error', [
                'transaction_id' => $transaction->transaction_id,
                'service_mapping_id' => $serviceMapping->id,
                'service_name' => $serviceMapping->service->name,
                'aggregator_name' => $serviceMapping->aggregator->name,
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'response_time' => $responseTime,
                'request_data' => $this->sanitizeLogData($requestData)
            ]);
            
            // Update transaction status to failed
            $transaction->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'updated_at' => now()
            ]);
            
            return [
                'success' => false,
                'response' => [
                    'status' => 'error',
                    'message' => 'Service temporarily unavailable',
                    'transaction_id' => $transaction->transaction_id,
                    'error_code' => 'ESB_ERROR'
                ],
                'status_code' => 500,
                'response_time' => $responseTime,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Prepare request for aggregator
     */
    protected function prepareAggregatorRequest(ServiceMapping $serviceMapping, array $transformedData)
    {
        $service = $serviceMapping->service;
        $aggregator = $serviceMapping->aggregator;
        $settings = is_array($aggregator->settings) ? $aggregator->settings : [];

        // Tembo uses x-account-id / x-secret-key; Selcom uses Digest - see wallet-pull-funds-push-ussd.md
        $isTembo = strtoupper($aggregator->code ?? '') === 'TEMBO';
        $isSelcom = !$isTembo && (
            ($settings['auth_type'] ?? null) === 'selcom_digest'
            || strtoupper($aggregator->code ?? '') === 'SELCOM'
        );

        $request = [
            'url' => $this->buildAggregatorUrl((string) $aggregator->api_endpoint, (string) $service->endpoint),
            'method' => $service->method,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'ZIMA-ESB/1.0'
            ],
            'timeout' => $service->timeout ?? 30,
        ];

        if ($isTembo) {
            // Tembo-style auth (x-account-id, x-secret-key) per TEMBO_API_TESTING.md
            if ($aggregator->api_key) {
                $request['headers']['x-account-id'] = $aggregator->api_key;
            }
            if ($aggregator->api_secret) {
                $request['headers']['x-secret-key'] = $aggregator->api_secret;
            }
        } elseif ($isSelcom) {
            $settings = is_array($aggregator->settings) ? $aggregator->settings : [];
            $vendor = $settings['vendor'] ?? env('SELCOM_VENDOR_ID', '01234567891');
            $transformedData['vendor'] = $transformedData['vendor'] ?? $vendor;
            $transformedData['transid'] = $transformedData['transid'] ?? ('TXN-' . time() . '-' . Str::random(6));
            $request = $this->addSelcomAuth($request, $aggregator, $service, $transformedData);
        } else {
            // Other aggregators: fallback to x-account-id / x-secret-key if present
            if ($aggregator->api_key) {
                $request['headers']['x-account-id'] = $aggregator->api_key;
            }
            if ($aggregator->api_secret) {
                $request['headers']['x-secret-key'] = $aggregator->api_secret;
            }
        }

        $request['headers']['x-request-id'] = uniqid();

        if ($aggregator->settings && isset($aggregator->settings['headers'])) {
            foreach ($aggregator->settings['headers'] as $key => $value) {
                if (strtolower($key) !== 'content-type') {
                    $request['headers'][$key] = $value;
                }
            }
        }

        if (in_array($service->method, ['POST', 'PUT', 'PATCH'])) {
            $request['data'] = $transformedData;
        } else {
            $request['params'] = $transformedData;
            if ($isSelcom && ($service->settings['query_params'] ?? false)) {
                $request['url'] = $request['url'] . '?' . http_build_query(array_filter($transformedData));
                $request['params'] = [];
            }
        }

        return $request;
    }

    /**
     * Join base URL and path without duplicating a /v1 segment (e.g. base .../v1 + path /v1/wallet/...).
     */
    protected function buildAggregatorUrl(string $baseUrl, string $endpoint): string
    {
        $base = rtrim($baseUrl, '/');
        $path = '/' . ltrim($endpoint, '/');
        if (preg_match('#/v1$#i', $base) && str_starts_with(strtolower($path), '/v1/')) {
            $path = substr($path, 3);
        }

        return $base . $path;
    }

    /**
     * Add Selcom Digest authentication (HMAC-SHA256)
     * Per https://developers.selcommobile.com/?#introduction :
     * Digest = Base64(HMAC-SHA256("timestamp={Ts}&k1=v1&k2=v2...", api_secret))
     * Every key in Signed-Fields must appear in that string (empty value if absent), in the same order.
     */
    protected function addSelcomAuth(array $request, $aggregator, $service, array $data): array
    {
        $timestamp = Carbon::now('Africa/Dar_es_Salaam')->format('Y-m-d\TH:i:sP');

        $request['headers']['Authorization'] = 'SELCOM ' . base64_encode($aggregator->api_key);
        $request['headers']['Timestamp'] = $timestamp;
        $request['headers']['Digest-Method'] = 'HS256';

        $serviceSettings = is_array($service->settings) ? $service->settings : [];
        $signedFields = $serviceSettings['signed_fields'] ?? 'transid,utilityref,amount,vendor,msisdn';

        if ($service->method === 'GET' && ($serviceSettings['query_params'] ?? false)) {
            $signedFields = $serviceSettings['signed_fields'] ?? 'transid,reference';
        }

        $parts = ['timestamp=' . $timestamp];
        foreach (explode(',', $signedFields) as $field) {
            $field = trim($field);
            if ($field === '') {
                continue;
            }
            $parts[] = $field . '=' . $this->selcomDigestScalar($data[$field] ?? null);
        }
        $signString = implode('&', $parts);
        $digest = base64_encode(hash_hmac('sha256', $signString, $aggregator->api_secret, true));

        $request['headers']['Digest'] = $digest;
        $request['headers']['Signed-Fields'] = $signedFields;

        return $request;
    }

    /**
     * Stringify a body field for Selcom digest signing (must match gateway expectations).
     */
    protected function selcomDigestScalar(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_int($value) || is_float($value)) {
            if (is_float($value) && is_finite($value) && floor($value) == $value) {
                return (string) (int) $value;
            }

            return (string) $value;
        }
        if (is_scalar($value)) {
            return (string) $value;
        }

        return '';
    }
    
    /**
     * Send request to aggregator with retry logic
     */
    protected function sendToAggregator(array $request, ServiceMapping $serviceMapping)
    {
        $maxRetries = $serviceMapping->service->retry_attempts ?? 3;
        $attempt = 0;
        
        while ($attempt < $maxRetries) {
            $attemptStartTime = microtime(true);
            
            // Generate unique reference and data for each retry attempt to avoid duplicate request errors
            if ($attempt > 0 && isset($request['data']['reference'])) {
                $originalReference = $request['data']['reference'];
                $baseReference = explode('_', $originalReference);
                array_pop($baseReference); // Remove the random part
                $baseReference = implode('_', $baseReference);
                
                // Create new unique reference with timestamp and random string
                $newReference = $baseReference . '_' . time() . '_' . Str::random(4);
                $request['data']['reference'] = $newReference;
                
                // Also update the date to make the request more unique
                $request['data']['date'] = now()->format('Y-m-d H:i:s');
                
                // Add a small amount variation to avoid duplicate detection based on amount
                if (isset($request['data']['amount'])) {
                    $originalAmount = $request['data']['amount'];
                    $variation = rand(1, 5); // Add 1-5 shillings variation
                    $request['data']['amount'] = $originalAmount + $variation;
                    
                    Log::info('Generated unique data for retry attempt', [
                        'attempt' => $attempt + 1,
                        'original_reference' => $originalReference,
                        'new_reference' => $newReference,
                        'original_amount' => $originalAmount,
                        'new_amount' => $request['data']['amount'],
                        'amount_variation' => $variation
                    ]);
                } else {
                    Log::info('Generated unique reference for retry attempt', [
                        'attempt' => $attempt + 1,
                        'original_reference' => $originalReference,
                        'new_reference' => $newReference
                    ]);
                }
            }
            
            try {
                // Log attempt details
                Log::info('Aggregator request attempt', [
                    'attempt' => $attempt + 1,
                    'max_retries' => $maxRetries,
                    'url' => $request['url'],
                    'method' => $request['method'],
                    'timeout' => $request['timeout'],
                    'headers' => $this->sanitizeHeaders($request['headers']),
                    'data' => $this->sanitizeLogData($request['data'] ?? null)
                ]);
                
                $response = Http::timeout($request['timeout'])
                               ->withHeaders($request['headers']);
                
                if (isset($request['data'])) {
                    $response = $response->post($request['url'], $request['data']);
                } else {
                    $response = $response->get($request['url'], $request['params']);
                }
                
                $attemptTime = microtime(true) - $attemptStartTime;
                
                // Log response details
                Log::info('Aggregator response received', [
                    'attempt' => $attempt + 1,
                    'url' => $request['url'],
                    'status_code' => $response->status(),
                    'response_time' => $attemptTime,
                    'response_headers' => $this->sanitizeHeaders($response->headers()),
                    'response_body' => $this->sanitizeLogData($response->json()),
                    'successful' => $response->successful()
                ]);
                
                if ($response->successful()) {
                    return [
                        'success' => true,
                        'data' => $response->json(),
                        'status_code' => $response->status(),
                        'headers' => $response->headers(),
                        'response_time' => $attemptTime
                    ];
                }
                
                // Log unsuccessful response details
                Log::warning('Aggregator returned unsuccessful response', [
                    'attempt' => $attempt + 1,
                    'url' => $request['url'],
                    'status_code' => $response->status(),
                    'response_time' => $attemptTime,
                    'response_body' => $this->sanitizeLogData($response->json()),
                    'error_message' => $response->body()
                ]);
                
                // Check if this is a duplicate request error - don't retry these
                $responseBody = $response->json();
                if ($response->status() === 409 && 
                    isset($responseBody['reason']) && 
                    $responseBody['reason'] === 'DUPLICATE_REQUEST') {
                    
                    Log::warning('Duplicate request detected, stopping retries', [
                        'attempt' => $attempt + 1,
                        'status_code' => $response->status(),
                        'reason' => $responseBody['reason'],
                        'reference' => $request['data']['reference'] ?? 'unknown'
                    ]);
                    
                    // Return the error immediately without retrying
                    return [
                        'success' => false,
                        'data' => $responseBody,
                        'status_code' => $response->status(),
                        'headers' => $response->headers(),
                        'response_time' => $attemptTime,
                        'error' => 'Duplicate request detected by aggregator'
                    ];
                }
                
                // If response is not successful, throw exception for retry
                throw new \Exception("Aggregator returned status: " . $response->status() . " - " . $response->body());
                
            } catch (\Exception $e) {
                $attempt++;
                $attemptTime = microtime(true) - $attemptStartTime;
                
                // Log detailed error information
                Log::error('Aggregator request failed', [
                    'attempt' => $attempt,
                    'max_retries' => $maxRetries,
                    'url' => $request['url'],
                    'method' => $request['method'],
                    'error' => $e->getMessage(),
                    'error_type' => get_class($e),
                    'response_time' => $attemptTime,
                    'will_retry' => $attempt < $maxRetries
                ]);
                
                if ($attempt >= $maxRetries) {
                    // Log final failure
                    Log::critical('Aggregator request failed after all retries', [
                        'url' => $request['url'],
                        'method' => $request['method'],
                        'total_attempts' => $attempt,
                        'final_error' => $e->getMessage(),
                        'total_time' => microtime(true) - $attemptStartTime
                    ]);
                    
                    throw $e;
                }
                
                // Log retry attempt
                $backoffTime = pow(2, $attempt - 1);
                Log::info('Retrying aggregator request', [
                    'attempt' => $attempt + 1,
                    'backoff_time' => $backoffTime,
                    'url' => $request['url']
                ]);
                
                // Exponential backoff
                sleep($backoffTime);
            }
        }
    }
    
    /**
     * Send webhook notification to client
     */
    public function sendWebhookNotification(Transaction $transaction)
    {
        if (!$transaction->webhook_url) {
            return;
        }
        
        try {
            $webhookData = [
                'transaction_id' => $transaction->transaction_id,
                'external_transaction_id' => $transaction->external_transaction_id,
                'client_reference' => $transaction->client_reference,
                'status' => $transaction->status,
                'aggregator_status' => $transaction->aggregator_status,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency,
                'customer_phone' => $transaction->customer_phone,
                'mobile_network' => $transaction->mobile_network,
                'description' => $transaction->description,
                'timestamp' => now()->toISOString(),
                'response' => $transaction->final_response
            ];
            
            $response = Http::timeout(10)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($transaction->webhook_url, $webhookData);
            
            // Update webhook response in transaction
            $transaction->updateWebhookResponse($response->body(), $response->status());
            
            Log::info('Webhook notification sent successfully', [
                'transaction_id' => $transaction->transaction_id,
                'webhook_url' => $transaction->webhook_url,
                'status_code' => $response->status()
            ]);
                
        } catch (\Exception $e) {
            Log::error('Webhook notification failed', [
                'transaction_id' => $transaction->transaction_id,
                'webhook_url' => $transaction->webhook_url,
                'error' => $e->getMessage()
            ]);
            
            // Update webhook response with error
            $transaction->updateWebhookResponse(['error' => $e->getMessage()], 0);
        }
    }
    
    /**
     * Get service health status
     */
    public function getServiceHealth(ServiceMapping $serviceMapping)
    {
        $cacheKey = "service_health:{$serviceMapping->id}";
        
        return Cache::remember($cacheKey, 300, function () use ($serviceMapping) {
            try {
                $request = $this->prepareAggregatorRequest($serviceMapping, []);
                $request['url'] = $serviceMapping->aggregator->api_endpoint . '/health';
                $request['method'] = 'GET';
                
                $response = Http::timeout(5)->get($request['url']);
                
                return [
                    'status' => $response->successful() ? 'healthy' : 'unhealthy',
                    'response_time' => $response->handlerStats()['total_time'] ?? null,
                    'last_check' => now()->toISOString()
                ];
                
            } catch (\Exception $e) {
                return [
                    'status' => 'unhealthy',
                    'error' => $e->getMessage(),
                    'last_check' => now()->toISOString()
                ];
            }
        });
    }
    
    /**
     * Get service statistics
     */
    public function getServiceStats(ServiceMapping $serviceMapping, $period = '24h')
    {
        $query = $serviceMapping->transactions();
        
        switch ($period) {
            case '1h':
                $query->where('created_at', '>=', now()->subHour());
                break;
            case '24h':
                $query->where('created_at', '>=', now()->subDay());
                break;
            case '7d':
                $query->where('created_at', '>=', now()->subWeek());
                break;
            case '30d':
                $query->where('created_at', '>=', now()->subMonth());
                break;
        }
        
        $total = $query->count();
        $successful = $query->where('status', 'success')->count();
        $failed = $query->where('status', 'failed')->count();
        $avgResponseTime = $query->whereNotNull('response_time')->avg('response_time');
        
        return [
            'total_requests' => $total,
            'successful_requests' => $successful,
            'failed_requests' => $failed,
            'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
            'average_response_time' => round($avgResponseTime * 1000, 2), // in milliseconds
            'period' => $period
        ];
    }
    
    /**
     * Transform aggregator response based on service type
     */
    protected function transformAggregatorResponse(ServiceMapping $serviceMapping, $aggregatorResponse)
    {
        // Special handling for statement services that return arrays
        if ($serviceMapping->service->code === 'COLLECTION_STATEMENT') {
            return $this->transformStatementResponse($aggregatorResponse);
        }
        
        // Default transformation for single transaction responses
        return $serviceMapping->transformResponse($aggregatorResponse);
    }

    /**
     * Fill response fields from request when aggregator does not return them (e.g. Tembo omits amount).
     */
    protected function fillResponseFromRequest(array $response, array $requestData): array
    {
        if (($response['amount'] ?? null) === null && isset($requestData['amount'])) {
            $response['amount'] = $requestData['amount'];
        }
        if (($response['currency'] ?? null) === null && isset($requestData['currency'])) {
            $response['currency'] = $requestData['currency'];
        }
        if (($response['customer_phone'] ?? null) === null && isset($requestData['customer_phone'])) {
            $response['customer_phone'] = $requestData['customer_phone'];
        }
        if (($response['mobile_network'] ?? null) === null && isset($requestData['mobile_network'])) {
            $response['mobile_network'] = $requestData['mobile_network'];
        }
        if (($response['description'] ?? null) === null && isset($requestData['description'])) {
            $response['description'] = $requestData['description'];
        }
        return $response;
    }
    
    /**
     * Transform statement response (array of transactions)
     */
    protected function transformStatementResponse($aggregatorResponse)
    {
        // If response is not an array, return as is
        if (!is_array($aggregatorResponse)) {
            return $aggregatorResponse;
        }
        
        // Transform each statement record
        $transformedRecords = [];
        foreach ($aggregatorResponse as $record) {
            $transformedRecords[] = [
                'account_number' => '9000725736', // Tembo collection account
                'transaction_type' => $record['debitOrCredit'] ?? 'CR',
                'transaction_reference' => $record['tranRefNo'] ?? null,
                'description' => $record['narration'] ?? null,
                'transaction_date' => $record['valueDate'] ?? $record['txnDate'] ?? null,
                'amount_credited' => $record['amountCredited'] ?? 0,
                'amount_debited' => $record['amountDebited'] ?? 0,
                'balance' => $record['balance'] ?? 0,
                'cba_reference' => $record['cbaRefNo'] ?? null
            ];
        }
        
        return $transformedRecords;
    }
    
    /**
     * Extract network provider from mobile network code
     */
    protected function extractNetworkProvider($mobileNetwork)
    {
        $providers = [
            'TZ-AIRTEL-C2B' => 'Airtel Money',
            'TZ-TIGO-C2B' => 'Tigo Pesa',
            'TZ-MPESA-C2B' => 'M-Pesa',
            'TZ-HALOPESA-C2B' => 'HaloPesa'
        ];
        
        return $providers[$mobileNetwork] ?? 'Unknown';
    }

    /**
     * Sanitize data for logging (remove sensitive information)
     */
    protected function sanitizeLogData($data)
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
                    $sanitized[$key] = is_array($value) ? $this->sanitizeLogData($value) : $value;
                }
            }
            
            return $sanitized;
        }
        
        return $data;
    }
    
    /**
     * Sanitize headers for logging (remove sensitive information)
     */
    protected function sanitizeHeaders($headers)
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
} 