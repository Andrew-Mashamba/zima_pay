<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100" 
     @if($isWaitingForCallback) wire:poll.10s="checkTransactionStatus" @endif>
    <!-- Compact Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
        
        <div class="max-w-4xl mx-auto px-2">
            <div class="flex items-center justify-between h-12">
                <div class="flex items-center space-x-2">
                    <div class="w-7 h-7 bg-gradient-to-r from-blue-900 to-blue-700 rounded flex items-center justify-center">
                        <i class="fas fa-credit-card text-white text-xs"></i>
                    </div>
                    <h1 class="text-base font-bold text-gray-900">ZIMA PAY</h1>
                </div>
                <div class="text-xs text-gray-600 font-medium">Secure Payment</div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-2 py-4">
        <!-- Payment Summary -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-3">
            <h2 class="text-base font-bold text-gray-900 mb-3 flex items-center">
                <i class="fas fa-receipt text-blue-900 mr-2 text-sm"></i>
                {{ $paymentLink->description }}
            </h2>
            
            <div class="grid grid-cols-2 gap-2 text-sm">
                <div class="text-gray-600">Amount:</div>
                <div class="font-bold text-gray-900 text-right">TZS {{ number_format($paymentLink->amount) }}</div>
                
                @if($paymentLink->customer_name)
                <div class="text-gray-600">Customer:</div>
                <div class="text-gray-900 text-right">{{ $paymentLink->customer_name }}</div>
                @endif
                
                @if($paymentLink->metadata['customer_reference'])
                <div class="text-gray-600">Reference:</div>
                <div class="text-gray-900 text-right">{{ $paymentLink->metadata['customer_reference'] }}</div>
                @endif
            </div>
        </div>

        <!-- Items Selection (if multiple) -->
        @if($paymentLink->items->count() > 1)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-3">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">Select Items</h3>
                @if($this->canPayAll)
                <button wire:click="togglePayAll" 
                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded text-white bg-blue-900 hover:bg-blue-800">
                    <i class="fas {{ $payAll ? 'fa-check-square' : 'fa-square' }} mr-1 text-xs"></i>
                    {{ $payAll ? 'All Selected' : 'Select All' }}
                </button>
                @endif
            </div>

            <div class="space-y-2">
                @foreach($paymentLink->items as $item)
                <div class="border border-gray-200 rounded p-2 {{ in_array($item->item_code, $selectedItems) ? 'border-blue-900 bg-blue-50' : 'bg-gray-50' }}">
                    <div class="flex items-center space-x-2">
                        <button wire:click="toggleItem('{{ $item->item_code }}')" class="flex-shrink-0">
                            <i class="fas {{ in_array($item->item_code, $selectedItems) ? 'fa-check-square text-blue-900' : 'fa-square text-gray-400' }} text-sm"></i>
                        </button>
                        
                        <div class="flex-1 grid grid-cols-2 gap-1">
                            <div>
                                <h4 class="text-xs font-medium text-gray-900">{{ $item->item_name }}</h4>
                                @if($item->allow_partial)
                                <span class="text-xs text-green-700">Partial OK</span>
                                @endif
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-semibold text-gray-900">TZS {{ number_format($item->amount) }}</div>
                                @if($item->paid_amount > 0)
                                <div class="text-xs text-gray-600">Balance: {{ number_format($item->remaining_amount) }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Payment Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                <i class="fas fa-user-circle text-blue-900 mr-2 text-sm"></i>
                Payment Details
            </h3>
            
            <!-- Status Messages -->
            @if($showSuccess)
            <div class="mb-3 p-2 bg-green-50 border border-green-200 rounded">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-check-circle text-green-600 text-xs"></i>
                    <span class="text-green-800 text-xs font-medium">{{ $successMessage }}</span>
                </div>
                @if($transactionId)
                <p class="text-xs text-green-700 mt-1">ID: {{ $transactionId }}</p>
                @endif
            </div>
            @endif

            @if($showError)
            <div class="mb-3 p-2 bg-red-50 border border-red-200 rounded">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-exclamation-circle text-red-600 text-xs"></i>
                    <span class="text-red-800 text-xs font-medium">{{ $errorMessage }}</span>
                </div>
            </div>
            @endif

            @if($isProcessing)
            <div class="mb-3 p-2 bg-blue-50 border border-blue-200 rounded">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-spinner fa-spin text-blue-600 text-xs"></i>
                    <span class="text-blue-800 text-xs font-medium">{{ $processingMessage }}</span>
                </div>
            </div>
            @endif

            @if($isWaitingForCallback)
            <div class="mb-3 p-2 bg-yellow-50 border border-yellow-200 rounded">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-clock text-yellow-600 text-xs"></i>
                    <span class="text-yellow-800 text-xs font-medium">{{ $processingMessage }}</span>
                </div>
                <button wire:click="checkTransactionStatus" 
                        class="mt-2 px-2 py-1 text-xs font-medium rounded text-yellow-800 bg-yellow-100 hover:bg-yellow-200">
                    <i class="fas fa-sync-alt mr-1 text-xs"></i>
                    Check Status
                </button>
            </div>
            @endif

            <!-- Customer Information -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                <!-- Customer Name -->
                <div>
                    <label for="customerName" class="block text-xs font-medium text-gray-700 mb-1">
                        Full Name @if($this->isPublicLink)<span class="text-red-500">*</span>@endif
                    </label>
                    <input type="text" 
                           wire:model.defer="customerName"
                           id="customerName"
                           @if(!$this->isPublicLink) readonly @endif
                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-900 @if(!$this->isPublicLink) bg-gray-50 @endif"
                           placeholder="Enter name"
                           autocomplete="name">
                    @error('customerName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <!-- Phone Number -->
                <div>
                    <label for="customerPhone" class="block text-xs font-medium text-gray-700 mb-1">
                        Phone Number <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" 
                           wire:model="customerPhone"
                           id="customerPhone"
                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-900"
                           placeholder="255712345678"
                           autocomplete="tel"
                           inputmode="numeric">
                    @error('customerPhone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <!-- Email Address -->
                <div>
                    <label for="customerEmail" class="block text-xs font-medium text-gray-700 mb-1">
                        Email (Optional)
                    </label>
                    <input type="email" 
                           wire:model.defer="customerEmail"
                           id="customerEmail"
                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-900"
                           placeholder="email@example.com"
                           autocomplete="email"
                           inputmode="email">
                    @error('customerEmail') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Network Display -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        Network
                    </label>
                    <div class="border border-gray-200 rounded p-2 bg-gray-50">
                        @if($detectedNetwork)
                            <div class="flex items-center space-x-2">
                                <i class="{{ $detectedNetwork['icon'] }} text-sm" style="color: {{ $detectedNetwork['color'] }}"></i>
                                <span class="text-xs font-medium text-gray-800">{{ $detectedNetwork['name'] }}</span>
                            </div>
                        @elseif(!empty($customerPhone) && strlen($customerPhone) >= 12)
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-question-circle text-gray-400 text-sm"></i>
                                <span class="text-xs text-gray-600">Unknown network</span>
                            </div>
                        @else
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-mobile-alt text-gray-400 text-sm"></i>
                                <span class="text-xs text-gray-600">Enter phone number</span>
                            </div>
                        @endif
                    </div>
                    <input type="hidden" wire:model="mobileNetwork">
                    @error('mobileNetwork') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Payment Amount -->
            @if($paymentLink->allow_partial_payment)
            <div class="mb-3">
                <label for="paymentAmount" class="block text-xs font-medium text-gray-700 mb-1">
                    Payment Amount <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-2 top-1.5 text-gray-500 text-sm">TZS</span>
                    <input type="number" 
                           wire:model.defer="paymentAmount"
                           id="paymentAmount"
                           min="{{ $paymentLink->minimum_amount ?? 100 }}"
                           max="{{ $paymentLink->amount }}"
                           step="100"
                           class="w-full pl-10 pr-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-900"
                           inputmode="numeric">
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Min: {{ number_format($paymentLink->minimum_amount ?? 100) }} | Max: {{ number_format($paymentLink->amount) }}
                </p>
                @error('paymentAmount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            @endif

            <!-- Selected Items Summary -->
            @if($paymentLink->items->count() > 1 && !empty($selectedItems))
            <div class="mb-3 p-2 bg-blue-50 rounded border border-blue-200">
                <div class="flex justify-between items-center">
                    <span class="text-xs font-medium text-blue-900">Total ({{ count($selectedItems) }} items):</span>
                    <span class="text-sm font-bold text-blue-900">TZS {{ number_format($paymentAmount) }}</span>
                </div>
            </div>
            @endif

            <!-- Submit Button -->
            <button wire:click="processPayment" 
                    wire:loading.attr="disabled"
                    class="w-full bg-blue-900 hover:bg-blue-800 text-white font-semibold py-2 px-4 rounded text-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <div wire:loading.remove>
                    <i class="fas fa-lock mr-1 text-xs"></i>
                    Pay TZS {{ number_format($paymentAmount) }}
                </div>
                <div wire:loading>
                    <i class="fas fa-spinner fa-spin mr-1 text-xs"></i>
                    Processing...
                </div>
            </button>

            <!-- Instructions -->
            <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                <div class="text-center">
                    <div class="w-6 h-6 bg-blue-900 text-white rounded-full flex items-center justify-center mx-auto mb-1">
                        <span class="text-xs">1</span>
                    </div>
                    <p class="text-gray-600">Click Pay</p>
                </div>
                <div class="text-center">
                    <div class="w-6 h-6 bg-blue-900 text-white rounded-full flex items-center justify-center mx-auto mb-1">
                        <span class="text-xs">2</span>
                    </div>
                    <p class="text-gray-600">USSD Prompt</p>
                </div>
                <div class="text-center">
                    <div class="w-6 h-6 bg-blue-900 text-white rounded-full flex items-center justify-center mx-auto mb-1">
                        <span class="text-xs">3</span>
                    </div>
                    <p class="text-gray-600">Enter PIN</p>
                </div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-4 mt-6">
        <div class="max-w-4xl mx-auto px-2 text-center">
            <div class="flex justify-center space-x-4 mb-2">
                <span class="text-xs flex items-center">
                    <i class="fas fa-shield-alt text-green-400 mr-1 text-xs"></i>
                    Secure
                </span>
                <span class="text-xs flex items-center">
                    <i class="fas fa-lock text-blue-400 mr-1 text-xs"></i>
                    Encrypted
                </span>
            </div>
            <p class="text-xs opacity-75">
                © 2025 ZIMA PAY - Secure Mobile Money Payments
            </p>
        </div>
    </footer>

    <!-- Livewire Scripts -->
    @livewireScripts
</div>
