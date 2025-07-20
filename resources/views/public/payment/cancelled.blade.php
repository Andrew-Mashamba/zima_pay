@extends('public.payment.layout')

@section('content')
    <!-- Cancelled Card -->
    <div class="bg-white rounded-lg card-shadow p-8 text-center">
        <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-ban text-3xl text-yellow-600"></i>
        </div>
        
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Payment Cancelled</h2>
        
        <p class="text-gray-600 mb-6">
            Your payment was cancelled. No charges have been made to your account.
        </p>

        <!-- Cancellation Details -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
            <div class="flex items-start space-x-3">
                <i class="fas fa-info-circle text-yellow-600 mt-1"></i>
                <div class="text-left">
                    <h4 class="font-medium text-yellow-800">What happened?</h4>
                    <p class="text-sm text-yellow-700 mt-1">
                        You cancelled the payment process. Your account has not been charged 
                        and you can try again whenever you're ready.
                    </p>
                </div>
            </div>
        </div>

        <!-- Payment Link Details -->
        @if($paymentLink)
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-6">
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Payment For:</span>
                    <span class="font-medium text-gray-800">{{ $paymentLink->description }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Amount:</span>
                    <span class="text-xl font-bold text-gray-800">TZS {{ number_format($paymentLink->amount) }}</span>
                </div>
                
                @if($paymentLink->customer_name)
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Customer:</span>
                    <span class="font-medium text-gray-800">{{ $paymentLink->customer_name }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="space-y-3">
            <button onclick="window.location.href='{{ url('/pay/' . ($paymentLink->short_code ?? '')) }}'" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                <i class="fas fa-redo mr-2"></i>
                Try Again
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
                    <p class="text-gray-600">No Charges Made</p>
                </div>
                <div class="text-center">
                    <i class="fas fa-clock text-blue-600 text-xl mb-2"></i>
                    <p class="text-gray-600">Available 24/7</p>
                </div>
            </div>
        </div>
    </div>
@endsection 