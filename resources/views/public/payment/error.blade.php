@extends('public.payment.layout')

@section('content')
    <!-- Error Card -->
    <div class="bg-white rounded-lg card-shadow p-8 text-center">
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-exclamation-triangle text-3xl text-red-600"></i>
        </div>
        
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Payment Link Error</h2>
        
        <p class="text-gray-600 mb-6">
            {{ $message ?? 'The payment link you are looking for could not be found or is no longer available.' }}
        </p>

        @if(isset($error_details))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-start space-x-3">
                <i class="fas fa-info-circle text-red-600 mt-1"></i>
                <div class="text-left">
                    <h4 class="font-medium text-red-800">Error Details</h4>
                    <p class="text-sm text-red-700 mt-1">{{ $error_details }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Common Solutions -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h4 class="font-medium text-blue-800 mb-3">Possible Solutions:</h4>
            <ul class="text-sm text-blue-700 space-y-2 text-left">
                <li class="flex items-start space-x-2">
                    <i class="fas fa-check-circle text-blue-600 mt-1 flex-shrink-0"></i>
                    <span>Check if the payment link URL is correct</span>
                </li>
                <li class="flex items-start space-x-2">
                    <i class="fas fa-check-circle text-blue-600 mt-1 flex-shrink-0"></i>
                    <span>Verify that the payment link hasn't expired</span>
                </li>
                <li class="flex items-start space-x-2">
                    <i class="fas fa-check-circle text-blue-600 mt-1 flex-shrink-0"></i>
                    <span>Contact the sender if you believe this is an error</span>
                </li>
                <li class="flex items-start space-x-2">
                    <i class="fas fa-check-circle text-blue-600 mt-1 flex-shrink-0"></i>
                    <span>Try refreshing the page</span>
                </li>
            </ul>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-3">
            <button onclick="window.location.reload()" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                <i class="fas fa-redo mr-2"></i>
                Try Again
            </button>
            
            <button onclick="window.history.back()" 
                    class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Go Back
            </button>
        </div>

        <!-- Contact Information -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-sm text-gray-500 mb-2">Need help? Contact support:</p>
            <div class="flex justify-center space-x-4 text-sm">
                <a href="mailto:support@zima-esb.com" class="text-blue-600 hover:text-blue-700">
                    <i class="fas fa-envelope mr-1"></i>
                    support@zima-esb.com
                </a>
                <span class="text-gray-400">|</span>
                <span class="text-gray-600">
                    <i class="fas fa-phone mr-1"></i>
                    +255 123 456 789
                </span>
            </div>
        </div>
    </div>
@endsection 