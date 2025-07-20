@extends('public.payment.layout')

@section('content')
    <!-- Payment Summary Card -->
    <div class="bg-white rounded-lg card-shadow p-6 mb-6">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-credit-card text-2xl text-green-600"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">{{ $paymentLink->description }}</h2>
            <p class="text-gray-600">Complete your payment securely</p>
        </div>

        <!-- Payment Purpose Banner -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg p-4 mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-receipt text-lg"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-lg">Payment for: {{ $paymentLink->description }}</h3>
                    @if($paymentLink->metadata['customer_reference'] ?? false)
                    <p class="text-blue-100 text-sm">Reference: {{ $paymentLink->metadata['customer_reference'] }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Payment Details -->
        <div class="space-y-4 mb-6">
            <!-- Payment Purpose Section -->

            
            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                <span class="text-gray-600">Total Amount:</span>
                <span class="text-2xl font-bold text-gray-800">TZS {{ number_format($paymentLink->amount) }}</span>
            </div>
            
            @if($paymentLink->customer_name)
            <div class="flex justify-between items-center py-2">
                <span class="text-gray-600">Customer:</span>
                <span class="font-medium text-gray-800">{{ $paymentLink->customer_name }}</span>
            </div>
            @endif
            
            @if($paymentLink->customer_phone)
            <div class="flex justify-between items-center py-2">
                <span class="text-gray-600">Phone:</span>
                <span class="font-medium text-gray-800">{{ $paymentLink->customer_phone }}</span>
                @if($detectedNetwork)
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 rounded-full" style="background-color: {{ $detectedNetwork['color'] }}"></div>
                    <span class="text-sm text-gray-600">{{ $detectedNetwork['name'] }}</span>
                </div>
                @endif
            </div>
            @endif
            
            <div class="flex justify-between items-center py-2">
                <span class="text-gray-600">Reference:</span>
                <span class="font-medium text-gray-800">{{ $paymentLink->metadata['customer_reference'] ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- Items Breakdown -->
        @if($paymentLink->items->count() > 1)
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">What You're Paying For</h3>
            <div class="space-y-3">
                @foreach($paymentLink->items as $item)
                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex-1">
                        <div class="font-medium text-gray-800 mb-1">{{ $item->item_name }}</div>
                        @if($item->item_description)
                        <div class="text-sm text-gray-600 mb-2">{{ $item->item_description }}</div>
                        @endif
                        @if($item->metadata['product_service_reference'] ?? false)
                        <div class="text-xs text-gray-500 mb-1">
                            <strong>Item Code:</strong> {{ $item->metadata['product_service_reference'] }}
                        </div>
                        @endif
                        <div class="text-xs text-gray-500">
                            @if($item->allow_partial)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Partial payment allowed
                                    @if($item->minimum_amount)
                                        (Min: TZS {{ number_format($item->minimum_amount) }})
                                    @endif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    Full payment required
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right ml-4">
                        <div class="font-semibold text-gray-800 text-lg">TZS {{ number_format($item->amount) }}</div>
                        @if($item->paid_amount > 0)
                        <div class="text-sm text-green-600">Paid: TZS {{ number_format($item->paid_amount) }}</div>
                        @endif
                        <div class="text-xs text-gray-500 mt-1">
                            {{ ucfirst($item->category) }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Payment Form -->
    <div class="bg-white rounded-lg card-shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment Details</h3>
        
        <form id="payment-form" method="POST" action="{{ route('public.payment.process', $paymentLink->short_code) }}">
            @csrf
            
            <!-- Customer Information -->
            <div class="space-y-4 mb-6">
                @if(!($paymentLink->metadata['is_public_link'] ?? false))
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg mb-4">
                    <div class="flex items-start space-x-2">
                        <i class="fas fa-info-circle text-blue-600 mt-1"></i>
                        <div class="text-sm text-blue-700">
                            <strong>Note:</strong> You can edit your phone number, email, and payment amount below.
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Customer Name (Read-only for individual, editable for public) -->
                <div>
                    <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Full Name 
                        @if($paymentLink->metadata['is_public_link'] ?? false)
                            <span class="text-red-500">*</span>
                        @endif
                    </label>
                    @if($paymentLink->metadata['is_public_link'] ?? false)
                        <input type="text" 
                               id="customer_name" 
                               name="customer_name" 
                               required
                               value="{{ $paymentLink->customer_name ?? '' }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Enter your full name">
                    @else
                        <input type="text" 
                               id="customer_name" 
                               name="customer_name" 
                               value="{{ $paymentLink->customer_name ?? '' }}"
                               readonly
                               class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-600"
                               placeholder="Customer name">
                        <p class="text-xs text-gray-500 mt-1">This field cannot be edited</p>
                    @endif
                </div>
                
                <!-- Phone Number (Always editable) -->
                <div>
                    <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Phone Number <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" 
                           id="customer_phone" 
                           name="customer_phone" 
                           required
                           pattern="^255[0-9]{9}$"
                           value="{{ $paymentLink->customer_phone ?? '' }}"
                           oninput="detectNetworkFromPhone(this.value)"
                           onchange="detectNetworkFromPhone(this.value)"
                           onblur="detectNetworkFromPhone(this.value)"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="255712345678">
                    <p class="text-xs text-gray-500 mt-1">Format: 255712345678</p>
                </div>
                
                <!-- Email Address (Always editable) -->
                <div>
                    <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address (Optional)
                    </label>
                    <input type="email" 
                           id="customer_email" 
                           name="customer_email"
                           value="{{ $paymentLink->customer_email ?? '' }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="your.email@example.com">
                </div>
            </div>

            <!-- Mobile Network Display (Auto-detected) -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Mobile Money Network
                </label>
                <div id="network-display" class="border-2 border-gray-200 rounded-lg p-4 bg-gray-50">
                    @if($detectedNetwork)
                        <div class="flex items-center space-x-3">
                            <div class="text-2xl">
                                <i class="{{ $detectedNetwork['icon'] }}" style="color: {{ $detectedNetwork['color'] }}"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-800">{{ $detectedNetwork['name'] }}</div>
                                <div class="text-sm text-gray-600">Automatically detected from your phone number</div>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center space-x-3">
                            <div class="text-2xl text-gray-400">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-600">Network will be detected automatically</div>
                                <div class="text-sm text-gray-500">Enter your phone number above to detect your network</div>
                            </div>
                        </div>
                    @endif
                </div>
                <input type="hidden" id="mobile_network" name="mobile_network" value="{{ $detectedNetwork['network_code'] ?? '' }}" required>
            </div>

            <!-- Payment Amount -->
            @if($paymentLink->allow_partial_payment)
            <div class="mb-6">
                <label for="payment_amount" class="block text-sm font-medium text-gray-700 mb-2">
                    Payment Amount <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-3 text-gray-500">TZS</span>
                    <input type="number" 
                           id="payment_amount" 
                           name="payment_amount" 
                           required
                           min="{{ $paymentLink->minimum_amount ?? 100 }}"
                           max="{{ $paymentLink->amount }}"
                           value="{{ $paymentLink->amount }}"
                           step="100"
                           data-min-amount="{{ $paymentLink->minimum_amount ?? 100 }}"
                           data-max-amount="{{ $paymentLink->amount }}"
                           onchange="validateAmount(this)"
                           class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Min: TZS {{ number_format($paymentLink->minimum_amount ?? 100) }} | 
                    Max: TZS {{ number_format($paymentLink->amount) }}
                </p>
            </div>
            @endif

            <!-- Submit Button -->
            <button type="button" 
                    id="submit-btn"
                    onclick="submitPayment()"
                    class="w-full btn-primary text-white font-semibold py-4 px-6 rounded-lg text-lg">
                <i class="fas fa-lock mr-2"></i>
                Pay TZS {{ number_format($paymentLink->amount) }}
            </button>
        </form>

        <!-- Security Notice -->
        <div class="mt-6 p-4 bg-blue-50 rounded-lg">
            <div class="flex items-start space-x-3">
                <i class="fas fa-shield-alt text-blue-600 mt-1"></i>
                <div>
                    <h4 class="font-medium text-blue-800">Secure Payment</h4>
                    <p class="text-sm text-blue-700 mt-1">
                        Your payment is processed securely through certified mobile money providers. 
                        We never store your payment details.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Instructions -->
    <div class="bg-white rounded-lg card-shadow p-6 mt-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment Instructions</h3>
        <div class="space-y-3 text-sm text-gray-600">
            <div class="flex items-start space-x-3">
                <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-blue-600 font-semibold text-xs">1</span>
                </div>
                <p>Select your preferred mobile money network above</p>
            </div>
            <div class="flex items-start space-x-3">
                <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-blue-600 font-semibold text-xs">2</span>
                </div>
                <p>Click "Pay" to proceed with the payment</p>
            </div>
            <div class="flex items-start space-x-3">
                <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-blue-600 font-semibold text-xs">3</span>
                </div>
                <p>You will receive a USSD prompt on your phone</p>
            </div>
            <div class="flex items-start space-x-3">
                <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-blue-600 font-semibold text-xs">4</span>
                </div>
                <p>Enter your PIN to complete the transaction</p>
            </div>
        </div>
    </div>

    <!-- Immediate Network Detection Script -->
    <script>
        // Run network detection immediately if phone number is pre-filled
        (function() {
            const phoneInput = document.getElementById('customer_phone');
            if (phoneInput && phoneInput.value) {
                console.log('Immediate detection for:', phoneInput.value);
                setTimeout(function() {
                    detectNetworkFromPhone(phoneInput.value);
                }, 100);
            }
        })();
    </script>
@endsection 