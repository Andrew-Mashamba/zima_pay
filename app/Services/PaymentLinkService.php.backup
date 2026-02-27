<?php

namespace App\Services;

use App\Models\PaymentLink;
use App\Models\Client;
use App\Models\ServiceMapping;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PaymentLinkService
{
    /**
     * Generate a new payment link
     */
    public function generatePaymentLink(array $data, Client $client): array
    {
        try {
            // Validate input data
            $validation = $this->validatePaymentLinkData($data);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $validation['errors']
                ];
            }

            // Get service mapping for money collection
            $serviceMapping = ServiceMapping::where('client_id', $client->id)
                ->whereHas('service', function($q) {
                    $q->where('code', 'MONEY_COLLECTION');
                })->first();

            if (!$serviceMapping) {
                return [
                    'success' => false,
                    'error' => 'Money collection service not available for this client'
                ];
            }

            // Create payment link
            $paymentLink = PaymentLink::create([
                'client_reference' => $data['reference'] ?? 'LINK_' . Str::random(16),
                'client_id' => $client->id,
                'service_mapping_id' => $serviceMapping->id,
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'TZS',
                'description' => $data['description'],
                'narration' => $data['narration'] ?? $data['description'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'payment_method' => $data['payment_method'] ?? 'mobile_money',
                'allowed_networks' => $data['allowed_networks'] ?? [
                    'TZ-AIRTEL-C2B',
                    'TZ-TIGO-C2B',
                    'TZ-MPESA-C2B',
                    'TZ-HALOPESA-C2B'
                ],
                'allow_partial_payment' => $data['allow_partial_payment'] ?? false,
                'minimum_amount' => $data['minimum_amount'] ?? null,
                'maximum_amount' => $data['maximum_amount'] ?? null,
                'expires_at' => $data['expires_at'] ? Carbon::parse($data['expires_at']) : null,
                'max_uses' => $data['max_uses'] ?? null,
                'is_reusable' => $data['is_reusable'] ?? false,
                'is_public' => $data['is_public'] ?? true,
                'metadata' => $data['metadata'] ?? null,
                'settings' => $data['settings'] ?? null,
                'webhook_url' => $data['webhook_url'] ?? $client->webhook_url,
                'success_url' => $data['success_url'] ?? null,
                'failure_url' => $data['failure_url'] ?? null,
                'cancel_url' => $data['cancel_url'] ?? null,
                'created_by' => $data['created_by'] ?? 'api',
            ]);

            Log::info('Payment link generated', [
                'link_id' => $paymentLink->link_id,
                'short_code' => $paymentLink->short_code,
                'client_id' => $client->id,
                'amount' => $paymentLink->amount
            ]);

            return [
                'success' => true,
                'data' => [
                    'link_id' => $paymentLink->link_id,
                    'short_code' => $paymentLink->short_code,
                    'payment_url' => $paymentLink->payment_url,
                    'qr_code_data' => $paymentLink->qr_code_data,
                    'amount' => $paymentLink->amount,
                    'currency' => $paymentLink->currency,
                    'description' => $paymentLink->description,
                    'expires_at' => $paymentLink->expires_at?->toISOString(),
                    'max_uses' => $paymentLink->max_uses,
                    'is_reusable' => $paymentLink->is_reusable,
                    'allowed_networks' => $paymentLink->allowed_networks_array,
                    'created_at' => $paymentLink->created_at->toISOString()
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Payment link generation failed', [
                'error' => $e->getMessage(),
                'client_id' => $client->id,
                'data' => $data
            ]);

            return [
                'success' => false,
                'error' => 'Payment link generation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get payment link by short code
     */
    public function getPaymentLink(string $shortCode): ?PaymentLink
    {
        $paymentLink = PaymentLink::where('short_code', $shortCode)
            ->where('is_public', true)
            ->with(['client', 'serviceMapping'])
            ->first();

        if ($paymentLink) {
            // Increment view count
            $paymentLink->incrementViews();
        }

        return $paymentLink;
    }

    /**
     * Process payment through payment link
     */
    public function processPayment(PaymentLink $paymentLink, array $paymentData): array
    {
        try {
            // Validate payment link can be used
            if (!$paymentLink->can_be_used) {
                return [
                    'success' => false,
                    'error' => 'Payment link is not available for use'
                ];
            }

            // Validate payment amount
            $amount = $paymentData['amount'] ?? $paymentLink->amount;
            if (!$paymentLink->validatePaymentAmount($amount)) {
                return [
                    'success' => false,
                    'error' => 'Invalid payment amount'
                ];
            }

            // Validate mobile network
            $network = $paymentData['mobile_network'] ?? null;
            if ($network && !$paymentLink->isNetworkAllowed($network)) {
                return [
                    'success' => false,
                    'error' => 'Mobile network not allowed for this payment link'
                ];
            }

            // Create transaction data
            $transactionData = [
                'customer_phone' => $paymentData['customer_phone'],
                'mobile_network' => $paymentData['mobile_network'],
                'amount' => $amount,
                'description' => $paymentLink->description,
                'reference' => $paymentLink->client_reference . '_' . Str::random(8),
                'date' => now()->format('Y-m-d H:i:s'),
                'webhook_url' => $paymentLink->webhook_url,
                'metadata' => [
                    'payment_link_id' => $paymentLink->link_id,
                    'payment_link_short_code' => $paymentLink->short_code,
                    'customer_name' => $paymentData['customer_name'] ?? $paymentLink->customer_name,
                    'customer_email' => $paymentData['customer_email'] ?? $paymentLink->customer_email,
                ]
            ];

            // Process through ESB
            $esbService = new EsbService();
            $result = $esbService->processRequest(
                $paymentLink->serviceMapping,
                $transactionData,
                $this->createTransaction($paymentLink, $transactionData)
            );

            // Increment usage count if successful
            if ($result['success']) {
                $paymentLink->incrementUses();
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Payment link processing failed', [
                'error' => $e->getMessage(),
                'payment_link_id' => $paymentLink->link_id,
                'payment_data' => $paymentData
            ]);

            return [
                'success' => false,
                'error' => 'Payment processing failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get payment link statistics
     */
    public function getPaymentLinkStats(PaymentLink $paymentLink): array
    {
        return [
            'link_id' => $paymentLink->link_id,
            'short_code' => $paymentLink->short_code,
            'status' => $paymentLink->status,
            'amount' => $paymentLink->amount,
            'currency' => $paymentLink->currency,
            'views_count' => $paymentLink->views_count,
            'current_uses' => $paymentLink->current_uses,
            'max_uses' => $paymentLink->max_uses,
            'total_collected' => $paymentLink->total_collected,
            'successful_transactions_count' => $paymentLink->successful_transactions_count,
            'conversion_rate' => $paymentLink->conversion_rate,
            'created_at' => $paymentLink->created_at->toISOString(),
            'expires_at' => $paymentLink->expires_at?->toISOString(),
            'last_viewed_at' => $paymentLink->last_viewed_at?->toISOString(),
        ];
    }

    /**
     * List payment links for a client
     */
    public function listPaymentLinks(Client $client, array $filters = []): array
    {
        $query = PaymentLink::where('client_id', $client->id);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $paymentLinks = $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 20);

        return [
            'success' => true,
            'data' => $paymentLinks->items(),
            'pagination' => [
                'current_page' => $paymentLinks->currentPage(),
                'last_page' => $paymentLinks->lastPage(),
                'per_page' => $paymentLinks->perPage(),
                'total' => $paymentLinks->total(),
            ]
        ];
    }

    /**
     * Cancel a payment link
     */
    public function cancelPaymentLink(string $linkId, Client $client): array
    {
        $paymentLink = PaymentLink::where('link_id', $linkId)
            ->where('client_id', $client->id)
            ->first();

        if (!$paymentLink) {
            return [
                'success' => false,
                'error' => 'Payment link not found'
            ];
        }

        $paymentLink->markAsCancelled();

        return [
            'success' => true,
            'message' => 'Payment link cancelled successfully'
        ];
    }

    /**
     * Validate payment link data
     */
    private function validatePaymentLinkData(array $data): array
    {
        $rules = [
            'amount' => 'required|numeric|min:100|max:1000000',
            'description' => 'required|string|max:255',
            'reference' => 'nullable|string|max:100',
            'currency' => 'nullable|string|size:3',
            'customer_phone' => 'nullable|regex:/^255[0-9]{9}$/',
            'customer_email' => 'nullable|email',
            'allowed_networks' => 'nullable|array',
            'allowed_networks.*' => 'string|in:TZ-AIRTEL-C2B,TZ-TIGO-C2B,TZ-MPESA-C2B,TZ-HALOPESA-C2B',
            'expires_at' => 'nullable|date|after:now',
            'max_uses' => 'nullable|integer|min:1|max:1000',
            'webhook_url' => 'nullable|url',
            'success_url' => 'nullable|url',
            'failure_url' => 'nullable|url',
            'cancel_url' => 'nullable|url',
        ];

        $validator = Validator::make($data, $rules);

        return [
            'valid' => !$validator->fails(),
            'errors' => $validator->errors()
        ];
    }

    /**
     * Create transaction record for payment link
     */
    private function createTransaction(PaymentLink $paymentLink, array $transactionData)
    {
        return \App\Models\Transaction::create([
            'transaction_id' => 'TXN_' . Str::random(16),
            'client_reference' => $transactionData['reference'],
            'client_id' => $paymentLink->client_id,
            'aggregator_id' => $paymentLink->serviceMapping->aggregator_id,
            'service_id' => $paymentLink->serviceMapping->service_id,
            'service_mapping_id' => $paymentLink->service_mapping_id,
            'amount' => $transactionData['amount'],
            'currency' => $paymentLink->currency,
            'customer_phone' => $transactionData['customer_phone'],
            'mobile_network' => $transactionData['mobile_network'],
            'description' => $transactionData['description'],
            'narration' => $paymentLink->narration,
            'request_data' => $transactionData,
            'status' => 'pending',
            'webhook_url' => $transactionData['webhook_url'],
            'metadata' => $transactionData['metadata'] ?? [],
        ]);
    }
} 