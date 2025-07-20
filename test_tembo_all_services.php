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

echo "🚀 Testing All Tembo Services with Correct API Format\n";
echo "====================================================\n\n";

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

    // Test 1: Money Collection with Tembo format
    echo "💰 Test 1: Money Collection (Tembo Format)\n";
    echo "-----------------------------------------\n";
    
    $collectionMapping = ServiceMapping::where('client_id', $client->id)
        ->whereHas('service', function($q) {
            $q->where('code', 'MONEY_COLLECTION');
        })->first();
    
    if (!$collectionMapping) {
        throw new Exception("Money collection mapping not found!");
    }
    
    // Create transaction with Tembo format data
    $uniqueRef = 'TEMBO_COLLECT_' . time() . '_' . rand(1000, 9999);
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
            'msisdn' => '255712345678',
            'amount' => 1000,
            'narration' => 'Test collection via Tembo',
            'reference' => $uniqueRef
        ]
    ]);
    
    // Use Tembo's exact request format
    $temboCollectionData = [
        'msisdn' => '255712345678',
        'amount' => 1000,
        'narration' => 'Test collection via Tembo',
        'reference' => $uniqueRef
    ];

    echo "📤 Sending collection request with Tembo format...\n";
    echo "   Request data: " . json_encode($temboCollectionData, JSON_PRETTY_PRINT) . "\n";
    
    $collectionResult = $esbService->processRequest($collectionMapping, $temboCollectionData, $collectionTransaction);
    
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

    // Test 2: Collection Balance
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
        'transaction_id' => 'BALANCE_TEMBO_' . time(),
        'client_reference' => 'BALANCE_TEMBO_' . time(),
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

    // Test 3: Collection Statement with Tembo format
    echo "📊 Test 3: Collection Statement (Tembo Format)\n";
    echo "----------------------------------------------\n";
    
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
        'transaction_id' => 'STATEMENT_TEMBO_' . time(),
        'client_reference' => 'STATEMENT_TEMBO_' . time(),
        'amount' => 0,
        'currency' => 'TZS',
        'status' => 'pending',
        'request_data' => [
            'startDate' => date('Y-m-d', strtotime('-7 days')),
            'endDate' => date('Y-m-d')
        ]
    ]);
    
    // Use Tembo's statement format
    $temboStatementData = [
        'startDate' => date('Y-m-d', strtotime('-7 days')),
        'endDate' => date('Y-m-d')
    ];
    
    echo "📤 Fetching collection statement with Tembo format...\n";
    echo "   Request data: " . json_encode($temboStatementData, JSON_PRETTY_PRINT) . "\n";
    
    $statementResult = $esbService->processRequest($statementMapping, $temboStatementData, $statementTransaction);
    
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

    // Test 4: Payment Status with Tembo format
    if ($transactionId) {
        echo "🔍 Test 4: Payment Status (Tembo Format)\n";
        echo "----------------------------------------\n";
        
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
            'transaction_id' => 'STATUS_TEMBO_' . time(),
            'client_reference' => 'STATUS_TEMBO_' . time(),
            'amount' => 0,
            'currency' => 'TZS',
            'status' => 'pending',
            'request_data' => [
                'reference' => $transactionId
            ]
        ]);
        
        // Use Tembo's status format
        $temboStatusData = [
            'reference' => $transactionId
        ];
        
        echo "📤 Checking payment status with Tembo format...\n";
        echo "   Request data: " . json_encode($temboStatusData, JSON_PRETTY_PRINT) . "\n";
        
        $statusResult = $esbService->processRequest($statusMapping, $temboStatusData, $statusTransaction);
        
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

    // Test 5: Multiple Collections with Tembo format
    echo "🔄 Test 5: Multiple Collections (Tembo Format)\n";
    echo "---------------------------------------------\n";
    
    $successCount = 0;
    $totalCollections = 3;
    
    for ($i = 1; $i <= $totalCollections; $i++) {
        $uniqueRef = 'MULTI_TEMBO_' . time() . '_' . $i . '_' . rand(1000, 9999);
        
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
                'msisdn' => '25571234567' . $i,
                'amount' => 500 + ($i * 100),
                'narration' => "Multiple collection test {$i} via Tembo",
                'reference' => $uniqueRef
            ]
        ]);

        $temboMultiData = [
            'msisdn' => '25571234567' . $i,
            'amount' => 500 + ($i * 100),
            'narration' => "Multiple collection test {$i} via Tembo",
            'reference' => $uniqueRef
        ];

        echo "📤 Sending collection {$i}/{$totalCollections} with Tembo format...\n";
        $result = $esbService->processRequest($collectionMapping, $temboMultiData, $multiTransaction);
        
        if ($result['success']) {
            echo "✅ Collection {$i} successful!\n";
            $successCount++;
        } else {
            echo "❌ Collection {$i} failed: " . ($result['error'] ?? 'Unknown error') . "\n";
        }
    }
    
    echo "\n📈 Multiple Collections Summary: {$successCount}/{$totalCollections} successful\n\n";

    echo "🎉 All Tembo services testing completed!\n";
    echo "=======================================\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 