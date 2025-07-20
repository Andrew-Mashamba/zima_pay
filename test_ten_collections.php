<?php

/**
 * Test Script: 10 Money Collections + Statement Check
 * 
 * This script performs 10 money collection transactions and then
 * checks the collection statement to verify the records appear.
 */

// Test configuration
$apiKey = 'test_bank_key_8CGbIjJCMRWLjFll';
$apiSecret = 'test_bank_secret_GtlfgLnQYUpcTwOry9LV1xvRcCVFO1Ci';
$baseUrl = 'http://127.0.0.1:8000/api/esb';

echo "🚀 Testing 10 Money Collections + Statement Verification\n";
echo "=======================================================\n\n";

// Store transaction details for verification
$transactions = [];

// Perform 10 money collection transactions
echo "📱 Performing 10 Money Collection Transactions...\n";
echo "------------------------------------------------\n";

for ($i = 1; $i <= 10; $i++) {
    echo "Transaction {$i}/10: ";
    
    $collectionData = [
        'customer_phone' => '255778342299',
        'mobile_network' => 'TZ-AIRTEL-C2B',
        'amount' => 1000 * $i, // Different amounts: 1000, 2000, 3000, etc.
        'description' => "Test payment {$i} from ESB - Batch Test",
        'reference' => 'BATCH_TEST_' . $i . '_' . time(),
        'date' => date('Y-m-d H:i:s'),
        'webhook_url' => 'https://webhook.site/batch-test-' . $i
    ];
    
    $response = makeRequest('POST', $baseUrl . '/MONEY_COLLECTION', $collectionData, [
        'X-API-Key: ' . $apiKey,
        'X-API-Secret: ' . $apiSecret,
        'Content-Type: application/json'
    ]);
    
    if ($response['status_code'] === 200) {
        $transactionId = $response['data']['transaction_id'] ?? 'N/A';
        $status = $response['data']['status'] ?? 'Unknown';
        echo "✅ SUCCESS - ID: {$transactionId}, Status: {$status}\n";
        
        $transactions[] = [
            'number' => $i,
            'reference' => $collectionData['reference'],
            'amount' => $collectionData['amount'],
            'transaction_id' => $transactionId,
            'status' => $status,
            'description' => $collectionData['description']
        ];
    } else {
        echo "❌ FAILED - Status: {$response['status_code']}\n";
        echo "   Error: " . json_encode($response['data']) . "\n";
    }
    
    // Small delay between requests
    usleep(500000); // 0.5 seconds
}

echo "\n📊 Transaction Summary:\n";
echo "----------------------\n";
echo "Total Transactions: " . count($transactions) . "/10\n";
echo "Successful: " . count(array_filter($transactions, fn($t) => $t['status'] === 'PENDING_ACK')) . "\n";
echo "Failed: " . count(array_filter($transactions, fn($t) => $t['status'] !== 'PENDING_ACK')) . "\n\n";

// Wait a moment for transactions to be processed
echo "⏳ Waiting 5 seconds for transactions to be processed...\n";
sleep(5);

// Check collection statement
echo "\n📋 Checking Collection Statement...\n";
echo "----------------------------------\n";

$statementData = [
    'start_date' => date('Y-m-d', strtotime('-1 day')), // Last 24 hours
    'end_date' => date('Y-m-d', strtotime('+1 day'))    // Include today
];

$statementResponse = makeRequest('POST', $baseUrl . '/COLLECTION_STATEMENT', $statementData, [
    'X-API-Key: ' . $apiKey,
    'X-API-Secret: ' . $apiSecret,
    'Content-Type: application/json'
]);

if ($statementResponse['status_code'] === 200) {
    $statement = $statementResponse['data'];
    
    if (is_array($statement) && !empty($statement)) {
        echo "✅ Statement Retrieved Successfully!\n";
        echo "Total Records: " . count($statement) . "\n\n";
        
        echo "📝 Statement Details:\n";
        echo "--------------------\n";
        
        foreach ($statement as $index => $record) {
            echo "Record " . (intval($index) + 1) . ":\n";
            echo "  Account: " . ($record['account_number'] ?? 'N/A') . "\n";
            echo "  Type: " . ($record['transaction_type'] ?? 'N/A') . "\n";
            echo "  Reference: " . ($record['transaction_reference'] ?? 'N/A') . "\n";
            echo "  Description: " . ($record['description'] ?? 'N/A') . "\n";
            echo "  Date: " . ($record['transaction_date'] ?? 'N/A') . "\n";
            echo "  Amount Credited: " . ($record['amount_credited'] ?? '0') . "\n";
            echo "  Amount Debited: " . ($record['amount_debited'] ?? '0') . "\n";
            echo "  Balance: " . ($record['balance'] ?? 'N/A') . "\n";
            echo "\n";
        }
        
        // Try to match our transactions with statement records
        echo "🔍 Transaction Matching Analysis:\n";
        echo "--------------------------------\n";
        
        $matchedTransactions = 0;
        foreach ($transactions as $transaction) {
            $found = false;
            foreach ($statement as $record) {
                if (strpos($record['description'] ?? '', "Test payment {$transaction['number']}") !== false ||
                    strpos($record['transaction_reference'] ?? '', $transaction['reference']) !== false) {
                    echo "✅ MATCHED: Transaction {$transaction['number']} - " . 
                         "Amount: {$transaction['amount']}, Reference: {$transaction['reference']}\n";
                    $found = true;
                    $matchedTransactions++;
                    break;
                }
            }
            if (!$found) {
                echo "❌ NOT FOUND: Transaction {$transaction['number']} - " . 
                     "Amount: {$transaction['amount']}, Reference: {$transaction['reference']}\n";
            }
        }
        
        echo "\n📈 Matching Summary:\n";
        echo "Matched Transactions: {$matchedTransactions}/" . count($transactions) . "\n";
        echo "Statement Records: " . count($statement) . "\n";
        
    } else {
        echo "⚠️ Statement is empty - no records found for the date range\n";
        echo "This might be normal if transactions are still being processed\n";
    }
    
} else {
    echo "❌ Failed to retrieve statement\n";
    echo "Status Code: " . $statementResponse['status_code'] . "\n";
    echo "Error: " . json_encode($statementResponse['data']) . "\n";
}

// Also check current balance
echo "\n💰 Checking Current Balance...\n";
echo "------------------------------\n";

$balanceResponse = makeRequest('POST', $baseUrl . '/COLLECTION_BALANCE', [], [
    'X-API-Key: ' . $apiKey,
    'X-API-Secret: ' . $apiSecret,
    'Content-Type: application/json'
]);

if ($balanceResponse['status_code'] === 200) {
    $balance = $balanceResponse['data'];
    echo "✅ Balance Retrieved Successfully!\n";
    echo "Account: " . ($balance['account_name'] ?? 'N/A') . "\n";
    echo "Account Number: " . ($balance['account_number'] ?? 'N/A') . "\n";
    echo "Current Balance: " . ($balance['current_balance'] ?? 'N/A') . " TZS\n";
    echo "Available Balance: " . ($balance['available_balance'] ?? 'N/A') . " TZS\n";
    echo "Status: " . ($balance['account_status'] ?? 'N/A') . "\n";
} else {
    echo "❌ Failed to retrieve balance\n";
    echo "Status Code: " . $balanceResponse['status_code'] . "\n";
}

echo "\n✅ Test completed!\n";

/**
 * Make HTTP request
 */
function makeRequest($method, $url, $data = [], $headers = [])
{
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status_code' => $httpCode,
        'data' => json_decode($response, true) ?: $response
    ];
} 