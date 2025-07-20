<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100" 
     @if($isWaitingForCallback) wire:poll.10s="checkTransactionStatus" @endif>
    <!-- Mobile-Optimized Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14 sm:h-16">
                <div class="flex items-center space-x-2 sm:space-x-3">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-credit-card text-white text-sm sm:text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-lg sm:text-xl font-bold text-gray-900">ZIMA PAY</h1>
                        <p class="text-xs sm:text-sm text-gray-600 hidden sm:block">Secure Payment Gateway</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-xs sm:text-sm text-gray-600">Powered by</div>
                    <div class="font-semibold text-gray-900 text-sm sm:text-base">Tanzania Mobile Money</div>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile-Optimized Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <!-- Payment Header - Mobile Optimized -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-4">
            <div class="text-center mb-4 sm:mb-6">
                <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-r from-green-500 to-emerald-600 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                    <i class="fas fa-receipt text-xl sm:text-2xl text-white"></i>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">{{ $paymentLink->description }}</h2>
                <p class="text-sm sm:text-base text-gray-600">Complete your payment securely</p>
            </div>

            <!-- Payment Details - Mobile Optimized -->
            <div class="space-y-3 sm:space-y-4">
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <span class="text-sm sm:text-base text-gray-600">Total Amount:</span>
                    <span class="text-xl sm:text-2xl font-bold text-gray-900">TZS {{ number_format($paymentLink->amount) }}</span>
                </div>
                
                @if($paymentLink->customer_name)
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm sm:text-base text-gray-600">Customer:</span>
                    <span class="font-medium text-gray-900 text-sm sm:text-base">{{ $paymentLink->customer_name }}</span>
                </div>
                @endif
                
                @if($paymentLink->customer_phone)
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm sm:text-base text-gray-600">Phone:</span>
                    <div class="flex items-center space-x-2">
                        <span class="font-medium text-gray-900 text-sm sm:text-base">{{ $paymentLink->customer_phone }}</span>
                        @if($detectedNetwork)
                        <div class="flex items-center space-x-1">
                            <div class="w-2 h-2 sm:w-3 sm:h-3 rounded-full" style="background-color: {{ $detectedNetwork['color'] }}"></div>
                            <span class="text-xs sm:text-sm text-gray-600">{{ $detectedNetwork['name'] }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
                
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm sm:text-base text-gray-600">Reference:</span>
                    <span class="font-medium text-gray-900 text-sm sm:text-base">{{ $paymentLink->metadata['customer_reference'] ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- Items Selection - Mobile Optimized -->
        @if($paymentLink->items->count() > 1)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 space-y-3 sm:space-y-0">
                <h3 class="text-lg font-semibold text-gray-900">Select Items to Pay</h3>
                @if($this->canPayAll)
                <button wire:click="togglePayAll" 
                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors touch-manipulation">
                    <i class="fas {{ $payAll ? 'fa-check-square' : 'fa-square' }} mr-2"></i>
                    {{ $payAll ? 'Paying All' : 'Pay All' }}
                </button>
                @endif
            </div>

            <div class="space-y-3">
                @foreach($paymentLink->items as $item)
                <div class="border border-gray-200 rounded-lg p-3 sm:p-4 {{ in_array($item->item_code, $selectedItems) ? 'border-indigo-300 bg-indigo-50' : 'bg-gray-50' }} transition-all duration-200">
                    <div class="flex items-start space-x-3">
                        <button wire:click="toggleItem('{{ $item->item_code }}')" 
                                class="mt-1 flex-shrink-0 p-1 touch-manipulation">
                            <i class="fas {{ in_array($item->item_code, $selectedItems) ? 'fa-check-square text-indigo-600' : 'fa-square text-gray-400' }} text-lg transition-colors"></i>
                        </button>
                        
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between space-y-2 sm:space-y-0">
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-gray-900 mb-1">{{ $item->item_name }}</h4>
                                    @if($item->item_description)
                                    <p class="text-xs sm:text-sm text-gray-600 mb-2">{{ $item->item_description }}</p>
                                    @endif
                                    
                                    @if($item->metadata['product_service_reference'] ?? false)
                                    <div class="text-xs text-gray-500 mb-2">
                                        <strong>Item Code:</strong> {{ $item->metadata['product_service_reference'] }}
                                    </div>
                                    @endif
                                    
                                    <div class="flex flex-wrap items-center gap-2">
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
                                
                                <div class="text-right sm:text-right sm:ml-4">
                                    <div class="font-semibold text-gray-900 text-sm sm:text-base">TZS {{ number_format($item->amount) }}</div>
                                    @if($item->paid_amount > 0)
                                    <div class="text-xs sm:text-sm text-green-600">Paid: TZS {{ number_format($item->paid_amount) }}</div>
                                    <div class="text-xs sm:text-sm text-gray-600">Remaining: TZS {{ number_format($item->remaining_amount) }}</div>
                                    @endif
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ ucfirst($item->category) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Payment Form - Mobile Optimized -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 sm:mb-6">Payment Details</h3>
            
            <!-- Success/Error Messages - Mobile Optimized -->
            @if($showSuccess)
            <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <span class="text-green-800 font-medium text-sm sm:text-base">{{ $successMessage }}</span>
                </div>
                @if($transactionId)
                <p class="text-xs sm:text-sm text-green-700 mt-1">Transaction ID: {{ $transactionId }}</p>
                @endif
            </div>
            @endif

            @if($showError)
            <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-exclamation-circle text-red-600"></i>
                    <span class="text-red-800 font-medium text-sm sm:text-base">{{ $errorMessage }}</span>
                </div>
            </div>
            @endif

            <!-- Processing State - Mobile Optimized -->
            @if($isProcessing)
            <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-spinner fa-spin text-blue-600"></i>
                    <span class="text-blue-800 font-medium text-sm sm:text-base">{{ $processingMessage }}</span>
                </div>
            </div>
            @endif

            <!-- Callback Waiting State - Mobile Optimized -->
            @if($isWaitingForCallback)
            <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-clock text-yellow-600"></i>
                    <span class="text-yellow-800 font-medium text-sm sm:text-base">{{ $processingMessage }}</span>
                </div>
                <div class="mt-2 text-xs sm:text-sm text-yellow-700">
                    <p>We're waiting for confirmation from your mobile money provider. This usually takes a few moments.</p>
                    @if($lastStatusCheck)
                    <p class="mt-1 text-xs">Last checked: {{ $lastStatusCheck->diffForHumans() }}</p>
                    @endif
                </div>
                <div class="mt-3">
                    <button wire:click="checkTransactionStatus" 
                            class="w-full sm:w-auto inline-flex items-center justify-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-yellow-800 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors touch-manipulation">
                        <i class="fas fa-sync-alt mr-2"></i>
                        Check Status
                    </button>
                </div>
            </div>
            @endif

            <!-- Customer Information - Mobile Optimized -->
            <div class="space-y-4 mb-4 sm:mb-6">
                @if(!$this->isPublicLink)
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start space-x-2">
                        <i class="fas fa-info-circle text-blue-600 mt-1"></i>
                        <div class="text-xs sm:text-sm text-blue-700">
                            <strong>Note:</strong> You can edit your phone number, email, and payment amount below.
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Customer Name - Mobile Optimized -->
                <div>
                    <label for="customerName" class="block text-sm font-medium text-gray-700 mb-2">
                        Full Name 
                        @if($this->isPublicLink)
                            <span class="text-red-500">*</span>
                        @endif
                    </label>
                    @if($this->isPublicLink)
                        <input type="text" 
                               wire:model.defer="customerName"
                               id="customerName"
                               class="w-full px-3 sm:px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors text-base"
                               placeholder="Enter your full name"
                               autocomplete="name">
                    @else
                        <input type="text" 
                               wire:model.defer="customerName"
                               id="customerName"
                               readonly
                               class="w-full px-3 sm:px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-600 text-base"
                               placeholder="Customer name">
                        <p class="text-xs text-gray-500 mt-1">This field cannot be edited</p>
                    @endif
                    @error('customerName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <!-- Phone Number - Mobile Optimized -->
                <div>
                    <label for="customerPhone" class="block text-sm font-medium text-gray-700 mb-2">
                        Phone Number <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" 
                           wire:model="customerPhone"
                           id="customerPhone"
                           class="w-full px-3 sm:px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors text-base"
                           placeholder="255712345678"
                           autocomplete="tel"
                           inputmode="numeric">
                    <p class="text-xs text-gray-500 mt-1">Format: 255712345678</p>
                    @error('customerPhone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    @if(isset($validationErrors['customerPhone']))
                        <span class="text-red-500 text-sm">{{ $validationErrors['customerPhone'] }}</span>
                    @endif
                </div>
                
                <!-- Email Address - Mobile Optimized -->
                <div>
                    <label for="customerEmail" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address (Optional)
                    </label>
                    <input type="email" 
                           wire:model.defer="customerEmail"
                           id="customerEmail"
                           class="w-full px-3 sm:px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors text-base"
                           placeholder="your.email@example.com"
                           autocomplete="email"
                           inputmode="email">
                    @error('customerEmail') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Mobile Network Display - Mobile Optimized -->
            <div class="mb-4 sm:mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Mobile Money Network
                </label>
                <div class="border-2 border-gray-200 rounded-lg p-3 sm:p-4 bg-gray-50">
                    @if($detectedNetwork)
                        <div class="flex items-center space-x-3">
                            <div class="text-xl sm:text-2xl">
                                <i class="{{ $detectedNetwork['icon'] }}" style="color: {{ $detectedNetwork['color'] }}"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-800 text-sm sm:text-base">{{ $detectedNetwork['name'] }}</div>
                                <div class="text-xs sm:text-sm text-gray-600">Automatically detected from your phone number</div>
                            </div>
                        </div>
                    @elseif(!empty($customerPhone) && strlen($customerPhone) >= 12)
                        <div class="flex items-center space-x-3">
                            <div class="text-xl sm:text-2xl text-gray-400">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-600 text-sm sm:text-base">Network not recognized</div>
                                <div class="text-xs sm:text-sm text-gray-500">Please check your phone number format or select network manually</div>
                            </div>
                        </div>
                    @elseif(!empty($customerPhone))
                        <div class="flex items-center space-x-3">
                            <div class="text-xl sm:text-2xl text-gray-400">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-600 text-sm sm:text-base">Detecting network...</div>
                                <div class="text-xs sm:text-sm text-gray-500">Please wait while we identify your mobile money provider</div>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center space-x-3">
                            <div class="text-xl sm:text-2xl text-gray-400">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-600 text-sm sm:text-base">Network will be detected automatically</div>
                                <div class="text-xs sm:text-sm text-gray-500">Enter your phone number above to detect your network</div>
                            </div>
                        </div>
                    @endif
                </div>
                <input type="hidden" wire:model="mobileNetwork">
                @error('mobileNetwork') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Payment Amount - Mobile Optimized -->
            @if($paymentLink->allow_partial_payment)
            <div class="mb-4 sm:mb-6">
                <label for="paymentAmount" class="block text-sm font-medium text-gray-700 mb-2">
                    Payment Amount <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-3 text-gray-500">TZS</span>
                    <input type="number" 
                           wire:model.defer="paymentAmount"
                           id="paymentAmount"
                           min="{{ $paymentLink->minimum_amount ?? 100 }}"
                           max="{{ $paymentLink->amount }}"
                           step="100"
                           class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors text-base"
                           inputmode="numeric">
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Min: TZS {{ number_format($paymentLink->minimum_amount ?? 100) }} | 
                    Max: TZS {{ number_format($paymentLink->amount) }}
                </p>
                @error('paymentAmount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                @if(isset($validationErrors['paymentAmount']))
                    <span class="text-red-500 text-sm">{{ $validationErrors['paymentAmount'][0] }}</span>
                @endif
            </div>
            @endif

            <!-- Selected Items Summary - Mobile Optimized -->
            @if($paymentLink->items->count() > 1 && !empty($selectedItems))
            <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-gray-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-900 mb-2">Selected Items ({{ count($selectedItems) }})</h4>
                <div class="space-y-2">
                    @foreach($selectedItems as $item)
                        @php $selectedItem = $paymentLink->items->where('item_code', $item)->first() @endphp
                        @if($selectedItem)
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">{{ $selectedItem->item_name }}</span>
                            <span class="font-medium text-gray-900">TZS {{ number_format($selectedItem->remaining_amount) }}</span>
                        </div>
                        @endif
                    @endforeach
                    <div class="border-t border-gray-200 pt-2 mt-2">
                        <div class="flex justify-between items-center font-medium">
                            <span class="text-gray-900">Total:</span>
                            <span class="text-lg text-gray-900">TZS {{ number_format($paymentAmount) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Submit Button - Mobile Optimized -->
            <button wire:click="processPayment" 
                    wire:loading.attr="disabled"
                    class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-4 px-6 rounded-lg text-lg transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none touch-manipulation">
                <div wire:loading.remove>
                    <i class="fas fa-lock mr-2"></i>
                    Pay TZS {{ number_format($paymentAmount) }}
                </div>
                <div wire:loading>
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                    Processing...
                </div>
            </button>

            <!-- Security Notice - Mobile Optimized -->
            <div class="mt-4 sm:mt-6 p-3 sm:p-4 bg-blue-50 rounded-lg">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-shield-alt text-blue-600 mt-1"></i>
                    <div>
                        <h4 class="font-medium text-blue-800 text-sm sm:text-base">Secure Payment</h4>
                        <p class="text-xs sm:text-sm text-blue-700 mt-1">
                            Your payment is processed securely through certified mobile money providers. 
                            We never store your payment details.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Instructions - Mobile Optimized -->
        <div class="mt-6 sm:mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Instructions</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
         
                <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <span class="text-indigo-600 font-semibold text-xs sm:text-sm">1</span>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 text-xs sm:text-sm">Click Pay</h4>
                        <p class="text-xs sm:text-sm text-gray-600">Review your payment details and click the pay button</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <span class="text-indigo-600 font-semibold text-xs sm:text-sm">2</span>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 text-xs sm:text-sm">USSD Prompt</h4>
                        <p class="text-xs sm:text-sm text-gray-600">You will receive a USSD prompt on your phone</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <span class="text-indigo-600 font-semibold text-xs sm:text-sm">3</span>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 text-xs sm:text-sm">Enter PIN</h4>
                        <p class="text-xs sm:text-sm text-gray-600">Enter your mobile money PIN to complete the transaction</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Mobile-Optimized Footer -->
    <footer class="bg-gray-800 text-white py-6 sm:py-8 mt-8 sm:mt-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="flex flex-col sm:flex-row justify-center space-y-2 sm:space-y-0 sm:space-x-6 mb-4">
                <div class="flex items-center justify-center space-x-2">
                    <i class="fas fa-shield-alt text-green-400"></i>
                    <span class="text-xs sm:text-sm">Secure</span>
                </div>
                <div class="flex items-center justify-center space-x-2">
                    <i class="fas fa-lock text-blue-400"></i>
                    <span class="text-xs sm:text-sm">Encrypted</span>
                </div>
                <div class="flex items-center justify-center space-x-2">
                    <i class="fas fa-check-circle text-green-400"></i>
                    <span class="text-xs sm:text-sm">Verified</span>
                </div>
            </div>
            <p class="text-xs sm:text-sm opacity-75">
                © 2025 ZIMA PAY. All payments are processed securely through certified mobile money providers.
            </p>
        </div>
    </footer>

    <!-- Livewire Scripts -->
    @livewireScripts
</div>
