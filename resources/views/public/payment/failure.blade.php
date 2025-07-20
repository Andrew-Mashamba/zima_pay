@extends('public.payment.layout')

@section('content')
    <!-- Failure Card -->
    <div class="bg-white rounded-lg card-shadow p-8 text-center">
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-times-circle text-3xl text-red-600"></i>
        </div>
        
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Payment Failed</h2>
        
        <p class="text-gray-600 mb-6">
            We're sorry, but your payment could not be processed at this time.
        </p>

        <!-- Error Details -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
            <div class="flex items-start space-x-3">
                <i class="fas fa-exclamation-triangle text-red-600 mt-1"></i>
                <div class="text-left">
                    <h4 class="font-medium text-red-800">What happened?</h4>
                    <p class="text-sm text-red-700 mt-1">
                        Your payment was not completed. This could be due to insufficient funds, 
                        network issues, or other temporary problems.
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

        <!-- Help Information -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-info-circle text-blue-600 mt-1"></i>
                    <div class="text-left">
                        <h4 class="font-medium text-blue-800">Need Help?</h4>
                        <p class="text-sm text-blue-700 mt-1">
                            If you continue to experience issues, please contact our support team 
                            at <strong>support@zima-esb.com</strong> or call <strong>+255 123 456 789</strong>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 