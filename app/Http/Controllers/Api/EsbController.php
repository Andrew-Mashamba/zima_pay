<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ServiceMapping;
use App\Models\Transaction;
use App\Services\EsbService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class EsbController extends Controller
{
    protected $esbService;

    public function __construct(EsbService $esbService)
    {
        $this->esbService = $esbService;
    }

    /**
     * Handle incoming ESB requests
     */
    public function handleRequest(Request $request, $serviceCode)
    {
        try {
            // 1. Authenticate client
            $client = $this->authenticateClient($request);
            if (!$client) {
                return response()->json([
                    'status' => 'error',
                    'error_code' => 'AUTH_001',
                    'message' => 'Invalid API credentials',
                    'details' => 'The provided API key or secret is invalid or inactive',
                    'timestamp' => now()->toISOString(),
                    'request_id' => uniqid('req_'),
                    'suggestions' => [
                        'Verify your API key and secret are correct',
                        'Ensure your client account is active',
                        'Check that you are using the correct authentication headers'
                    ]
                ], 401);
            }

            // 2. Rate limiting check
            if (!$this->checkRateLimit($client, $serviceCode)) {
                return response()->json([
                    'status' => 'error',
                    'error_code' => 'RATE_001',
                    'message' => 'Rate limit exceeded',
                    'details' => 'You have exceeded the allowed number of requests for this service',
                    'timestamp' => now()->toISOString(),
                    'request_id' => uniqid('req_'),
                    'rate_limit_info' => [
                        'limit' => 100,
                        'window' => '1 minute',
                        'reset_time' => now()->addMinute()->toISOString()
                    ],
                    'suggestions' => [
                        'Reduce the frequency of your requests',
                        'Implement request throttling in your application',
                        'Contact support if you need a higher rate limit'
                    ]
                ], 429);
            }

            // 3. Find service mapping
            $serviceMapping = $this->findServiceMapping($client, $serviceCode);
            if (!$serviceMapping) {
                return response()->json([
                    'status' => 'error',
                    'error_code' => 'SERVICE_001',
                    'message' => 'Service not available',
                    'details' => 'The requested service is not available for your client account',
                    'timestamp' => now()->toISOString(),
                    'request_id' => uniqid('req_'),
                    'service_info' => [
                        'requested_service' => $serviceCode,
                        'available_services' => $client->services()->pluck('code')->toArray()
                    ],
                    'suggestions' => [
                        'Verify the service code is correct',
                        'Contact support to enable this service for your account',
                        'Check your service mapping configuration'
                    ]
                ], 404);
            }

            // 4. Validate request data
            $validation = $this->validateRequest($request, $serviceMapping);
            if (!$validation['valid']) {
                return response()->json([
                    'status' => 'error',
                    'error_code' => 'VALIDATION_001',
                    'message' => 'Invalid request data',
                    'details' => 'One or more required fields are missing or invalid',
                    'timestamp' => now()->toISOString(),
                    'request_id' => uniqid('req_'),
                    'errors' => $validation['errors'],
                    'validation_rules' => $this->getValidationRules($serviceMapping),
                    'suggestions' => [
                        'Review the validation errors below',
                        'Ensure all required fields are provided',
                        'Check field formats and value ranges'
                    ]
                ], 400);
            }

            // 5. Create transaction record
            $transaction = $this->createTransaction($client, $serviceMapping, $request);

            // 6. Process request through ESB
            $result = $this->esbService->processRequest($serviceMapping, $request->all(), $transaction);

            // 7. Update transaction with result
            $this->updateTransaction($transaction, $result);

            // 8. Return response
            return response()->json($result['response'], $result['status_code']);

        } catch (\Exception $e) {
            $requestId = uniqid('req_');
            
            Log::error('ESB Request Error', [
                'request_id' => $requestId,
                'service_code' => $serviceCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'error_code' => 'INTERNAL_001',
                'message' => 'Internal server error',
                'details' => 'An unexpected error occurred while processing your request',
                'timestamp' => now()->toISOString(),
                'request_id' => $requestId,
                'suggestions' => [
                    'Try your request again in a few moments',
                    'If the problem persists, contact support with the request ID',
                    'Check our service status page for any ongoing issues'
                ]
            ], 500);
        }
    }

    /**
     * Authenticate client using API key
     */
    protected function authenticateClient(Request $request)
    {
        $apiKey = $request->header('X-API-Key') ?: $request->input('api_key');
        $apiSecret = $request->header('X-API-Secret') ?: $request->input('api_secret');

        if (!$apiKey || !$apiSecret) {
            return null;
        }

        return Client::where('api_key', $apiKey)
                    ->where('api_secret', $apiSecret)
                    ->where('status', true)
                    ->first();
    }

    /**
     * Check rate limiting for client and service
     */
    protected function checkRateLimit(Client $client, string $serviceCode)
    {
        $cacheKey = "rate_limit:{$client->id}:{$serviceCode}";
        $currentCount = Cache::get($cacheKey, 0);

        // Get rate limit from client-service relationship
        $service = $client->services()->where('code', $serviceCode)->first();
        $rateLimit = $service?->pivot?->rate_limit ?? 100; // Default 100 requests per minute

        if ($currentCount >= $rateLimit) {
            return false;
        }

        Cache::put($cacheKey, $currentCount + 1, 60); // 1 minute window
        return true;
    }

    /**
     * Find appropriate service mapping
     */
    protected function findServiceMapping(Client $client, string $serviceCode)
    {
        return ServiceMapping::where('client_id', $client->id)
                            ->whereHas('service', function ($query) use ($serviceCode) {
                                $query->where('code', $serviceCode);
                            })
                            ->where('status', true)
                            ->orderBy('priority', 'desc')
                            ->first();
    }

    /**
     * Validate request data against service mapping
     */
    protected function validateRequest(Request $request, ServiceMapping $serviceMapping)
    {
        // Get required fields from request mapping
        $requiredFields = array_keys($serviceMapping->request_mapping);
        
        $rules = [];
        foreach ($requiredFields as $field) {
            $rules[$field] = 'required';
        }

        // Add specific validation rules for money collection service
        if ($serviceMapping->service->code === 'MONEY_COLLECTION') {
            $rules['customer_phone'] = 'required|regex:/^255[0-9]{9}$/';
            $rules['mobile_network'] = 'required|in:TZ-AIRTEL-C2B,TZ-TIGO-C2B,TZ-MPESA-C2B,TZ-HALOPESA-C2B';
            $rules['amount'] = 'required|numeric|min:100|max:1000000';
            $rules['description'] = 'required|string|max:255';
            $rules['reference'] = 'required|string|max:100';
            $rules['date'] = 'required|date';
            $rules['webhook_url'] = 'required|url';
        }

        $validator = Validator::make($request->all(), $rules);

        return [
            'valid' => !$validator->fails(),
            'errors' => $validator->errors()
        ];
    }

    /**
     * Create transaction record
     */
    protected function createTransaction(Client $client, ServiceMapping $serviceMapping, Request $request)
    {
        $requestData = $request->all();
        
        // Extract transaction details from request
        $amount = $requestData['amount'] ?? null;
        $customerPhone = $requestData['customer_phone'] ?? null;
        $mobileNetwork = $requestData['mobile_network'] ?? null;
        $description = $requestData['description'] ?? null;
        $reference = $requestData['reference'] ?? null;
        $webhookUrl = $requestData['webhook_url'] ?? null;

        return Transaction::create([
            'transaction_id' => 'TXN_' . Str::random(16),
            'client_reference' => $reference,
            'client_id' => $client->id,
            'aggregator_id' => $serviceMapping->aggregator_id,
            'service_id' => $serviceMapping->service_id,
            'service_mapping_id' => $serviceMapping->id,
            'amount' => $amount,
            'currency' => 'TZS',
            'customer_phone' => $customerPhone,
            'mobile_network' => $mobileNetwork,
            'network_provider' => $this->extractNetworkProvider($mobileNetwork),
            'description' => $description,
            'narration' => $description,
            'channel' => $mobileNetwork,
            'payment_method' => 'mobile_money',
            'original_request' => $requestData,
            'request_data' => $requestData,
            'status' => 'pending',
            'aggregator_status' => 'pending',
            'client_status' => 'pending',
            'webhook_url' => $webhookUrl,
            'session_id' => session()->getId(),
            'correlation_id' => 'TXN_' . Str::random(16),
            'trace_id' => uniqid('trace_'),
            'tags' => ['esb', 'mobile_money', $serviceMapping->service->code],
            'risk_level' => $this->assessRiskLevel($amount, $customerPhone),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_size' => strlen(json_encode($requestData)),
            'metadata' => [
                'service_code' => $serviceMapping->service->code,
                'aggregator_code' => $serviceMapping->aggregator->code,
                'request_headers' => $request->headers->all(),
                'client_ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]
        ]);
    }

    /**
     * Update transaction with result
     */
    protected function updateTransaction(Transaction $transaction, array $result)
    {
        $updateData = [
            'response_data' => $result['response'],
            'final_response' => $result['response'],
            'status' => $result['success'] ? 'success' : 'failed',
            'client_status' => $result['success'] ? 'success' : 'failed',
            'error_message' => $result['error'] ?? null,
            'response_time' => $result['response_time'] ?? null,
            'total_processing_time' => $result['processing_time'] ?? null,
            'response_size' => strlen(json_encode($result['response'])),
        ];

        // Extract aggregator response details if available
        if (isset($result['aggregator_response'])) {
            $updateData['aggregator_response'] = $result['aggregator_response'];
            $updateData['aggregator_status'] = $result['aggregator_response']['success'] ? 'success' : 'failed';
            $updateData['aggregator_response_time'] = $result['aggregator_response']['processing_time'] ?? null;
        }

        // Extract transformed request/response if available
        if (isset($result['transformed_request'])) {
            $updateData['transformed_request'] = $result['transformed_request'];
        }

        if (isset($result['transformed_response'])) {
            $updateData['transformed_response'] = $result['transformed_response'];
        }

        // Extract external transaction ID if available
        if (isset($result['response']['transaction_id'])) {
            $updateData['external_transaction_id'] = $result['response']['transaction_id'];
        }

        if (isset($result['response']['transactionId'])) {
            $updateData['external_transaction_id'] = $result['response']['transactionId'];
        }

        // Extract aggregator reference if available
        if (isset($result['response']['reference'])) {
            $updateData['aggregator_reference'] = $result['response']['reference'];
        }

        if (isset($result['response']['transactionRef'])) {
            $updateData['aggregator_reference'] = $result['response']['transactionRef'];
        }

        $transaction->update($updateData);

        // Add audit trail
        $transaction->addAuditTrail('transaction_processed', [
            'status' => $result['success'] ? 'success' : 'failed',
            'processing_time' => $result['processing_time'] ?? null,
            'error' => $result['error'] ?? null
        ]);
    }

    /**
     * Health check endpoint
     */
    public function health()
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0'
        ]);
    }

    /**
     * Get available services for authenticated client
     */
    public function getServices(Request $request)
    {
        $client = $this->authenticateClient($request);
        if (!$client) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get services directly through client-service relationship
        $services = $client->services()
                          ->where('services.status', true)
                          ->get()
                          ->map(function ($service) {
                              return [
                                  'code' => $service->code,
                                  'name' => $service->name,
                                  'description' => $service->description,
                                  'aggregator' => $service->aggregator->name ?? 'Unknown',
                                  'rate_limit' => $service->pivot->rate_limit ?? 100,
                                  'quota' => $service->pivot->quota ?? null,
                              ];
                          });

        return response()->json(['services' => $services]);
    }

    /**
     * Extract network provider from mobile network code
     */
    protected function extractNetworkProvider($mobileNetwork)
    {
        return match($mobileNetwork) {
            'TZ-AIRTEL-C2B' => 'Airtel',
            'TZ-TIGO-C2B' => 'Tigo',
            'TZ-MPESA-C2B' => 'M-Pesa',
            'TZ-HALOPESA-C2B' => 'HaloPesa',
            default => 'Unknown'
        };
    }

    /**
     * Assess risk level based on amount and customer phone
     */
    protected function assessRiskLevel($amount, $customerPhone)
    {
        // High risk for large amounts
        if ($amount > 100000) {
            return 'high';
        }

        // Medium risk for medium amounts
        if ($amount > 50000) {
            return 'medium';
        }

        // Low risk for small amounts
        return 'low';
    }
    
    /**
     * Get validation rules for a service mapping
     */
    protected function getValidationRules(ServiceMapping $serviceMapping)
    {
        if ($serviceMapping->service->code === 'MONEY_COLLECTION') {
            return [
                'customer_phone' => [
                    'required' => true,
                    'format' => '255XXXXXXXXX (9 digits after 255)',
                    'example' => '255692410353'
                ],
                'mobile_network' => [
                    'required' => true,
                    'allowed_values' => ['TZ-AIRTEL-C2B', 'TZ-TIGO-C2B', 'TZ-MPESA-C2B', 'TZ-HALOPESA-C2B']
                ],
                'amount' => [
                    'required' => true,
                    'type' => 'numeric',
                    'min' => 100,
                    'max' => 1000000,
                    'currency' => 'TZS'
                ],
                'description' => [
                    'required' => true,
                    'type' => 'string',
                    'max_length' => 255
                ],
                'reference' => [
                    'required' => true,
                    'type' => 'string',
                    'max_length' => 100
                ],
                'date' => [
                    'required' => true,
                    'format' => 'Y-m-d H:i:s',
                    'example' => '2025-07-19 18:05:00'
                ],
                'webhook_url' => [
                    'required' => true,
                    'type' => 'URL',
                    'example' => 'https://webhook.site/your-webhook'
                ]
            ];
        }
        
        return [];
    }
} 