<?php

namespace App\Services;

use App\Models\PaymentLink;
use App\Models\PaymentLinkItem;
use App\Models\Client;
use App\Models\ServiceMapping;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EnhancedPaymentLinkService
{
    /**
     * Generate a payment link with multiple items
     */
    public function generateMultiItemPaymentLink(array $data, Client $client): array
    {
        try {
            DB::beginTransaction();

            // Validate input data
            $validation = $this->validateMultiItemPaymentLinkData($data);
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

            // Calculate total amount from items
            $totalAmount = collect($data['items'])->sum('amount');

            // Create payment link
            $paymentLink = PaymentLink::create([
                'client_reference' => $data['reference'] ?? 'LINK_' . Str::random(16),
                'client_id' => $client->id,
                'service_mapping_id' => $serviceMapping->id,
                'amount' => $totalAmount,
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
                'allow_partial_payment' => $data['allow_partial_payment'] ?? true, // Default to true for multi-item
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

            // Create payment link items
            $items = collect();
            foreach ($data['items'] as $itemData) {
                $item = PaymentLinkItem::create([
                    'payment_link_id' => $paymentLink->id,
                    'item_name' => $itemData['name'],
                    'item_description' => $itemData['description'] ?? null,
                    'amount' => $itemData['amount'],
                    'currency' => $itemData['currency'] ?? 'TZS',
                    'is_required' => $itemData['is_required'] ?? true,
                    'allow_partial' => $itemData['allow_partial'] ?? false,
                    'minimum_amount' => $itemData['minimum_amount'] ?? null,
                    'quantity' => $itemData['quantity'] ?? 1,
                    'unit' => $itemData['unit'] ?? null,
                    'category' => $itemData['category'] ?? null,
                    'subcategory' => $itemData['subcategory'] ?? null,
                    'metadata' => $itemData['metadata'] ?? null,
                ]);
                $items->push($item);
            }

            DB::commit();

            Log::info('Multi-item payment link generated', [
                'link_id' => $paymentLink->link_id,
                'short_code' => $paymentLink->short_code,
                'client_id' => $client->id,
                'total_amount' => $totalAmount,
                'items_count' => count($items)
            ]);

            return [
                'success' => true,
                'data' => [
                    'link_id' => $paymentLink->link_id,
                    'short_code' => $paymentLink->short_code,
                    'payment_url' => $paymentLink->payment_url,
                    'qr_code_data' => $paymentLink->qr_code_data,
                    'total_amount' => $totalAmount,
                    'currency' => $paymentLink->currency,
                    'description' => $paymentLink->description,
                    'items' => $items->map(function($item) {
                        return [
                            'item_code' => $item->item_code,
                            'name' => $item->item_name,
                            'description' => $item->item_description,
                            'amount' => $item->amount,
                            'is_required' => $item->is_required,
                            'allow_partial' => $item->allow_partial,
                            'category' => $item->category,
                        ];
                    }),
                    'expires_at' => $paymentLink->expires_at?->toISOString(),
                    'max_uses' => $paymentLink->max_uses,
                    'is_reusable' => $paymentLink->is_reusable,
                    'allowed_networks' => $paymentLink->allowed_networks_array,
                    'created_at' => $paymentLink->created_at->toISOString()
                ]
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Multi-item payment link generation failed', [
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
     * Generate bulk payment links for multiple customers
     */
    public function generateBulkPaymentLinks(array $data, Client $client): array
    {
        try {
            $results = [];
            $successCount = 0;
            $errorCount = 0;

            foreach ($data['customers'] as $customerData) {
                $linkData = array_merge($data['link_template'], [
                    'customer_name' => $customerData['name'],
                    'customer_phone' => $customerData['phone'] ?? null,
                    'customer_email' => $customerData['email'] ?? null,
                    'reference' => $data['link_template']['reference'] ?? 'BULK_' . Str::random(8) . '_' . $customerData['id'] ?? uniqid(),
                ]);

                $result = $this->generateMultiItemPaymentLink($linkData, $client);
                
                if ($result['success']) {
                    $successCount++;
                    $results[] = [
                        'customer' => $customerData,
                        'payment_link' => $result['data']
                    ];
                } else {
                    $errorCount++;
                    $results[] = [
                        'customer' => $customerData,
                        'error' => $result['error']
                    ];
                }
            }

            return [
                'success' => true,
                'data' => [
                    'total_customers' => count($data['customers']),
                    'successful_links' => $successCount,
                    'failed_links' => $errorCount,
                    'results' => $results
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Bulk payment link generation failed', [
                'error' => $e->getMessage(),
                'client_id' => $client->id
            ]);

            return [
                'success' => false,
                'error' => 'Bulk payment link generation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process itemized payment for a payment link
     */
    public function processItemizedPayment(PaymentLink $paymentLink, array $paymentData): array
    {
        try {
            DB::beginTransaction();

            // Validate payment link can be used
            if (!$paymentLink->can_be_used) {
                return [
                    'success' => false,
                    'error' => 'Payment link is not available for use'
                ];
            }

            // Validate payment items
            $itemPayments = $paymentData['items'] ?? [];
            $totalPaymentAmount = 0;
            $processedItems = [];

            foreach ($itemPayments as $itemPayment) {
                $item = PaymentLinkItem::where('item_code', $itemPayment['item_code'])
                    ->where('payment_link_id', $paymentLink->id)
                    ->first();

                if (!$item) {
                    return [
                        'success' => false,
                        'error' => 'Invalid item code: ' . $itemPayment['item_code']
                    ];
                }

                $amount = $itemPayment['amount'];
                $totalPaymentAmount += $amount;

                // Validate item payment
                if (!$item->canPayPartially($amount)) {
                    return [
                        'success' => false,
                        'error' => 'Invalid payment amount for item: ' . $item->item_name
                    ];
                }

                $processedItems[] = [
                    'item' => $item,
                    'amount' => $amount
                ];
            }

            // Create transaction data
            $transactionData = [
                'customer_phone' => $paymentData['customer_phone'],
                'mobile_network' => $paymentData['mobile_network'],
                'amount' => $totalPaymentAmount,
                'description' => $paymentLink->description,
                'reference' => $paymentLink->client_reference . '_' . Str::random(8),
                'date' => now()->format('Y-m-d H:i:s'),
                'webhook_url' => $paymentLink->webhook_url,
                'metadata' => [
                    'payment_link_id' => $paymentLink->link_id,
                    'payment_link_short_code' => $paymentLink->short_code,
                    'customer_name' => $paymentData['customer_name'] ?? $paymentLink->customer_name,
                    'customer_email' => $paymentData['customer_email'] ?? $paymentLink->customer_email,
                    'itemized_payments' => collect($processedItems)->map(function($item) {
                        return [
                            'item_code' => $item['item']->item_code,
                            'item_name' => $item['item']->item_name,
                            'amount' => $item['amount']
                        ];
                    })
                ]
            ];

            // Process through ESB
            $esbService = new EsbService();
            $result = $esbService->processRequest(
                $paymentLink->serviceMapping,
                $transactionData,
                $this->createTransaction($paymentLink, $transactionData)
            );

            // If successful, record payments for items
            if ($result['success']) {
                foreach ($processedItems as $itemPayment) {
                    $itemPayment['item']->recordPayment($itemPayment['amount']);
                }
                
                $paymentLink->incrementUses();
            }

            DB::commit();

            return $result;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Itemized payment processing failed', [
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
     * Get detailed payment link statistics including items
     */
    public function getDetailedPaymentLinkStats(PaymentLink $paymentLink): array
    {
        $items = $paymentLink->items()->with('paymentLink')->get();
        
        $itemStats = $items->map(function($item) {
            return [
                'item_code' => $item->item_code,
                'name' => $item->item_name,
                'description' => $item->item_description,
                'amount' => $item->amount,
                'paid_amount' => $item->paid_amount,
                'remaining_amount' => $item->remaining_amount,
                'payment_percentage' => $item->payment_percentage,
                'status' => $item->status,
                'is_required' => $item->is_required,
                'allow_partial' => $item->allow_partial,
                'category' => $item->category,
            ];
        });

        return [
            'link_id' => $paymentLink->link_id,
            'short_code' => $paymentLink->short_code,
            'status' => $paymentLink->status,
            'total_amount' => $paymentLink->total_items_amount,
            'total_paid' => $paymentLink->total_items_paid,
            'remaining_amount' => $paymentLink->remaining_items_amount,
            'payment_progress' => $paymentLink->payment_progress,
            'currency' => $paymentLink->currency,
            'views_count' => $paymentLink->views_count,
            'current_uses' => $paymentLink->current_uses,
            'max_uses' => $paymentLink->max_uses,
            'total_collected' => $paymentLink->total_collected,
            'successful_transactions_count' => $paymentLink->successful_transactions_count,
            'conversion_rate' => $paymentLink->conversion_rate,
            'items' => $itemStats,
            'items_summary' => [
                'total_items' => $items->count(),
                'paid_items' => $items->where('status', 'paid')->count(),
                'partial_items' => $items->where('status', 'partial')->count(),
                'pending_items' => $items->where('status', 'pending')->count(),
            ],
            'created_at' => $paymentLink->created_at->toISOString(),
            'expires_at' => $paymentLink->expires_at?->toISOString(),
            'last_viewed_at' => $paymentLink->last_viewed_at?->toISOString(),
        ];
    }

    /**
     * Validate multi-item payment link data
     */
    private function validateMultiItemPaymentLinkData(array $data): array
    {
        $rules = [
            'description' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.amount' => 'required|numeric|min:100|max:1000000',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.is_required' => 'nullable|boolean',
            'items.*.allow_partial' => 'nullable|boolean',
            'items.*.minimum_amount' => 'nullable|numeric|min:100',
            'items.*.quantity' => 'nullable|integer|min:1',
            'items.*.category' => 'nullable|string|max:100',
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