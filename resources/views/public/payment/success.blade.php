@extends('public.payment.layout')

@section('content')
    <!-- Success Card -->
    <div class="bg-white rounded-lg card-shadow p-8 text-center">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-check-circle text-3xl text-green-600"></i>
        </div>
        
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Payment Successful!</h2>
        
        <p class="text-gray-600 mb-6">
            Your payment has been processed successfully. Thank you for your payment!
        </p>

        <!-- Payment Details -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Transaction ID:</span>
                    <span class="font-mono text-sm font-medium text-gray-800">{{ $transaction->transaction_id ?? 'N/A' }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Amount Paid:</span>
                    <span class="text-xl font-bold text-green-600">TZS {{ number_format($transaction->amount ?? 0) }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Payment Method:</span>
                    <span class="font-medium text-gray-800">
                        @if(str_contains($transaction->mobile_network ?? '', 'MPESA'))
                            M-Pesa
                        @elseif(str_contains($transaction->mobile_network ?? '', 'AIRTEL'))
                            Airtel Money
                        @elseif(str_contains($transaction->mobile_network ?? '', 'TIGO'))
                            Tigo Pesa
                        @elseif(str_contains($transaction->mobile_network ?? '', 'HALOPESA'))
                            HaloPesa
                        @else
                            {{ $transaction->mobile_network ?? 'Mobile Money' }}
                        @endif
                    </span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Customer Phone:</span>
                    <span class="font-medium text-gray-800">{{ $transaction->customer_phone ?? 'N/A' }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Date & Time:</span>
                    <span class="font-medium text-gray-800">{{ $transaction->created_at ? $transaction->created_at->format('M d, Y H:i') : 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- Receipt Information -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-start space-x-3">
                <i class="fas fa-receipt text-blue-600 mt-1"></i>
                <div class="text-left">
                    <h4 class="font-medium text-blue-800">Receipt Information</h4>
                    <p class="text-sm text-blue-700 mt-1">
                        A receipt has been sent to your phone number. You can also download a copy below.
                    </p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-3">
            <button onclick="downloadReceipt()" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                <i class="fas fa-download mr-2"></i>
                Download Receipt
            </button>
            
            <button onclick="window.close()" 
                    class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                <i class="fas fa-times mr-2"></i>
                Close Window
            </button>
        </div>

        <!-- Additional Information -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="text-center">
                    <i class="fas fa-shield-alt text-green-600 text-xl mb-2"></i>
                    <p class="text-gray-600">Secure Transaction</p>
                </div>
                <div class="text-center">
                    <i class="fas fa-clock text-blue-600 text-xl mb-2"></i>
                    <p class="text-gray-600">Instant Processing</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Receipt Form -->
    <form id="receipt-form" method="POST" action="{{ route('public.payment.receipt', $transaction->transaction_id ?? '') }}" style="display: none;">
        @csrf
    </form>

    <script>
        function downloadReceipt() {
            document.getElementById('receipt-form').submit();
        }
    </script>
@endsection 