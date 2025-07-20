<?php

require_once 'vendor/autoload.php';

use App\Models\Client;
use App\Models\Service;
use App\Models\ServiceMapping;
use App\Models\Aggregator;
use App\Models\Transaction;
use App\Services\EsbService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🚀 Final Tembo Services Test with Correct Format\n";
echo "===============================================\n\n";

try {
    // Get the Sample Payment Gateway client
    $client = Client::where('name', 'Sample Payment Gateway')->first();
    if (!$client) {
        throw new Exception("Sample Payment Gateway client not found!");
    }
    echo "✅ Found client: {$client->name}\n";

    // Get Tembo aggregator
    $tembo = Aggregator::where('code', 'TEMBO')->first();
    if (!$tembo) {
        throw new Exception("Tembo aggregator not found!");
    }
    echo "✅ Found aggregator: {$tembo->name}\n";
    echo "   API Endpoint: {$tembo->api_endpoint}\n";
    echo "   API Key: " . substr($tembo->api_key, 0, 10) . "...\n";
    echo "   API Secret: " . substr($tembo->api_secret, 0, 10) . "...\n\n";

    // Initialize ESB Service
    $esbService = new EsbService();
    echo "🔧 ESB Service initialized\n\n";

    // Test 1: Money Collection with CORRECT Tembo format
    echo "💰 Test 1: Money Collection (Correct Tembo Format)\n";
    echo "------------------------------------------------\n";
    
    $collectionMapping = ServiceMapping::where('client_id', $client->id)
        ->whereHas('service', function($q) {
            $q->where('code', 'MONEY_COLLECTION');
        })->first();
    
    if (!$collectionMapping) {
        throw new Exception("Money collection mapping not found!");
    }
    
    // Create transaction with CORRECT Tembo format data
    $uniqueRef = 'FINAL_COLLECT_' . time() . '_' . rand(1000, 9999);
    $collectionTransaction = Transaction::create([
        'client_id' => $client->id,
        'aggregator_id' => $tembo->id,
        'service_id' => $collectionMapping->service->id,
        'service_mapping_id' => $collectionMapping->id,
        'transaction_id' => $uniqueRef,
        'client_reference' => $uniqueRef,
        'amount' => 1000,
        'currency' => 'TZS',
        'status' => 'pending',
        'request_data' => [
            'customer_phone' => '255712345678',
            'mobile_network' => 'TZ-AIRTEL-C2B',
            'amount' => 1000,
            'description' => 'Final test collection via Tembo',
            'reference' => $uniqueRef,
            'date' => date('Y-m-d H:i:s'),
            'webhook_url' => 'https://webhook.site/final-test'
        ]
    ]);
    
    // Use CORRECT Tembo request format
    $correctCollectionData = [
        'customer_phone' => '255712345678',
        'mobile_network' => 'TZ-AIRTEL-C2B',
        'amount' => 1000,
        'description' => 'Final test collection via Tembo',
        'reference' => $uniqueRef,
        'date' => date('Y-m-d H:i:s'),
        'webhook_url' => 'https://webhook.site/final-test'
    ];

    echo "📤 Sending collection request with CORRECT Tembo format...\n";
    echo "   Request data: " . json_encode($correctCollectionData, JSON_PRETTY_PRINT) . "\n";
    
    $collectionResult = $esbService->processRequest($collectionMapping, $correctCollectionData, $collectionTransaction);
    
    if ($collectionResult['success']) {
        echo "✅ Collection successful!\n";
        echo "   Transaction ID: " . ($collectionTransaction->transaction_id) . "\n";
        echo "   Status: " . ($collectionResult['response']['status'] ?? 'N/A') . "\n";
        echo "   Full Response: " . json_encode($collectionResult['response'], JSON_PRETTY_PRINT) . "\n";
        $transactionId = $collectionTransaction->transaction_id;
    } else {
        echo "❌ Collection failed: " . ($collectionResult['error'] ?? 'Unknown error') . "\n";
        echo "   Error Details: " . json_encode($collectionResult, JSON_PRETTY_PRINT) . "\n";
        $transactionId = null;
    }
    echo "\n";

    // Test 2: Collection Balance (This one works)
    echo "💳 Test 2: Collection Balance\n";
    echo "-----------------------------\n";
    
    $balanceMapping = ServiceMapping::where('client_id', $client->id)
        ->whereHas('service', function($q) {
            $q->where('code', 'COLLECTION_BALANCE');
        })->first();
    
    if (!$balanceMapping) {
        throw new Exception("Collection balance mapping not found!");
    }
    
    $balanceTransaction = Transaction::create([
        'client_id' => $client->id,
        'aggregator_id' => $tembo->id,
        'service_id' => $balanceMapping->service->id,
        'service_mapping_id' => $balanceMapping->id,
        'transaction_id' => 'BALANCE_FINAL_' . time(),
        'client_reference' => 'BALANCE_FINAL_' . time(),
        'amount' => 0,
        'currency' => 'TZS',
        'status' => 'pending',
        'request_data' => []
    ]);
    
    echo "📤 Fetching collection balance...\n";
    $balanceResult = $esbService->processRequest($balanceMapping, [], $balanceTransaction);
    
    if ($balanceResult['success']) {
        echo "✅ Balance retrieved successfully!\n";
        $balanceData = $balanceResult['response'];
        echo "   Balance: " . ($balanceData['balance'] ?? 'N/A') . "\n";
        echo "   Currency: " . ($balanceData['currency'] ?? 'N/A') . "\n";
        echo "   Full Response: " . json_encode($balanceData, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "❌ Balance retrieval failed: " . ($balanceResult['error'] ?? 'Unknown error') . "\n";
        echo "   Error Details: " . json_encode($balanceResult, JSON_PRETTY_PRINT) . "\n";
    }
    echo "\n";

    // Test 3: Collection Statement (This one works)
    echo "📊 Test 3: Collection Statement\n";
    echo "-------------------------------\n";
    
    $statementMapping = ServiceMapping::where('client_id', $client->id)
        ->whereHas('service', function($q) {
            $q->where('code', 'COLLECTION_STATEMENT');
        })->first();
    
    if (!$statementMapping) {
        throw new Exception("Collection statement mapping not found!");
    }
    
    $statementTransaction = Transaction::create([
        'client_id' => $client->id,
        'aggregator_id' => $tembo->id,
        'service_id' => $statementMapping->service->id,
        'service_mapping_id' => $statementMapping->id,
        'transaction_id' => 'STATEMENT_FINAL_' . time(),
        'client_reference' => 'STATEMENT_FINAL_' . time(),
        'amount' => 0,
        'currency' => 'TZS',
        'status' => 'pending',
        'request_data' => [
            'startDate' => date('Y-m-d', strtotime('-7 days')),
            'endDate' => date('Y-m-d')
        ]
    ]);
    
    echo "📤 Fetching collection statement...\n";
    $statementResult = $esbService->processRequest($statementMapping, [
        'startDate' => date('Y-m-d', strtotime('-7 days')),
        'endDate' => date('Y-m-d')
    ], $statementTransaction);
    
    if ($statementResult['success']) {
        echo "✅ Statement retrieved successfully!\n";
        $statementData = $statementResult['response'];
        $transactions = $statementData['transactions'] ?? [];
        echo "   Total transactions: " . count($transactions) . "\n";
        
        if (!empty($transactions)) {
            echo "   Recent transactions:\n";
            $recentTransactions = array_slice($transactions, 0, 3);
            foreach ($recentTransactions as $index => $transaction) {
                echo "     " . ($index + 1) . ". " . ($transaction['reference'] ?? 'N/A') . 
                     " - " . ($transaction['amount'] ?? 'N/A') . " " . ($transaction['currency'] ?? 'TZS') . 
                     " (" . ($transaction['status'] ?? 'N/A') . ")\n";
            }
        }
        echo "   Full Response: " . json_encode($statementData, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "❌ Statement retrieval failed: " . ($statementResult['error'] ?? 'Unknown error') . "\n";
        echo "   Error Details: " . json_encode($statementResult, JSON_PRETTY_PRINT) . "\n";
    }
    echo "\n";

    // Test 4: Payment Status (if we have a transaction ID)
    if ($transactionId) {
        echo "🔍 Test 4: Payment Status\n";
        echo "-------------------------\n";
        
        $statusMapping = ServiceMapping::where('client_id', $client->id)
            ->whereHas('service', function($q) {
                $q->where('code', 'PAYMENT_STATUS');
            })->first();
        
        if (!$statusMapping) {
            throw new Exception("Payment status mapping not found!");
        }
        
        $statusTransaction = Transaction::create([
            'client_id' => $client->id,
            'aggregator_id' => $tembo->id,
            'service_id' => $statusMapping->service->id,
            'service_mapping_id' => $statusMapping->id,
            'transaction_id' => 'STATUS_FINAL_' . time(),
            'client_reference' => 'STATUS_FINAL_' . time(),
            'amount' => 0,
            'currency' => 'TZS',
            'status' => 'pending',
            'request_data' => [
                'reference' => $transactionId
            ]
        ]);
        
        echo "📤 Checking payment status for transaction: {$transactionId}\n";
        $statusResult = $esbService->processRequest($statusMapping, [
            'reference' => $transactionId
        ], $statusTransaction);
        
        if ($statusResult['success']) {
            echo "✅ Payment status retrieved successfully!\n";
            $statusData = $statusResult['response'];
            echo "   Transaction ID: " . ($statusData['transaction_id'] ?? 'N/A') . "\n";
            echo "   Status: " . ($statusData['status'] ?? 'N/A') . "\n";
            echo "   Amount: " . ($statusData['amount'] ?? 'N/A') . "\n";
            echo "   Full Response: " . json_encode($statusData, JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "❌ Payment status check failed: " . ($statusResult['error'] ?? 'Unknown error') . "\n";
            echo "   Error Details: " . json_encode($statusResult, JSON_PRETTY_PRINT) . "\n";
        }
        echo "\n";
    } else {
        echo "⏭️  Test 4: Payment Status - Skipped (no transaction ID available)\n\n";
    }

    // Test 5: Multiple Collections with correct format
    echo "🔄 Test 5: Multiple Collections (Correct Format)\n";
    echo "-----------------------------------------------\n";
    
    $successCount = 0;
    $totalCollections = 3;
    
    for ($i = 1; $i <= $totalCollections; $i++) {
        $uniqueRef = 'MULTI_FINAL_' . time() . '_' . $i . '_' . rand(1000, 9999);
        
        $multiTransaction = Transaction::create([
            'client_id' => $client->id,
            'aggregator_id' => $tembo->id,
            'service_id' => $collectionMapping->service->id,
            'service_mapping_id' => $collectionMapping->id,
            'transaction_id' => $uniqueRef,
            'client_reference' => $uniqueRef,
            'amount' => 500 + ($i * 100),
            'currency' => 'TZS',
            'status' => 'pending',
            'request_data' => [
                'customer_phone' => '25571234567' . $i,
                'mobile_network' => 'TZ-AIRTEL-C2B',
                'amount' => 500 + ($i * 100),
                'description' => "Multiple collection test {$i} via Tembo",
                'reference' => $uniqueRef,
                'date' => date('Y-m-d H:i:s'),
                'webhook_url' => 'https://webhook.site/multi-test-' . $i
            ]
        ]);

        $correctMultiData = [
            'customer_phone' => '25571234567' . $i,
            'mobile_network' => 'TZ-AIRTEL-C2B',
            'amount' => 500 + ($i * 100),
            'description' => "Multiple collection test {$i} via Tembo",
            'reference' => $uniqueRef,
            'date' => date('Y-m-d H:i:s'),
            'webhook_url' => 'https://webhook.site/multi-test-' . $i
        ];

        echo "📤 Sending collection {$i}/{$totalCollections} with correct format...\n";
        $result = $esbService->processRequest($collectionMapping, $correctMultiData, $multiTransaction);
        
        if ($result['success']) {
            echo "✅ Collection {$i} successful!\n";
            $successCount++;
        } else {
            echo "❌ Collection {$i} failed: " . ($result['error'] ?? 'Unknown error') . "\n";
        }
    }
    
    echo "\n📈 Multiple Collections Summary: {$successCount}/{$totalCollections} successful\n\n";

    echo "🎉 Final Tembo services testing completed!\n";
    echo "=========================================\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 