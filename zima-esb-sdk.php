<?php

/**
 * ZIMA ESB PHP SDK
 * 
 * A simple and easy-to-use PHP SDK for integrating with the ZIMA Enterprise Service Bus API.
 * 
 * @package ZimaEsb
 * @version 1.0.0
 * @author ZIMA ESB Team
 */

class ZimaEsbSDK {
    private $apiKey;
    private $apiSecret;
    private $baseUrl;
    private $timeout;
    private $verifySSL;
    
    /**
     * Constructor
     * 
     * @param string $apiKey Your API key
     * @param string $apiSecret Your API secret
     * @param string $baseUrl Base URL for the API (default: production)
     * @param int $timeout Request timeout in seconds (default: 30)
     * @param bool $verifySSL Whether to verify SSL certificates (default: true)
     */
    public function __construct($apiKey, $apiSecret, $baseUrl = 'https://api.zimaesb.com/api', $timeout = 30, $verifySSL = true) {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
        $this->verifySSL = $verifySSL;
    }
    
    /**
     * Initiate a money collection transaction
     * 
     * @param array $data Transaction data
     * @return array Response data
     * @throws Exception
     */
    public function initiateMoneyCollection($data) {
        $requiredFields = ['customer_phone', 'mobile_network', 'amount', 'description', 'reference', 'date', 'webhook_url'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }
        
        return $this->makeRequest('POST', '/esb/MONEY_COLLECTION', $data);
    }
    
    /**
     * Check transaction status
     * 
     * @param string $transactionId Transaction ID
     * @return array Response data
     * @throws Exception
     */
    public function checkTransactionStatus($transactionId) {
        return $this->makeRequest('GET', '/callback/status/' . $transactionId);
    }
    
    /**
     * Get collection balance
     * 
     * @return array Response data
     * @throws Exception
     */
    public function getCollectionBalance() {
        return $this->makeRequest('POST', '/esb/COLLECTION_BALANCE');
    }
    
    /**
     * Get collection statement
     * 
     * @param string $startDate Start date in YYYY-MM-DD format
     * @param string $endDate End date in YYYY-MM-DD format
     * @return array Response data
     * @throws Exception
     */
    public function getCollectionStatement($startDate, $endDate) {
        $data = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
        
        return $this->makeRequest('POST', '/esb/COLLECTION_STATEMENT', $data);
    }
    
    /**
     * Check payment status
     * 
     * @param string $transactionId Transaction ID from aggregator
     * @param string $reference Transaction reference
     * @return array Response data
     * @throws Exception
     */
    public function checkPaymentStatus($transactionId, $reference) {
        $data = [
            'transaction_id' => $transactionId,
            'reference' => $reference
        ];
        
        return $this->makeRequest('POST', '/esb/PAYMENT_STATUS', $data);
    }
    
    /**
     * Check callback status
     * 
     * @param string $transactionId Transaction ID
     * @return array Response data
     * @throws Exception
     */
    public function checkCallbackStatus($transactionId) {
        if (empty($transactionId)) {
            throw new Exception("Transaction ID is required");
        }
        
        return $this->makeRequest('GET', "/callback/status/{$transactionId}");
    }
    
    /**
     * Test API credentials
     * 
     * @return array Response data
     * @throws Exception
     */
    public function testCredentials() {
        return $this->makeRequest('GET', '/esb/transaction/test');
    }
    
    /**
     * Make HTTP request to the API
     * 
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $data Request data (for POST requests)
     * @return array Response data
     * @throws Exception
     */
    private function makeRequest($method, $endpoint, $data = null) {
        $url = $this->baseUrl . $endpoint;
        
        $headers = [
            'X-API-Key: ' . $this->apiKey,
            'X-API-Secret: ' . $this->apiSecret,
            'Accept: application/json'
        ];
        
        if ($method === 'POST' && $data !== null) {
            $headers[] = 'Content-Type: application/json';
        }
        
        $ch = curl_init();
        
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => $this->verifySSL,
            CURLOPT_SSL_VERIFYHOST => $this->verifySSL ? 2 : 0,
            CURLOPT_USERAGENT => 'ZimaEsbSDK/1.0.0 PHP/' . PHP_VERSION
        ];
        
        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            if ($data !== null) {
                $options[CURLOPT_POSTFIELDS] = json_encode($data);
            }
        }
        
        curl_setopt_array($ch, $options);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL Error: {$error}");
        }
        
        $responseData = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response: " . json_last_error_msg());
        }
        
        return [
            'status_code' => $httpCode,
            'response' => $responseData,
            'raw_response' => $response
        ];
    }
    
    /**
     * Validate phone number format
     * 
     * @param string $phone Phone number
     * @return bool True if valid, false otherwise
     */
    public static function validatePhoneNumber($phone) {
        return preg_match('/^255[0-9]{9}$/', $phone) === 1;
    }
    
    /**
     * Validate mobile network code
     * 
     * @param string $network Network code
     * @return bool True if valid, false otherwise
     */
    public static function validateMobileNetwork($network) {
        $validNetworks = [
            'TZ-AIRTEL-C2B',
            'TZ-TIGO-C2B',
            'TZ-MPESA-C2B',
            'TZ-HALOPESA-C2B'
        ];
        
        return in_array($network, $validNetworks);
    }
    
    /**
     * Validate amount
     * 
     * @param int $amount Amount in TZS
     * @return bool True if valid, false otherwise
     */
    public static function validateAmount($amount) {
        return is_numeric($amount) && $amount >= 100 && $amount <= 1000000;
    }
    
    /**
     * Get supported mobile networks
     * 
     * @return array Array of supported networks
     */
    public static function getSupportedNetworks() {
        return [
            'TZ-AIRTEL-C2B' => 'Airtel Money',
            'TZ-TIGO-C2B' => 'Tigo Pesa',
            'TZ-MPESA-C2B' => 'M-Pesa',
            'TZ-HALOPESA-C2B' => 'HaloPesa'
        ];
    }
    
    /**
     * Format phone number to Tanzania international format
     * 
     * @param string $phone Phone number (local or international format)
     * @return string Formatted phone number
     */
    public static function formatPhoneNumber($phone) {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If it's already in international format, return as is
        if (strlen($phone) === 12 && substr($phone, 0, 3) === '255') {
            return $phone;
        }
        
        // If it's a local number (9 digits), add country code
        if (strlen($phone) === 9) {
            return '255' . $phone;
        }
        
        // If it's a local number with leading 0 (10 digits), remove 0 and add country code
        if (strlen($phone) === 10 && substr($phone, 0, 1) === '0') {
            return '255' . substr($phone, 1);
        }
        
        return $phone;
    }
    
    /**
     * Generate a unique transaction reference
     * 
     * @param string $prefix Reference prefix (default: 'TXN')
     * @return string Unique reference
     */
    public static function generateReference($prefix = 'TXN') {
        return $prefix . '_' . time() . '_' . substr(md5(uniqid()), 0, 8);
    }
    
    /**
     * Get current date in required format
     * 
     * @return string Formatted date
     */
    public static function getCurrentDate() {
        return date('Y-m-d H:i:s');
    }
}

/**
 * ZIMA ESB Exception Class
 */
class ZimaEsbException extends Exception {
    private $responseData;
    
    public function __construct($message, $code = 0, $responseData = null) {
        parent::__construct($message, $code);
        $this->responseData = $responseData;
    }
    
    public function getResponseData() {
        return $this->responseData;
    }
}

/**
 * Usage Examples
 */

/*
// Initialize the SDK
$sdk = new ZimaEsbSDK(
    'your_api_key_here',
    'your_api_secret_here',
    'https://api.zimaesb.com/api' // or 'http://127.0.0.1:8000/api' for local testing
);

// Test credentials
try {
    $result = $sdk->testCredentials();
    echo "Credentials are valid!\n";
} catch (Exception $e) {
    echo "Credential test failed: " . $e->getMessage() . "\n";
}

// Initiate a money collection transaction
try {
    $transactionData = [
        'customer_phone' => ZimaEsbSDK::formatPhoneNumber('0692410353'),
        'mobile_network' => 'TZ-AIRTEL-C2B',
        'amount' => 1000,
        'description' => 'Payment for services',
        'reference' => ZimaEsbSDK::generateReference(),
        'date' => ZimaEsbSDK::getCurrentDate(),
        'webhook_url' => 'https://your-webhook-url.com/callback'
    ];
    
    $result = $sdk->initiateMoneyCollection($transactionData);
    
    if ($result['status_code'] === 200) {
        echo "Transaction initiated successfully!\n";
        echo "Transaction ID: " . $result['response']['transaction_id'] . "\n";
        echo "Reference: " . $result['response']['reference'] . "\n";
        
        // Check status after a few seconds
        sleep(5);
        $statusResult = $sdk->checkTransactionStatus($result['response']['transaction_id']);
        echo "Transaction status: " . $statusResult['response']['transaction']['status'] . "\n";
    } else {
        echo "Transaction failed: " . $result['response']['message'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Validate data before sending
$phone = '0692410353';
if (!ZimaEsbSDK::validatePhoneNumber(ZimaEsbSDK::formatPhoneNumber($phone))) {
    echo "Invalid phone number format\n";
}

$amount = 1000;
if (!ZimaEsbSDK::validateAmount($amount)) {
    echo "Invalid amount\n";
}

$network = 'TZ-AIRTEL-C2B';
if (!ZimaEsbSDK::validateMobileNetwork($network)) {
    echo "Invalid mobile network\n";
}

// Get supported networks
$networks = ZimaEsbSDK::getSupportedNetworks();
foreach ($networks as $code => $name) {
    echo "{$code}: {$name}\n";
}
*/ 