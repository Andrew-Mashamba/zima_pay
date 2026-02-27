<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'MICROPAY ESB Payment' }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-shadow {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .network-card {
            transition: all 0.3s ease;
        }
        .network-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .network-card.selected {
            border-color: #667eea;
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="gradient-bg text-white py-4">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-credit-card text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">MICROPAY</h1>
                        <p class="text-sm opacity-90">Secure Payment Gateway</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm opacity-90">Powered by</div>
                    <div class="font-semibold">Tanzania Mobile Money</div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6 mt-12">
        <div class="container mx-auto px-4 text-center">
            <div class="flex justify-center space-x-6 mb-4">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-shield-alt text-green-400"></i>
                    <span class="text-sm">Secure</span>
                </div>
                <div class="flex items-center space-x-2">
                    <i class="fas fa-lock text-blue-400"></i>
                    <span class="text-sm">Encrypted</span>
                </div>
                <div class="flex items-center space-x-2">
                    <i class="fas fa-check-circle text-green-400"></i>
                    <span class="text-sm">Verified</span>
                </div>
            </div>
            <p class="text-sm opacity-75">
                © 2025 MICROPAY. All payments are processed securely through certified mobile money providers.
            </p>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Update network display
        function updateNetworkDisplay(networkInfo) {
            const networkDisplay = document.getElementById('network-display');
            const mobileNetworkInput = document.getElementById('mobile_network');
            
            if (networkInfo) {
                networkDisplay.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <div class="text-2xl">
                            <i class="${networkInfo.icon}" style="color: ${networkInfo.color}"></i>
                        </div>
                        <div>
                            <div class="font-medium text-gray-800">${networkInfo.name}</div>
                            <div class="text-sm text-gray-600">Automatically detected from your phone number</div>
                        </div>
                    </div>
                `;
                mobileNetworkInput.value = networkInfo.code;
            } else {
                networkDisplay.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <div class="text-2xl text-gray-400">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div>
                            <div class="font-medium text-gray-600">Network will be detected automatically</div>
                            <div class="text-sm text-gray-500">Enter your phone number above to detect your network</div>
                        </div>
                    </div>
                `;
                mobileNetworkInput.value = '';
            }
        }

        // Network detection from phone number
        function detectNetworkFromPhone(phoneNumber) {
            console.log('Detecting network for phone number:', phoneNumber);
            
            // Tanzanian network prefixes (2025)
            const networks = {
                'vodacom': { name: 'Vodacom M-Pesa', color: '#E60000', icon: 'fas fa-mobile-alt', code: 'TZ-MPESA-C2B' },
                'airtel': { name: 'Airtel Money', color: '#FF0000', icon: 'fas fa-mobile-alt', code: 'TZ-AIRTEL-C2B' },
                'tigo': { name: 'Tigo Pesa', color: '#FFD700', icon: 'fas fa-mobile-alt', code: 'TZ-TIGO-C2B' },
                'halotel': { name: 'HaloPesa', color: '#0066CC', icon: 'fas fa-mobile-alt', code: 'TZ-HALOPESA-C2B' },
                'ttcl': { name: 'TTCL', color: '#FF6600', icon: 'fas fa-mobile-alt', code: 'TZ-TTCL-C2B' },
                'zantel': { name: 'Zantel', color: '#00CC00', icon: 'fas fa-mobile-alt', code: 'TZ-ZANTEL-C2B' }
            };
            
            // Clean phone number and handle different formats
            let cleanNumber = phoneNumber.replace(/[^0-9]/g, '');
            console.log('Cleaned number:', cleanNumber);
            
            // Handle different phone number formats
            if (cleanNumber.startsWith('0') && cleanNumber.length === 10) {
                // Convert 0712345678 to 255712345678
                cleanNumber = '255' + cleanNumber.substring(1);
                console.log('Converted from 0 format:', cleanNumber);
            } else if (cleanNumber.startsWith('+255') && cleanNumber.length === 13) {
                // Convert +255712345678 to 255712345678
                cleanNumber = cleanNumber.substring(1);
                console.log('Converted from +255 format:', cleanNumber);
            } else if (cleanNumber.startsWith('255') && cleanNumber.length === 12) {
                // Already in correct format
                console.log('Already in correct format:', cleanNumber);
            }
            
            // Extract prefix (digits after 255)
            if (cleanNumber.match(/^255(\d{2})/)) {
                const prefix = cleanNumber.match(/^255(\d{2})/)[1];
                console.log('Extracted prefix:', prefix);
                
                // Detect network
                let detectedNetwork = null;
                if (['74', '75', '76'].includes(prefix)) {
                    detectedNetwork = networks.vodacom;
                    console.log('Detected Vodacom M-Pesa');
                } else if (['68', '69', '78', '79'].includes(prefix)) {
                    detectedNetwork = networks.airtel;
                    console.log('Detected Airtel Money');
                } else if (['71', '65', '67'].includes(prefix)) {
                    detectedNetwork = networks.tigo;
                    console.log('Detected Tigo Pesa');
                } else if (['62', '63'].includes(prefix)) {
                    detectedNetwork = networks.halotel;
                    console.log('Detected HaloPesa');
                } else if (['73'].includes(prefix)) {
                    detectedNetwork = networks.ttcl;
                    console.log('Detected TTCL');
                } else if (['77'].includes(prefix)) {
                    detectedNetwork = networks.zantel;
                    console.log('Detected Zantel');
                } else {
                    console.log('Unknown prefix:', prefix);
                }
                
                // Update network display
                updateNetworkDisplay(detectedNetwork);
                
                // Also update the phone input to ensure it's in the correct format
                const phoneInput = document.getElementById('customer_phone');
                if (phoneInput && phoneInput.value !== cleanNumber) {
                    phoneInput.value = cleanNumber;
                    console.log('Updated phone input to:', cleanNumber);
                }
            } else {
                console.log('Invalid phone number format');
                // Clear network display
                updateNetworkDisplay(null);
            }
        }

        // Amount validation
        function validateAmount(input) {
            const amount = parseFloat(input.value);
            const minAmount = parseFloat(input.dataset.minAmount || 0);
            const maxAmount = parseFloat(input.dataset.maxAmount || Infinity);
            
            if (amount < minAmount) {
                input.setCustomValidity(`Minimum amount is TZS ${minAmount.toLocaleString()}`);
            } else if (amount > maxAmount) {
                input.setCustomValidity(`Maximum amount is TZS ${maxAmount.toLocaleString()}`);
            } else {
                input.setCustomValidity('');
            }
        }

        // Initialize network detection on page load
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('customer_phone');
            if (phoneInput && phoneInput.value) {
                // Detect network from the pre-filled phone number immediately
                detectNetworkFromPhone(phoneInput.value);
            }
        });

        // Also run detection immediately if DOM is already loaded
        if (document.readyState === 'loading') {
            // DOM is still loading, wait for DOMContentLoaded
        } else {
            // DOM is already loaded, run detection immediately
            const phoneInput = document.getElementById('customer_phone');
            if (phoneInput && phoneInput.value) {
                detectNetworkFromPhone(phoneInput.value);
            }
        }

        // Form submission
        function submitPayment() {
            const form = document.getElementById('payment-form');
            if (form.checkValidity()) {
                // Show loading state
                const submitBtn = document.getElementById('submit-btn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
                
                // Get form data
                const formData = new FormData(form);
                
                // Submit form via AJAX
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showMessage('success', data.message || 'Payment initiated successfully!');
                        
                        // Redirect to success page after a delay
                        setTimeout(() => {
                            window.location.href = data.data?.redirect_url || window.location.href + '/success';
                        }, 2000);
                    } else {
                        // Show error message
                        showMessage('error', data.message || 'Payment processing failed. Please try again.');
                        
                        // Reset button
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-lock mr-2"></i>Pay TZS ' + getPaymentAmount();
                    }
                })
                .catch(error => {
                    console.error('Payment error:', error);
                    showMessage('error', 'An error occurred while processing your payment. Please try again.');
                    
                    // Reset button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-lock mr-2"></i>Pay TZS ' + getPaymentAmount();
                });
            } else {
                form.reportValidity();
            }
        }
        
        // Show user-friendly messages
        function showMessage(type, message) {
            // Remove existing messages
            const existingMessage = document.getElementById('payment-message');
            if (existingMessage) {
                existingMessage.remove();
            }
            
            // Create message element
            const messageDiv = document.createElement('div');
            messageDiv.id = 'payment-message';
            messageDiv.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 max-w-md ${
                type === 'success' 
                    ? 'bg-green-100 border border-green-400 text-green-700' 
                    : 'bg-red-100 border border-red-400 text-red-700'
            }`;
            
            messageDiv.innerHTML = `
                <div class="flex items-center space-x-2">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                    <span class="font-medium">${message}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            // Add to page
            document.body.appendChild(messageDiv);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (messageDiv.parentElement) {
                    messageDiv.remove();
                }
            }, 5000);
        }
        
        // Get payment amount from button text
        function getPaymentAmount() {
            const submitBtn = document.getElementById('submit-btn');
            const text = submitBtn.textContent || submitBtn.innerText;
            const match = text.match(/Pay TZS ([\d,]+)/);
            return match ? match[1] : '0';
        }
    </script>
</body>
</html> 