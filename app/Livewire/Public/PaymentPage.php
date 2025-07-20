<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\PaymentLink;
use App\Services\UniversalPaymentLinkService;
use App\Services\MobileNetworkDetectionService;
use App\Services\SecurityService;
use App\Services\ThreatDetectionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class PaymentPage extends Component
{
    public $shortCode;
    public $paymentLink;
    public $detectedNetwork;
    public $mobileNetworks;
    
    // Form data
    public $customerName;
    public $customerPhone;
    public $customerEmail;
    public $mobileNetwork;
    public $paymentAmount;
    public $selectedItems = [];
    public $payAll = false;
    
    // UI state
    public $isProcessing = false;
    public $processingMessage = '';
    public $showSuccess = false;
    public $showError = false;
    public $errorMessage = '';
    public $successMessage = '';
    public $transactionId = '';
    public $isWaitingForCallback = false;
    public $callbackStatus = '';
    public $lastStatusCheck = null;
    // Removed public property, will use computed property instead
    
    // Validation
    public $validationErrors = [];
    
    protected $listeners = [
        'networkDetected' => 'updateNetwork',
        'itemSelected' => 'updateSelectedItems',
        'payAllToggled' => 'togglePayAll'
    ];

    public function mount($shortCode)
    {
        $this->shortCode = $shortCode;
        $this->loadPaymentLink();
        $this->initializeForm();
        $this->detectNetworkFromPhone();
        
        // Log component initialization
        Log::info('Livewire PaymentPage component mounted', [
            'short_code' => $this->shortCode,
            'payment_link_found' => $this->paymentLink ? true : false,
            'items_count' => $this->paymentLink ? $this->paymentLink->items->count() : 0,
            'is_public_link' => $this->isPublicLink,
            'metadata' => $this->paymentLink ? $this->paymentLink->metadata : null,
            'detected_network' => $this->detectedNetwork ? $this->detectedNetwork['name'] : 'None'
        ]);
    }

    public function loadPaymentLink()
    {
        $this->paymentLink = PaymentLink::where('short_code', $this->shortCode)
            ->with(['client', 'serviceMapping', 'items'])
            ->first();

        if (!$this->paymentLink) {
            abort(404, 'Payment link not found');
        }

        if (!$this->paymentLink->can_be_used) {
            abort(400, 'This payment link is no longer available');
        }

        // Load mobile networks
        $mobileNetworkService = app(MobileNetworkDetectionService::class);
        $this->mobileNetworks = $mobileNetworkService->getAllNetworks();
    }

    public function initializeForm()
    {
        $this->customerName = $this->paymentLink->customer_name ?? '';
        $this->customerPhone = $this->paymentLink->customer_phone ?? '';
        $this->customerEmail = $this->paymentLink->customer_email ?? '';
        $this->paymentAmount = $this->paymentLink->amount;
        
        // Public link flag is now handled by computed property
        
        // Initialize selected items for individual payments
        if ($this->paymentLink->items->count() > 1) {
            $this->selectedItems = $this->paymentLink->items->pluck('item_code')->toArray();
        } else {
            $this->selectedItems = [$this->paymentLink->items->first()->item_code];
        }
        
        $this->payAll = true; // Default to pay all
    }

    public function updatedCustomerPhone()
    {
        // Detect network immediately when phone number changes
        $this->detectNetworkFromPhone();
        $this->validatePhoneNumber();
        
        // Log network detection
        Log::info('Network detection triggered via Livewire', [
            'short_code' => $this->shortCode,
            'phone_number' => substr($this->customerPhone, 0, 6) . '****' . substr($this->customerPhone, -2),
            'detected_network' => $this->detectedNetwork ? $this->detectedNetwork['name'] : 'None',
            'network_code' => $this->mobileNetwork
        ]);
    }

    // Real-time network detection as user types
    public function detectNetworkRealTime()
    {
        if (strlen($this->customerPhone) >= 12) {
            $this->detectNetworkFromPhone();
        }
    }

    public function detectNetworkFromPhone()
    {
        if (empty($this->customerPhone)) {
            $this->detectedNetwork = null;
            $this->mobileNetwork = '';
            return;
        }

        // Clean and format phone number
        $cleanNumber = $this->cleanPhoneNumber($this->customerPhone);
        
        // Update phone number if it was reformatted
        if ($cleanNumber !== $this->customerPhone) {
            $this->customerPhone = $cleanNumber;
        }

        $mobileNetworkService = app(MobileNetworkDetectionService::class);
        $this->detectedNetwork = $mobileNetworkService->detectNetwork($cleanNumber);
        
        if ($this->detectedNetwork) {
            $this->mobileNetwork = $this->detectedNetwork['network_code'];
        } else {
            $this->mobileNetwork = '';
        }

        // Network detection completed
    }

    private function cleanPhoneNumber($phoneNumber)
    {
        // Remove all non-numeric characters
        $cleanNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Handle different phone number formats
        if (strlen($cleanNumber) === 10 && $cleanNumber[0] === '0') {
            // Convert 0712345678 to 255712345678
            return '255' . substr($cleanNumber, 1);
        } elseif (strlen($cleanNumber) === 13 && substr($cleanNumber, 0, 3) === '255') {
            // Already in correct format
            return $cleanNumber;
        } elseif (strlen($cleanNumber) === 12 && substr($cleanNumber, 0, 3) === '255') {
            // Already in correct format
            return $cleanNumber;
        }
        
        // If format is not recognized, return as is
        return $cleanNumber;
    }

    public function validatePhoneNumber()
    {
        $this->validationErrors = [];
        
        if (!empty($this->customerPhone)) {
            $validator = Validator::make(
                ['phone' => $this->customerPhone],
                ['phone' => 'regex:/^255[0-9]{9}$/']
            );

            if ($validator->fails()) {
                $this->validationErrors['customerPhone'] = 'Phone number must be in format: 255712345678';
            }
        }
    }

    public function togglePayAll()
    {
        $this->payAll = !$this->payAll;
        
        if ($this->payAll) {
            $this->selectedItems = $this->paymentLink->items->pluck('item_code')->toArray();
            $this->paymentAmount = $this->paymentLink->amount;
        } else {
            $this->selectedItems = [];
            $this->paymentAmount = 0;
        }
    }

    public function toggleItem($itemCode)
    {
        if (in_array($itemCode, $this->selectedItems)) {
            $this->selectedItems = array_diff($this->selectedItems, [$itemCode]);
        } else {
            $this->selectedItems[] = $itemCode;
        }
        
        $this->updatePaymentAmount();
    }

    public function updatePaymentAmount()
    {
        if (empty($this->selectedItems)) {
            $this->paymentAmount = 0;
            return;
        }

        $totalAmount = 0;
        foreach ($this->paymentLink->items as $item) {
            if (in_array($item->item_code, $this->selectedItems)) {
                $totalAmount += $item->remaining_amount;
            }
        }
        
        $this->paymentAmount = $totalAmount;
    }

    public function updateSelectedItems($itemCodes)
    {
        $this->selectedItems = $itemCodes;
        $this->updatePaymentAmount();
    }

    public function updateNetwork($networkCode)
    {
        $this->mobileNetwork = $networkCode;
    }

    public function processPayment()
    {
        $this->isProcessing = true;
        $this->processingMessage = 'Validating payment details...';
        $this->validationErrors = [];
        $this->showError = false;
        $this->showSuccess = false;

        try {
            // Validate form
            $this->validatePaymentData();
            
            if (!empty($this->validationErrors)) {
                $this->showError = true;
                $this->errorMessage = 'Please correct the errors below.';
                $this->isProcessing = false;
                return;
            }

            $this->processingMessage = 'Preparing payment request...';

            // Prepare payment data
            $paymentData = [
                'customer_phone' => $this->customerPhone,
                'mobile_network' => $this->mobileNetwork,
                'amount' => $this->paymentAmount,
                'customer_name' => $this->customerName,
                'customer_email' => $this->customerEmail,
                'selected_items' => $this->selectedItems,
                'pay_all' => $this->payAll,
                'items' => $this->paymentLink->items->whereIn('item_code', $this->selectedItems)->map(function($item) {
                    return [
                        'item_code' => $item->item_code,
                        'amount' => $item->amount // Use the full item amount, not remaining_amount
                    ];
                })->toArray()
            ];

            $this->processingMessage = 'Processing payment...';

            // Process payment
            $universalPaymentService = app(UniversalPaymentLinkService::class);
            $result = $universalPaymentService->processUniversalPayment($this->paymentLink, $paymentData);

            if ($result['success']) {
                $this->transactionId = $result['data']['transaction_id'] ?? '';
                
                // Start waiting for callback instead of showing immediate success
                $this->startWaitingForCallback();
                
                Log::info('Payment initiated successfully via Livewire, waiting for callback', [
                    'short_code' => $this->shortCode,
                    'transaction_id' => $this->transactionId,
                    'amount' => $this->paymentAmount,
                    'selected_items' => $this->selectedItems
                ]);
            } else {
                $this->showError = true;
                $this->errorMessage = $this->getUserFriendlyErrorMessage($result['error'] ?? 'Payment processing failed. Please try again.');
                
                Log::error('Payment processing failed via Livewire', [
                    'short_code' => $this->shortCode,
                    'error' => $result['error'],
                    'amount' => $this->paymentAmount
                ]);
            }

        } catch (\Exception $e) {
            $this->showError = true;
            $this->errorMessage = $this->getUserFriendlyErrorMessage($e->getMessage());
            
            Log::error('Payment processing exception via Livewire', [
                'short_code' => $this->shortCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        $this->isProcessing = false;
    }

    private function validatePaymentData()
    {
        $rules = [
            'customerName' => 'required|string|max:255',
            'customerPhone' => 'required|regex:/^255[0-9]{9}$/',
            'customerEmail' => 'nullable|email',
            'mobileNetwork' => 'required|string',
            'paymentAmount' => 'required|numeric|min:100',
            'selectedItems' => 'required|array|min:1'
        ];

        $messages = [
            'customerName.required' => 'Full name is required.',
            'customerPhone.required' => 'Phone number is required.',
            'customerPhone.regex' => 'Phone number must be in format: 255712345678',
            'customerEmail.email' => 'Please enter a valid email address.',
            'mobileNetwork.required' => 'Please select a mobile money network.',
            'paymentAmount.required' => 'Payment amount is required.',
            'paymentAmount.min' => 'Minimum payment amount is TZS 100.',
            'selectedItems.required' => 'Please select at least one item to pay for.',
            'selectedItems.min' => 'Please select at least one item to pay for.'
        ];

        $validator = Validator::make([
            'customerName' => $this->customerName,
            'customerPhone' => $this->customerPhone,
            'customerEmail' => $this->customerEmail,
            'mobileNetwork' => $this->mobileNetwork,
            'paymentAmount' => $this->paymentAmount,
            'selectedItems' => $this->selectedItems
        ], $rules, $messages);

        if ($validator->fails()) {
            $this->validationErrors = $validator->errors()->toArray();
        }

        // Additional validation for payment amount
        if ($this->paymentAmount > $this->paymentLink->amount) {
            $this->validationErrors['paymentAmount'] = ['Payment amount cannot exceed the total amount.'];
        }

        if ($this->paymentAmount < ($this->paymentLink->minimum_amount ?? 100)) {
            $this->validationErrors['paymentAmount'] = ['Payment amount is below the minimum required.'];
        }
    }

    public function getSelectedItemsProperty()
    {
        if (!$this->paymentLink || !$this->paymentLink->items) {
            return collect();
        }
        return $this->paymentLink->items->whereIn('item_code', $this->selectedItems);
    }

    public function getTotalSelectedAmountProperty()
    {
        if (!$this->paymentLink || !$this->paymentLink->items) {
            return 0;
        }
        $total = 0;
        foreach ($this->paymentLink->items as $item) {
            if (in_array($item->item_code, $this->selectedItems)) {
                $total += $item->remaining_amount;
            }
        }
        return $total;
    }

    public function getCanPayAllProperty()
    {
        return $this->paymentLink->items->count() > 1;
    }

    public function getIsPublicLinkProperty()
    {
        if (!$this->paymentLink) {
            return false;
        }
        return $this->paymentLink->metadata['is_public_link'] ?? false;
    }

    private function getUserFriendlyErrorMessage($error)
    {
        // Handle specific error patterns
        if (strpos($error, 'DUPLICATE_REQUEST') !== false) {
            return 'This payment has already been processed. Please check your payment status or contact support if you believe this is an error.';
        }
        
        if (strpos($error, 'VALIDATION_ERROR') !== false) {
            return 'Payment request validation failed. Please check your payment details and try again.';
        }
        
        if (strpos($error, 'INSUFFICIENT_FUNDS') !== false) {
            return 'Insufficient funds in your mobile money account. Please ensure you have enough balance and try again.';
        }
        
        if (strpos($error, 'INVALID_PHONE') !== false) {
            return 'Invalid phone number format. Please enter a valid Tanzanian mobile number (e.g., 255712345678).';
        }
        
        if (strpos($error, 'NETWORK_ERROR') !== false) {
            return 'Network connection error. Please check your internet connection and try again.';
        }
        
        if (strpos($error, 'TIMEOUT') !== false) {
            return 'Payment request timed out. Please try again in a few moments.';
        }
        
        if (strpos($error, 'SERVICE_UNAVAILABLE') !== false) {
            return 'Mobile money service is temporarily unavailable. Please try again later.';
        }
        
        if (strpos($error, 'INVALID_AMOUNT') !== false) {
            return 'Invalid payment amount. Please check the amount and try again.';
        }
        
        if (strpos($error, 'TRANSACTION_FAILED') !== false) {
            return 'Transaction failed. Please try again or contact support if the problem persists.';
        }
        
        // Default user-friendly message
        return 'Payment processing failed. Please try again or contact support if the problem persists.';
    }

    /**
     * Check transaction status from callback
     */
    public function checkTransactionStatus()
    {
        if (empty($this->transactionId)) {
            return;
        }

        try {
            $response = Http::timeout(10)->get(route('api.callback.status', $this->transactionId));
            
            if ($response->successful()) {
                $data = $response->json();
                $this->callbackStatus = $data['status'] ?? 'pending';
                $this->lastStatusCheck = now();
                
                Log::info('Transaction status checked', [
                    'transaction_id' => $this->transactionId,
                    'status' => $this->callbackStatus,
                    'short_code' => $this->shortCode
                ]);
                
                // Handle different statuses
                switch ($this->callbackStatus) {
                    case 'success':
                        $this->handleSuccessfulPayment($data);
                        break;
                    case 'failed':
                        $this->handleFailedPayment($data);
                        break;
                    case 'timeout':
                        $this->handleTimeoutPayment($data);
                        break;
                    case 'cancelled':
                        $this->handleCancelledPayment($data);
                        break;
                    default:
                        // Still pending, continue waiting
                        break;
                }
            } else {
                Log::warning('Failed to check transaction status', [
                    'transaction_id' => $this->transactionId,
                    'status_code' => $response->status()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error checking transaction status', [
                'transaction_id' => $this->transactionId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle successful payment callback
     */
    protected function handleSuccessfulPayment($data)
    {
        $this->isWaitingForCallback = false;
        $this->isProcessing = false;
        $this->showSuccess = true;
        $this->successMessage = 'Payment completed successfully! Your transaction has been processed.';
        
        // Update payment link usage
        if ($this->paymentLink) {
            $this->paymentLink->incrementUses();
        }
        
        Log::info('Payment completed successfully via callback', [
            'transaction_id' => $this->transactionId,
            'short_code' => $this->shortCode,
            'amount' => $data['amount'] ?? $this->paymentAmount
        ]);
    }

    /**
     * Handle failed payment callback
     */
    protected function handleFailedPayment($data)
    {
        $this->isWaitingForCallback = false;
        $this->isProcessing = false;
        $this->showError = true;
        $this->errorMessage = 'Payment failed. Please try again or contact support if the problem persists.';
        
        Log::info('Payment failed via callback', [
            'transaction_id' => $this->transactionId,
            'short_code' => $this->shortCode,
            'aggregator_status' => $data['aggregator_status'] ?? 'unknown'
        ]);
    }

    /**
     * Handle timeout payment callback
     */
    protected function handleTimeoutPayment($data)
    {
        $this->isWaitingForCallback = false;
        $this->isProcessing = false;
        $this->showError = true;
        $this->errorMessage = 'Payment request timed out. Please try again.';
        
        Log::info('Payment timed out via callback', [
            'transaction_id' => $this->transactionId,
            'short_code' => $this->shortCode
        ]);
    }

    /**
     * Handle cancelled payment callback
     */
    protected function handleCancelledPayment($data)
    {
        $this->isWaitingForCallback = false;
        $this->isProcessing = false;
        $this->showError = true;
        $this->errorMessage = 'Payment was cancelled. Please try again if you wish to complete the payment.';
        
        Log::info('Payment cancelled via callback', [
            'transaction_id' => $this->transactionId,
            'short_code' => $this->shortCode
        ]);
    }

    /**
     * Start waiting for callback
     */
    public function startWaitingForCallback()
    {
        $this->isWaitingForCallback = true;
        $this->isProcessing = false;
        $this->processingMessage = 'Payment initiated successfully! Waiting for confirmation...';
        
        Log::info('Started waiting for callback', [
            'transaction_id' => $this->transactionId,
            'short_code' => $this->shortCode
        ]);
    }

    public function render()
    {
        return view('livewire.public.payment-page')
            ->layout('layouts.guest');
    }
}
