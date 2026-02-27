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
use Illuminate\Support\Arr;
use Carbon\Carbon;

class UniversalPaymentLinkService
{
    /**
     * Generate a universal payment link (individual or public)
     */
    public function generateUniversalPaymentLink(array $data, Client $client): array
    {
        try {
            DB::beginTransaction();

            // Validate input data
            $validation = $this->validateUniversalPaymentLinkData($data);
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

            // Determine target type and customer information
            $target = $data['target'] ?? 'individual';
            $isPublic = $target === 'public';
            
            // For public links, customer info is collected later
            $customerName = $isPublic ? null : ($data['customer_name'] ?? null);
            $customerPhone = $isPublic ? null : ($data['customer_phone'] ?? null);
            $customerEmail = $isPublic ? null : ($data['customer_email'] ?? null);
            $customerReference = $data['customer_reference'] ?? null;

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
                'customer_phone' => $customerPhone,
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'payment_method' => $data['payment_method'] ?? 'mobile_money',
                'allowed_networks' => $data['allowed_networks'] ?? [
                    'TZ-AIRTEL-C2B',
                    'TZ-TIGO-C2B',
                    'TZ-MPESA-C2B',
                    'TZ-HALOPESA-C2B'
                ],
                'allow_partial_payment' => $data['allow_partial_payment'] ?? true,
                'minimum_amount' => $data['minimum_amount'] ?? null,
                'maximum_amount' => $data['maximum_amount'] ?? null,
                'expires_at' => !empty($data['expires_at']) ? Carbon::parse($data['expires_at']) : null,
                'max_uses' => $data['max_uses'] ?? null,
                'is_reusable' => $data['is_reusable'] ?? false,
                'is_public' => $isPublic,
                'metadata' => array_merge($data['metadata'] ?? [], [
                    'target_type' => $target,
                    'customer_reference' => $customerReference,
                    'is_public_link' => $isPublic
                ]),
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
                    'item_name' => $itemData['product_service_name'],
                    'item_description' => $itemData['description'] ?? null,
                    'amount' => $itemData['amount'],
                    'currency' => $itemData['currency'] ?? 'TZS',
                    'is_required' => $itemData['is_required'] ?? true,
                    'allow_partial' => $itemData['allow_partial'] ?? false,
                    'minimum_amount' => $itemData['minimum_amount'] ?? null,
                    'quantity' => $itemData['quantity'] ?? 1,
                    'unit' => $itemData['unit'] ?? null,
                    'category' => $itemData['type'] ?? 'service',
                    'subcategory' => $itemData['subcategory'] ?? null,
                    'metadata' => array_merge($itemData['metadata'] ?? [], [
                        'type' => $itemData['type'] ?? 'service',
                        'product_service_reference' => $itemData['product_service_reference'] ?? null,
                        'product_service_name' => $itemData['product_service_name']
                    ]),
                ]);
                $items->push($item);
            }

            DB::commit();

            Log::info('Universal payment link generated', [
                'link_id' => $paymentLink->link_id,
                'short_code' => $paymentLink->short_code,
                'client_id' => $client->id,
                'target_type' => $target,
                'is_public' => $isPublic,
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
                    'target_type' => $target,
                    'is_public' => $isPublic,
                    'total_amount' => $totalAmount,
                    'currency' => $paymentLink->currency,
                    'description' => $paymentLink->description,
                    'customer_reference' => $customerReference,
                    'customer_name' => $customerName,
                    'customer_phone' => $customerPhone,
                    'customer_email' => $customerEmail,
                    'items' => $items->map(function($item) {
                        return [
                            'item_code' => $item->item_code,
                            'type' => $item->category,
                            'product_service_reference' => $item->metadata['product_service_reference'] ?? null,
                            'product_service_name' => $item->item_name,
                            'description' => $item->item_description,
                            'amount' => $item->amount,
                            'is_required' => $item->is_required,
                            'allow_partial' => $item->allow_partial,
                            'minimum_amount' => $item->minimum_amount,
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
            Log::error('Universal payment link generation failed', [
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
     * Process universal payment
     */
    public function processUniversalPayment(PaymentLink $paymentLink, array $paymentData): array
    {
        DB::beginTransaction();
        
        try {
            // Log payment processing start
            Log::info('Universal payment processing started', [
                'link_id' => $paymentLink->link_id,
                'short_code' => $paymentLink->short_code,
                'amount' => $paymentData['amount'],
                'mobile_network' => $paymentData['mobile_network'],
                'customer_phone' => substr($paymentData['customer_phone'], 0, 6) . '****' . substr($paymentData['customer_phone'], -2)
            ]);
            
            // Validate payment link can be used
            if (!$paymentLink->can_be_used) {
                $reason = 'unknown';
                if ($paymentLink->is_expired) {
                    $reason = 'expired';
                } elseif ($paymentLink->status === 'cancelled') {
                    $reason = 'cancelled';
                } elseif ($paymentLink->status === 'completed') {
                    $reason = 'completed';
                }
                
                Log::warning('Payment link cannot be used', [
                    'link_id' => $paymentLink->link_id,
                    'short_code' => $paymentLink->short_code,
                    'status' => $paymentLink->status,
                    'reason' => $reason
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Payment link is not available for use'
                ];
            }

            // For public links, validate and collect customer information
            $isPublic = $paymentLink->metadata['is_public_link'] ?? false;
            
            if ($isPublic) {
                // Validate required customer information for public links
                $customerValidation = Validator::make($paymentData, [
                    'customer_name' => 'required|string|max:255',
                    'customer_phone' => 'required|regex:/^255[0-9]{9}$/',
                    'customer_email' => 'nullable|email',
                ]);

                if ($customerValidation->fails()) {
                    Log::warning('Customer validation failed for public link', [
                        'link_id' => $paymentLink->link_id,
                        'errors' => $customerValidation->errors()->toArray()
                    ]);
                    
                    return [
                        'success' => false,
                        'error' => 'Customer information required for public payment link',
                        'errors' => $customerValidation->errors()
                    ];
                }

                // Update payment link with customer information
                $paymentLink->update([
                    'customer_name' => $paymentData['customer_name'],
                    'customer_phone' => $paymentData['customer_phone'],
                    'customer_email' => $paymentData['customer_email'] ?? null,
                ]);
                
                Log::info('Customer information updated for public link', [
                    'link_id' => $paymentLink->link_id,
                    'customer_name' => $paymentData['customer_name'],
                    'customer_phone' => substr($paymentData['customer_phone'], 0, 6) . '****' . substr($paymentData['customer_phone'], -2)
                ]);
            }

            // Validate payment items
            $itemPayments = $paymentData['items'] ?? [];
            $totalPaymentAmount = 0;
            $processedItems = [];

            Log::info('Processing payment items', [
                'link_id' => $paymentLink->link_id,
                'items_count' => count($itemPayments)
            ]);

            foreach ($itemPayments as $itemPayment) {
                $item = PaymentLinkItem::where('item_code', $itemPayment['item_code'])
                    ->where('payment_link_id', $paymentLink->id)
                    ->first();

                if (!$item) {
                    Log::error('Invalid item code in payment', [
                        'link_id' => $paymentLink->link_id,
                        'item_code' => $itemPayment['item_code'],
                        'available_items' => $paymentLink->items->pluck('item_code')->toArray()
                    ]);
                    
                    return [
                        'success' => false,
                        'error' => 'Invalid item code: ' . $itemPayment['item_code']
                    ];
                }

                $amount = $itemPayment['amount'];
                $totalPaymentAmount += $amount;

                // Validate item payment
                if (!$item->canPayPartially($amount)) {
                    Log::warning('Invalid payment amount for item', [
                        'link_id' => $paymentLink->link_id,
                        'item_code' => $item->item_code,
                        'item_name' => $item->item_name,
                        'requested_amount' => $amount,
                        'item_amount' => $item->amount,
                        'allow_partial' => $item->allow_partial,
                        'minimum_amount' => $item->minimum_amount
                    ]);
                    
                    return [
                        'success' => false,
                        'error' => 'Invalid payment amount for item: ' . $item->item_name
                    ];
                }

                $processedItems[] = [
                    'item' => $item,
                    'amount' => $amount
                ];
                
                Log::info('Item payment validated', [
                    'link_id' => $paymentLink->link_id,
                    'item_code' => $item->item_code,
                    'item_name' => $item->item_name,
                    'amount' => $amount
                ]);
            }

            // Create transaction data with unique reference
            $uniqueReference = $paymentLink->client_reference . '_' . time() . '_' . Str::random(6);
            
            // Generate the callback URL that Tembo Plus should use
            $callbackUrl = url('/api/callback/TEMBO');
            
            $transactionData = [
                'customer_phone' => $paymentData['customer_phone'],
                'mobile_network' => $paymentData['mobile_network'],
                'amount' => $totalPaymentAmount,
                'description' => $paymentLink->description,
                'reference' => $uniqueReference,
                'date' => now()->format('Y-m-d H:i:s'),
                'webhook_url' => $callbackUrl, // Use our callback URL instead of client webhook
                'metadata' => array_merge($paymentLink->metadata ?? [], [
                    'payment_link_id' => $paymentLink->link_id,
                    'payment_link_short_code' => $paymentLink->short_code,
                    'customer_name' => $paymentData['customer_name'],
                    'customer_email' => $paymentData['customer_email'] ?? null,
                    'customer_reference' => $paymentLink->metadata['customer_reference'] ?? null,
                    'target_type' => $paymentLink->metadata['target_type'] ?? 'individual',
                    'is_public_link' => $isPublic,
                    'itemized_payments' => collect($processedItems)->map(function($item) {
                        return [
                            'item_code' => $item['item']->item_code,
                            'type' => $item['item']->category,
                            'product_service_reference' => $item['item']->metadata['product_service_reference'] ?? null,
                            'product_service_name' => $item['item']->item_name,
                            'amount' => $item['amount']
                        ];
                    })
                ])
            ];

            Log::info('Transaction data prepared', [
                'link_id' => $paymentLink->link_id,
                'transaction_reference' => $transactionData['reference'],
                'total_amount' => $totalPaymentAmount,
                'mobile_network' => $transactionData['mobile_network'],
                'items_count' => count($processedItems),
                'aggregator_callback_url' => $transactionData['webhook_url'],
                'client_webhook_url' => $paymentLink->webhook_url,
                'note' => 'Aggregator callback URL is for Tembo to call us; client webhook URL is for us to notify the client'
            ]);

            // Process through ESB
            $esbService = new EsbService();
            $result = $esbService->processRequest(
                $paymentLink->serviceMapping,
                $transactionData,
                $this->createTransaction($paymentLink, $transactionData)
            );

            // Log ESB processing result
            Log::info('ESB processing completed', [
                'link_id' => $paymentLink->link_id,
                'success' => $result['success'],
                'status_code' => $result['status_code'],
                'response_time' => $result['response_time'],
                'aggregator_status' => $result['aggregator_response']['status_code'] ?? null,
                'error' => $result['error'] ?? null
            ]);

            // If successful, record payments for items
            if ($result['success']) {
                foreach ($processedItems as $itemPayment) {
                    $itemPayment['item']->recordPayment($itemPayment['amount']);
                    
                    Log::info('Item payment recorded', [
                        'link_id' => $paymentLink->link_id,
                        'item_code' => $itemPayment['item']->item_code,
                        'amount' => $itemPayment['amount']
                    ]);
                }
                
                $paymentLink->incrementUses();
                
                Log::info('Payment link usage incremented', [
                    'link_id' => $paymentLink->link_id,
                    'new_uses' => $paymentLink->current_uses + 1
                ]);
            }

            DB::commit();
            
            Log::info('Universal payment processing completed', [
                'link_id' => $paymentLink->link_id,
                'success' => $result['success'],
                'transaction_id' => $result['response']['transaction_id'] ?? null
            ]);

            return $result;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Universal payment processing failed', [
                'link_id' => $paymentLink->link_id,
                'short_code' => $paymentLink->short_code,
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'payment_data' => Arr::except($paymentData, ['customer_phone', 'customer_name', 'customer_email'])
            ]);

            return [
                'success' => false,
                'error' => 'Payment processing failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get universal payment link with enhanced details
     */
    public function getUniversalPaymentLink(string $shortCode): ?PaymentLink
    {
        $paymentLink = PaymentLink::where('short_code', $shortCode)
            ->with(['client', 'serviceMapping', 'items'])
            ->first();

        if ($paymentLink) {
            // Increment view count
            $paymentLink->incrementViews();
        }

        return $paymentLink;
    }

    /**
     * Get detailed universal payment link statistics
     */
    public function getUniversalPaymentLinkStats(PaymentLink $paymentLink): array
    {
        $items = $paymentLink->items()->with('paymentLink')->get();
        
        $itemStats = $items->map(function($item) {
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
        });

        return [
            'link_id' => $paymentLink->link_id,
            'short_code' => $paymentLink->short_code,
            'status' => $paymentLink->status,
            'target_type' => $paymentLink->metadata['target_type'] ?? 'individual',
            'is_public' => $paymentLink->metadata['is_public_link'] ?? false,
            'customer_reference' => $paymentLink->metadata['customer_reference'] ?? null,
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
     * Validate universal payment link data
     */
    private function validateUniversalPaymentLinkData(array $data): array
    {
        $rules = [
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
        ];

        // Add conditional validation for individual targets
        if (($data['target'] ?? 'individual') === 'individual') {
            $rules['customer_name'] = 'required|string|max:255';
            $rules['customer_phone'] = 'required|regex:/^255[0-9]{9}$/';
        }

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
            // Use client's webhook URL for notifications, not the internal callback URL
            'webhook_url' => $paymentLink->webhook_url,
            'metadata' => $transactionData['metadata'] ?? [],
        ]);
    }
} 