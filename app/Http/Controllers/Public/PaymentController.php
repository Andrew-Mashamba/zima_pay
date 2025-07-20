<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PaymentLink;
use App\Services\UniversalPaymentLinkService;
use App\Services\MobileNetworkDetectionService;
use App\Services\SecurityService;
use App\Services\ThreatDetectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class PaymentController extends Controller
{
    protected $universalPaymentLinkService;
    protected $mobileNetworkDetectionService;
    protected $securityService;
    protected $threatDetectionService;

    public function __construct(
        UniversalPaymentLinkService $universalPaymentLinkService,
        MobileNetworkDetectionService $mobileNetworkDetectionService,
        SecurityService $securityService,
        ThreatDetectionService $threatDetectionService
    ) {
        $this->universalPaymentLinkService = $universalPaymentLinkService;
        $this->mobileNetworkDetectionService = $mobileNetworkDetectionService;
        $this->securityService = $securityService;
        $this->threatDetectionService = $threatDetectionService;
    }

    /**
     * Show payment page for a payment link
     */
    public function showPaymentPage(string $shortCode)
    {
        try {
            // Security: Check for suspicious activity (more lenient for payment links)
            $request = request();
            $threatAnalysis = $this->threatDetectionService->analyzeRequest($request);
            
            if ($threatAnalysis['threats_detected']) {
                $this->threatDetectionService->processThreatDetection($threatAnalysis, $request);
                
                // Only block for extremely critical threats (very high threshold for payment links)
                if ($threatAnalysis['severity'] === 'critical' && $threatAnalysis['risk_score'] >= 95) {
                    Log::critical('Extremely critical threat detected on payment page access', [
                        'short_code' => $shortCode,
                        'ip' => $request->ip(),
                        'threats' => $threatAnalysis['threats']
                    ]);
                    
                    return view('public.payment.error', [
                        'message' => 'Access denied for security reasons',
                        'error_details' => 'Your request has been blocked due to security concerns.'
                    ]);
                }
                
                // Log other threats but don't block access
                if ($threatAnalysis['threats_detected']) {
                    Log::warning('Threat detected on payment page access (access allowed)', [
                        'short_code' => $shortCode,
                        'ip' => $request->ip(),
                        'threats' => $threatAnalysis['threats'],
                        'risk_score' => $threatAnalysis['risk_score']
                    ]);
                }
            }
            
            // Security: Rate limiting for payment page access
            $ip = $request->ip();
            $rateLimitKey = "payment_page_access:{$ip}";
            $accessCount = Cache::get($rateLimitKey, 0);
            
            if ($accessCount > 50) { // Max 50 payment page accesses per hour per IP
                Log::warning('Rate limit exceeded for payment page access', [
                    'ip' => $ip,
                    'short_code' => $shortCode,
                    'access_count' => $accessCount
                ]);
                
                return view('public.payment.error', [
                    'message' => 'Too many access attempts',
                    'error_details' => 'Please wait before trying to access this payment page again.'
                ]);
            }
            
            Cache::put($rateLimitKey, $accessCount + 1, 3600); // 1 hour window
            
            // Security: Log payment page access
            $this->logPaymentAccess($shortCode, $request, 'page_access');
            
            // Get payment link
            $paymentLink = $this->universalPaymentLinkService->getUniversalPaymentLink($shortCode);

            if (!$paymentLink) {
                            return view('public.payment.error', [
                'message' => 'Payment link not found or has expired',
                'error_details' => 'The payment link you are looking for could not be found in our system.'
            ]);
            }

            // Check if link can be used
            if (!$paymentLink->can_be_used) {
                $message = 'This payment link is no longer available';
                
                if ($paymentLink->is_expired) {
                    $message = 'This payment link has expired';
                } elseif ($paymentLink->status === 'cancelled') {
                    $message = 'This payment link has been cancelled';
                } elseif ($paymentLink->status === 'completed') {
                    $message = 'This payment link has been completed';
                }

                return view('public.payment.error', [
                    'message' => $message,
                    'error_details' => 'This payment link is no longer available for payments.'
                ]);
            }

            // Get mobile network detection data
            $mobileNetworks = $this->mobileNetworkDetectionService->getAllNetworks();
            $detectedNetwork = null;
            
            // If payment link has customer phone, detect network
            if ($paymentLink->customer_phone) {
                $detectedNetwork = $this->mobileNetworkDetectionService->detectNetwork($paymentLink->customer_phone);
            }

            return view('public.payment.show', [
                'paymentLink' => $paymentLink,
                'mobileNetworks' => $mobileNetworks,
                'detectedNetwork' => $detectedNetwork
            ]);

        } catch (\Exception $e) {
            Log::error('Payment page error', [
                'error' => $e->getMessage(),
                'short_code' => $shortCode
            ]);

            return view('public.payment.error', [
                'message' => 'An error occurred while loading the payment page'
            ]);
        }
    }

    /**
     * Process payment from payment page
     */
    public function processPayment(Request $request, string $shortCode)
    {
        try {
            // Security: Threat detection for payment processing (more lenient for payment links)
            $threatAnalysis = $this->threatDetectionService->analyzeRequest($request);
            
            if ($threatAnalysis['threats_detected']) {
                $this->threatDetectionService->processThreatDetection($threatAnalysis, $request);
                
                // Only block for extremely critical threats (very high threshold for payment processing)
                if ($threatAnalysis['severity'] === 'critical' && $threatAnalysis['risk_score'] >= 95) {
                    Log::critical('Extremely critical threat detected during payment processing', [
                        'short_code' => $shortCode,
                        'ip' => $request->ip(),
                        'threats' => $threatAnalysis['threats']
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment blocked for security reasons'
                    ], 403);
                }
                
                // Log other threats but don't block payment processing
                if ($threatAnalysis['threats_detected']) {
                    Log::warning('Threat detected during payment processing (processing allowed)', [
                        'short_code' => $shortCode,
                        'ip' => $request->ip(),
                        'threats' => $threatAnalysis['threats'],
                        'risk_score' => $threatAnalysis['risk_score']
                    ]);
                }
            }
            
            // Security: Rate limiting for payment processing
            $ip = $request->ip();
            $rateLimitKey = "payment_processing:{$ip}";
            $processingCount = Cache::get($rateLimitKey, 0);
            
            if ($processingCount > 10) { // Max 10 payment attempts per hour per IP
                Log::warning('Rate limit exceeded for payment processing', [
                    'ip' => $ip,
                    'short_code' => $shortCode,
                    'processing_count' => $processingCount
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Too many payment attempts. Please wait before trying again.'
                ], 429);
            }
            
            Cache::put($rateLimitKey, $processingCount + 1, 3600); // 1 hour window
            
            // Security: Log payment attempt
            $this->logPaymentAccess($shortCode, $request, 'payment_attempt');
            
            // Get payment link
            $paymentLink = $this->universalPaymentLinkService->getUniversalPaymentLink($shortCode);

            if (!$paymentLink) {
                Log::warning('Payment link not found during processing', [
                    'short_code' => $shortCode,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Payment link not found'
                ], 404);
            }

            // Check if link can be used
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
                    'short_code' => $shortCode,
                    'link_id' => $paymentLink->link_id,
                    'status' => $paymentLink->status,
                    'reason' => $reason,
                    'ip' => $request->ip()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'This payment link is no longer available for payments.'
                ], 400);
            }

            // Validate request data
            $isPublicLink = $paymentLink->metadata['is_public_link'] ?? false;
            $sanitizedData = $this->securityService->sanitizeInput($request->all());
            
            $validator = Validator::make($sanitizedData, [
                'customer_phone' => 'required|regex:/^255[0-9]{9}$/',
                'mobile_network' => 'required|string|in:' . implode(',', $paymentLink->allowed_networks_array),
                'payment_amount' => 'nullable|numeric|min:100',
                'customer_name' => $isPublicLink ? 'required|string|max:100' : 'nullable|string|max:100',
                'customer_email' => 'nullable|email',
            ]);

            if ($validator->fails()) {
                Log::warning('Payment validation failed', [
                    'short_code' => $shortCode,
                    'errors' => $validator->errors()->toArray(),
                    'ip' => $request->ip(),
                    'data' => Arr::except($sanitizedData, ['_token'])
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payment data',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Security: Encrypt sensitive payment data before processing
            $sensitiveData = [
                'customer_phone' => $sanitizedData['customer_phone'],
                'customer_name' => $sanitizedData['customer_name'] ?? null,
                'customer_email' => $sanitizedData['customer_email'] ?? null,
            ];
            
            $encryptedData = $this->securityService->encryptSensitiveData(json_encode($sensitiveData));
            
            // Prepare payment data with encrypted sensitive information
            $paymentData = [
                'customer_phone' => $sanitizedData['customer_phone'],
                'mobile_network' => $sanitizedData['mobile_network'],
                'amount' => $sanitizedData['payment_amount'] ?? $paymentLink->amount,
                'customer_name' => $sanitizedData['customer_name'] ?? null,
                'customer_email' => $sanitizedData['customer_email'] ?? null,
                'encrypted_data' => $encryptedData, // Store encrypted version for audit
                'items' => $paymentLink->items->map(function($item) use ($sanitizedData, $paymentLink) {
                    return [
                        'item_code' => $item->item_code,
                        'amount' => $sanitizedData['payment_amount'] ?? $paymentLink->amount
                    ];
                })->toArray()
            ];
            
            // Generate security hash after payment data is prepared
            $paymentData['security_hash'] = $this->securityService->generateAuditHash($paymentData);

            // Log payment attempt details
            Log::info('Payment processing initiated', [
                'short_code' => $shortCode,
                'link_id' => $paymentLink->link_id,
                'amount' => $paymentData['amount'],
                'mobile_network' => $paymentData['mobile_network'],
                'customer_phone' => substr($paymentData['customer_phone'], 0, 6) . '****' . substr($paymentData['customer_phone'], -2),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Process payment
            $result = $this->universalPaymentLinkService->processUniversalPayment($paymentLink, $paymentData);

            // Log the result
            Log::info('Payment processing result', [
                'short_code' => $shortCode,
                'link_id' => $paymentLink->link_id,
                'success' => $result['success'],
                'error' => $result['error'] ?? null,
                'transaction_id' => $result['data']['transaction_id'] ?? null,
                'response_time' => $result['response_time'] ?? null,
                'aggregator_status' => $result['aggregator_response']['status_code'] ?? null
            ]);

            if (!$result['success']) {
                // Provide user-friendly error messages
                $errorMessage = $this->getUserFriendlyErrorMessage($result['error'] ?? 'Payment processing failed');
                
                // Log detailed error information
                Log::error('Payment processing failed', [
                    'short_code' => $shortCode,
                    'link_id' => $paymentLink->link_id,
                    'original_error' => $result['error'],
                    'user_friendly_error' => $errorMessage,
                    'aggregator_response' => $result['aggregator_response'] ?? null,
                    'response_time' => $result['response_time'] ?? null,
                    'ip' => $request->ip()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 400);
            }

            // Log successful payment
            Log::info('Payment processed successfully', [
                'short_code' => $shortCode,
                'link_id' => $paymentLink->link_id,
                'transaction_id' => $result['data']['transaction_id'] ?? null,
                'amount' => $paymentData['amount'],
                'mobile_network' => $paymentData['mobile_network'],
                'response_time' => $result['response_time'] ?? null,
                'aggregator_status' => $result['aggregator_response']['status_code'] ?? null
            ]);

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully',
                'data' => [
                    'transaction_id' => $result['data']['transaction_id'] ?? null,
                    'status' => $result['data']['status'] ?? 'pending',
                    'message' => $result['data']['message'] ?? 'Payment is being processed',
                    'redirect_url' => $paymentLink->success_url
                ]
            ]);

        } catch (\Exception $e) {
            // Log the full exception details
            Log::error('Payment processing exception', [
                'error' => $e->getMessage(),
                'short_code' => $shortCode,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => Arr::except($request->all(), ['_token']),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your payment'
            ], 500);
        }
    }

    /**
     * Show payment success page
     */
    public function showSuccessPage(string $shortCode)
    {
        $paymentLink = PaymentLink::where('short_code', $shortCode)->first();

        return view('public.payment.success', [
            'paymentLink' => $paymentLink
        ]);
    }

    /**
     * Show payment failure page
     */
    public function showFailurePage(string $shortCode)
    {
        $paymentLink = PaymentLink::where('short_code', $shortCode)->first();

        return view('public.payment.failure', [
            'paymentLink' => $paymentLink
        ]);
    }

    /**
     * Show payment cancelled page
     */
    public function showCancelledPage(string $shortCode)
    {
        $paymentLink = PaymentLink::where('short_code', $shortCode)->first();

        return view('public.payment.cancelled', [
            'paymentLink' => $paymentLink
        ]);
    }

    /**
     * Get payment link details for AJAX requests
     */
    public function getPaymentLinkDetails(string $shortCode)
    {
        try {
            $paymentLink = $this->universalPaymentLinkService->getUniversalPaymentLink($shortCode);

            if (!$paymentLink) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment link not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'link_id' => $paymentLink->link_id,
                    'amount' => $paymentLink->amount,
                    'currency' => $paymentLink->currency,
                    'description' => $paymentLink->description,
                    'narration' => $paymentLink->narration,
                    'customer_name' => $paymentLink->customer_name,
                    'customer_email' => $paymentLink->customer_email,
                    'allowed_networks' => $paymentLink->allowed_networks_array,
                    'allow_partial_payment' => $paymentLink->allow_partial_payment,
                    'minimum_amount' => $paymentLink->minimum_amount,
                    'maximum_amount' => $paymentLink->maximum_amount,
                    'expires_at' => $paymentLink->expires_at?->toISOString(),
                    'max_uses' => $paymentLink->max_uses,
                    'current_uses' => $paymentLink->current_uses,
                    'is_reusable' => $paymentLink->is_reusable,
                    'can_be_used' => $paymentLink->can_be_used,
                    'status' => $paymentLink->status,
                    'created_at' => $paymentLink->created_at->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Payment link details error', [
                'error' => $e->getMessage(),
                'short_code' => $shortCode
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving payment link details'
            ], 500);
        }
    }

    /**
     * Download payment receipt
     */
    public function downloadReceipt(string $transactionId)
    {
        try {
            $transaction = \App\Models\Transaction::where('transaction_id', $transactionId)->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            // Generate receipt content
            $receiptContent = $this->generateReceiptContent($transaction);

            // Return PDF or text receipt
            return response($receiptContent)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', 'attachment; filename="receipt-' . $transactionId . '.txt"');

        } catch (\Exception $e) {
            Log::error('Receipt download error', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating the receipt'
            ], 500);
        }
    }

    /**
     * Get user-friendly error message
     */
    private function getUserFriendlyErrorMessage(string $error): string
    {
        // Map technical errors to user-friendly messages
        $errorMap = [
            'Aggregator returned status: 409' => 'This payment has already been processed. Please check your payment status.',
            'Aggregator returned status: 400' => 'Invalid payment information. Please check your details and try again.',
            'Aggregator returned status: 401' => 'Payment service authentication failed. Please try again later.',
            'Aggregator returned status: 403' => 'Payment service access denied. Please try again later.',
            'Aggregator returned status: 500' => 'Payment service is temporarily unavailable. Please try again in a few minutes.',
            'Service temporarily unavailable' => 'Payment service is temporarily unavailable. Please try again in a few minutes.',
            'Payment link not found' => 'This payment link is no longer available or has expired.',
            'Payment link is not available for use' => 'This payment link is no longer available for payments.',
            'Invalid payment data' => 'Please check your payment information and try again.',
            'Customer information required' => 'Please provide all required customer information.',
            'Invalid item code' => 'Payment item information is invalid. Please try again.',
            'Invalid payment amount' => 'Please enter a valid payment amount.',
            'Payment processing failed' => 'Unable to process your payment at this time. Please try again.',
        ];
        
        // Check for exact matches first
        if (isset($errorMap[$error])) {
            return $errorMap[$error];
        }
        
        // Check for partial matches
        foreach ($errorMap as $technicalError => $userMessage) {
            if (strpos($error, $technicalError) !== false) {
                return $userMessage;
            }
        }
        
        // Check for common patterns
        if (strpos($error, 'Aggregator returned status:') !== false) {
            return 'Payment service is experiencing issues. Please try again in a few minutes.';
        }
        
        if (strpos($error, 'timeout') !== false) {
            return 'Payment request timed out. Please check your connection and try again.';
        }
        
        if (strpos($error, 'network') !== false) {
            return 'Network connection issue. Please check your internet connection and try again.';
        }
        
        // Default user-friendly message
        return 'An unexpected error occurred. Please try again or contact support if the problem persists.';
    }

    /**
     * Generate receipt content
     */
    private function generateReceiptContent($transaction)
    {
        $content = "ZIMA ESB - PAYMENT RECEIPT\n";
        $content .= "================================\n\n";
        $content .= "Transaction ID: " . $transaction->transaction_id . "\n";
        $content .= "Date: " . $transaction->created_at->format('M d, Y H:i:s') . "\n";
        $content .= "Amount: TZS " . number_format($transaction->amount) . "\n";
        $content .= "Currency: " . $transaction->currency . "\n";
        $content .= "Customer Phone: " . $transaction->customer_phone . "\n";
        $content .= "Mobile Network: " . $transaction->mobile_network . "\n";
        $content .= "Description: " . $transaction->description . "\n";
        $content .= "Status: " . strtoupper($transaction->status) . "\n\n";
        $content .= "Thank you for your payment!\n";
        $content .= "This receipt serves as proof of your transaction.\n\n";
        $content .= "For support, contact: support@zima-esb.com\n";
        $content .= "© 2025 ZIMA ESB. All rights reserved.\n";

        return $content;
    }
    
    /**
     * Log payment access for security monitoring
     */
    private function logPaymentAccess(string $shortCode, Request $request, string $eventType): void
    {
        try {
            DB::table('security_logs')->insert([
                'event_type' => "payment_{$eventType}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'endpoint' => $request->path(),
                'data' => json_encode([
                    'short_code' => $shortCode,
                    'event_type' => $eventType,
                    'request_method' => $request->method(),
                    'request_uri' => $request->getRequestUri(),
                    'timestamp' => now()->toISOString()
                ]),
                'severity' => $eventType === 'payment_attempt' ? 'medium' : 'low',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log payment access', [
                'error' => $e->getMessage(),
                'short_code' => $shortCode,
                'event_type' => $eventType
            ]);
        }
    }
    
    /**
     * Log payment security event
     */
    private function logPaymentSecurityEvent(string $shortCode, Request $request, string $eventType, array $data = []): void
    {
        try {
            DB::table('security_logs')->insert([
                'event_type' => "payment_security_{$eventType}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'endpoint' => $request->path(),
                'data' => json_encode(array_merge([
                    'short_code' => $shortCode,
                    'event_type' => $eventType,
                    'request_method' => $request->method(),
                    'timestamp' => now()->toISOString()
                ], $data)),
                'severity' => 'high',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log payment security event', [
                'error' => $e->getMessage(),
                'short_code' => $shortCode,
                'event_type' => $eventType
            ]);
        }
    }
} 