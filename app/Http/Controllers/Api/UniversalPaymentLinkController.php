<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\UniversalPaymentLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UniversalPaymentLinkController extends Controller
{
    protected $universalPaymentLinkService;

    public function __construct(UniversalPaymentLinkService $universalPaymentLinkService)
    {
        $this->universalPaymentLinkService = $universalPaymentLinkService;
    }

    public function generateUniversal(Request $request)
    {
        Log::info('Initiating universal payment link generation request', [
            'request_data' => $request->all()
        ]);

        try {
            Log::info('Authenticating client for payment link generation');
            $client = $this->authenticateClient($request);

            if (!$client) {
                Log::warning('Client authentication failed', [
                    'headers' => $request->headers->all()
                ]);

                return response()->json([
                    'status' => 'error',
                    'error_code' => 'AUTH_001',
                    'message' => 'Authentication failed',
                    'details' => 'Invalid API key or secret',
                    'timestamp' => now()->toISOString(),
                    'request_id' => 'req_' . uniqid()
                ], 401);
            }

            Log::info('Client authenticated successfully', ['client_id' => $client->id]);

            Log::info('Validating request data');
            $validator = Validator::make($request->all(), [
                'description' => 'required|string|max:255',
                'target' => 'required|in:individual,public',
                'items' => 'required|array|min:1',
                'items.*.type' => 'required|in:service,product',
                'items.*.product_service_reference' => 'nullable|string|max:100',
                'items.*.product_service_name' => 'required|string|max:255',
                'items.*.amount' => 'required|numeric|min:100|max:1000000',
                'items.*.description' => 'nullable|string|max:500',
                'items.*.is_required' => 'nullable|boolean',
                'items.*.allow_partial' => 'nullable|boolean',
                'items.*.minimum_amount' => 'nullable|numeric|min:100',
                'items.*.quantity' => 'nullable|integer|min:1',
                'items.*.subcategory' => 'nullable|string|max:100',
                'reference' => 'nullable|string|max:100',
                'customer_reference' => 'nullable|string|max:100',
                'customer_name' => 'nullable|string|max:255',
                'customer_phone' => 'nullable|regex:/^255[0-9]{9}$/',
                'customer_email' => 'nullable|email',
                'currency' => 'nullable|string|size:3',
                'allowed_networks' => 'nullable|array',
                'allowed_networks.*' => 'string|in:TZ-AIRTEL-C2B,TZ-TIGO-C2B,TZ-MPESA-C2B,TZ-HALOPESA-C2B',
                'expires_at' => 'nullable|date|after:now',
                'max_uses' => 'nullable|integer|min:1|max:1000',
                'webhook_url' => 'nullable|url',
                'success_url' => 'nullable|url',
                'failure_url' => 'nullable|url',
                'cancel_url' => 'nullable|url',
            ]);

            if ($request->input('target') === 'individual') {
                Log::info('Adding additional validation rules for individual target');
                $validator->addRules([
                    'customer_name' => 'required|string|max:255',
                    'customer_phone' => 'required|regex:/^255[0-9]{9}$/',
                ]);
            }

            if ($validator->fails()) {
                Log::warning('Validation failed', [
                    'errors' => $validator->errors(),
                    'client_id' => $client->id
                ]);

                return response()->json([
                    'status' => 'error',
                    'error_code' => 'VALIDATION_001',
                    'message' => 'Validation failed',
                    'details' => 'Request data validation failed',
                    'timestamp' => now()->toISOString(),
                    'request_id' => 'req_' . uniqid(),
                    'errors' => $validator->errors(),
                    'suggestions' => $this->getValidationSuggestions($request->input('target'))
                ], 400);
            }

            Log::info('Validation passed, generating universal payment link', [
                'client_id' => $client->id
            ]);

            $result = $this->universalPaymentLinkService->generateUniversalPaymentLink(
                $request->all(),
                $client
            );

            if (!$result['success']) {
                Log::error('Payment link generation failed', [
                    'error' => $result['error'],
                    'client_id' => $client->id
                ]);

                return response()->json([
                    'status' => 'error',
                    'error_code' => 'GENERATION_001',
                    'message' => 'Payment link generation failed',
                    'details' => $result['error'],
                    'timestamp' => now()->toISOString(),
                    'request_id' => 'req_' . uniqid()
                ], 400);
            }

            Log::info('Universal payment link generated', [
                'client_id' => $client->id,
                'link_id' => $result['data']['link_id'],
                'target_type' => $result['data']['target_type'],
                'is_public' => $result['data']['is_public']
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Universal payment link generated successfully',
                'data' => $result['data'],
                'timestamp' => now()->toISOString(),
                'request_id' => 'req_' . uniqid()
            ], 201);

        } catch (\Exception $e) {
            Log::error('Exception during universal payment link generation', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'error_code' => 'INTERNAL_001',
                'message' => 'Internal server error',
                'details' => 'An unexpected error occurred',
                'timestamp' => now()->toISOString(),
                'request_id' => 'req_' . uniqid()
            ], 500);
        }
    }

    public function getUniversalPaymentLink($shortCode)
    {
        Log::info('Fetching universal payment link details', ['short_code' => $shortCode]);

        try {
            $paymentLink = $this->universalPaymentLinkService->getUniversalPaymentLink($shortCode);

            if (!$paymentLink) {
                Log::warning('Payment link not found', ['short_code' => $shortCode]);

                return response()->json([
                    'status' => 'error',
                    'error_code' => 'NOT_FOUND_001',
                    'message' => 'Payment link not found',
                    'details' => 'The requested payment link does not exist or is not accessible',
                    'timestamp' => now()->toISOString(),
                    'request_id' => 'req_' . uniqid()
                ], 404);
            }

            Log::info('Payment link found', ['link_id' => $paymentLink->link_id]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment link details retrieved successfully',
                'data' => [
                    'link_id' => $paymentLink->link_id,
                    'short_code' => $paymentLink->short_code,
                    'payment_url' => $paymentLink->payment_url,
                    'qr_code_data' => $paymentLink->qr_code_data,
                    'target_type' => $paymentLink->metadata['target_type'] ?? 'individual',
                    'is_public' => $paymentLink->metadata['is_public_link'] ?? false,
                    'description' => $paymentLink->description,
                    'total_amount' => $paymentLink->total_items_amount,
                    'currency' => $paymentLink->currency,
                    'customer_reference' => $paymentLink->metadata['customer_reference'] ?? null,
                    'customer_name' => $paymentLink->customer_name,
                    'customer_phone' => $paymentLink->customer_phone,
                    'customer_email' => $paymentLink->customer_email,
                    'items' => $paymentLink->items->map(function($item) {
                        return [
                            'item_code' => $item->item_code,
                            'type' => $item->category,
                            'product_service_reference' => $item->metadata['product_service_reference'] ?? null,
                            'product_service_name' => $item->item_name,
                            'description' => $item->item_description,
                            'amount' => $item->amount,
                            'paid_amount' => $item->paid_amount,
                            'remaining_amount' => $item->remaining_amount,
                            'payment_percentage' => $item->payment_percentage,
                            'status' => $item->status,
                            'is_required' => $item->is_required,
                            'allow_partial' => $item->allow_partial,
                            'minimum_amount' => $item->minimum_amount,
                        ];
                    }),
                    'expires_at' => $paymentLink->expires_at?->toISOString(),
                    'max_uses' => $paymentLink->max_uses,
                    'current_uses' => $paymentLink->current_uses,
                    'is_reusable' => $paymentLink->is_reusable,
                    'allowed_networks' => $paymentLink->allowed_networks_array,
                    'created_at' => $paymentLink->created_at->toISOString(),
                    'last_viewed_at' => $paymentLink->last_viewed_at?->toISOString(),
                ],
                'timestamp' => now()->toISOString(),
                'request_id' => 'req_' . uniqid()
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving universal payment link', [
                'error' => $e->getMessage(),
                'short_code' => $shortCode,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'error_code' => 'INTERNAL_001',
                'message' => 'Internal server error',
                'details' => 'An unexpected error occurred',
                'timestamp' => now()->toISOString(),
                'request_id' => 'req_' . uniqid()
            ], 500);
        }
    }

    public function getUniversalPaymentLinkStats($shortCode, Request $request)
    {
        Log::info('Fetching stats for payment link', ['short_code' => $shortCode]);

        try {
            $client = $this->authenticateClient($request);

            if (!$client) {
                Log::warning('Client authentication failed for stats request', [
                    'headers' => $request->headers->all()
                ]);

                return response()->json([
                    'status' => 'error',
                    'error_code' => 'AUTH_001',
                    'message' => 'Authentication failed',
                    'details' => 'Invalid API key or secret',
                    'timestamp' => now()->toISOString(),
                    'request_id' => 'req_' . uniqid()
                ], 401);
            }

            Log::info('Client authenticated for stats request', ['client_id' => $client->id]);

            $paymentLink = \App\Models\PaymentLink::where('short_code', $shortCode)
                ->where('client_id', $client->id)
                ->first();

            if (!$paymentLink) {
                Log::warning('Payment link not found for stats', [
                    'short_code' => $shortCode,
                    'client_id' => $client->id
                ]);

                return response()->json([
                    'status' => 'error',
                    'error_code' => 'NOT_FOUND_001',
                    'message' => 'Payment link not found',
                    'details' => 'The requested payment link does not exist or belongs to another client',
                    'timestamp' => now()->toISOString(),
                    'request_id' => 'req_' . uniqid()
                ], 404);
            }

            $stats = $this->universalPaymentLinkService->getUniversalPaymentLinkStats($paymentLink);

            Log::info('Stats retrieved successfully', ['link_id' => $paymentLink->link_id]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment link statistics retrieved successfully',
                'data' => $stats,
                'timestamp' => now()->toISOString(),
                'request_id' => 'req_' . uniqid()
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving payment link stats', [
                'error' => $e->getMessage(),
                'short_code' => $shortCode,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'error_code' => 'INTERNAL_001',
                'message' => 'Internal server error',
                'details' => 'An unexpected error occurred',
                'timestamp' => now()->toISOString(),
                'request_id' => 'req_' . uniqid()
            ], 500);
        }
    }

    private function authenticateClient(Request $request)
    {
        $apiKey = $request->header('X-API-Key');
        $apiSecret = $request->header('X-API-Secret');

        Log::debug('Authenticating client', [
            'has_api_key' => isset($apiKey),
            'has_api_secret' => isset($apiSecret)
        ]);

        if (!$apiKey || !$apiSecret) {
            return null;
        }

        $client = Client::where('api_key', $apiKey)
            ->where('api_secret', $apiSecret)
            ->where('status', true)
            ->first();

        if ($client) {
            Log::info('Client authenticated', ['client_id' => $client->id]);
        } else {
            Log::warning('Client credentials invalid or client inactive');
        }

        return $client;
    }

    private function getValidationSuggestions($target)
    {
        Log::debug('Generating validation suggestions for target', ['target' => $target]);

        if ($target === 'individual') {
            return [
                'Provide customer_name and customer_phone for individual targets',
                'Use target "public" if customer info will be collected later'
            ];
        }

        return [
            'Use target "individual" for specific customers',
            'Use target "public" for general collections'
        ];
    }
}
