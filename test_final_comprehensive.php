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

echo "🚀 FINAL COMPREHENSIVE TEST - All Tembo Services\n";
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

    // Test 1: Money Collection
    echo "💰 Test 1: Money Collection\n";
    echo "---------------------------\n";
    
    $collectionMapping = ServiceMapping::where('client_id', $client->id)
        ->whereHas('service', function($q) {
            $q->where('code', 'MONEY_COLLECTION');
        })->first();
    
    $uniqueRef = 'COMPREHENSIVE_' . time() . '_' . rand(1000, 9999);
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
            'description' => 'Comprehensive test collection',
            'reference' => $uniqueRef,
            'date' => date('Y-m-d H:i:s'),
            'webhook_url' => 'https://webhook.site/comprehensive-test'
        ]
    ]);
    
    $collectionData = [
        'customer_phone' => '255712345678',
        'mobile_network' => 'TZ-AIRTEL-C2B',
        'amount' => 1000,
        'description' => 'Comprehensive test collection',
        'reference' => $uniqueRef,
        'date' => date('Y-m-d H:i:s'),
        'webhook_url' => 'https://webhook.site/comprehensive-test'
    ];

    echo "📤 Sending collection request...\n";
    $collectionResult = $esbService->processRequest($collectionMapping, $collectionData, $collectionTransaction);
    
    if ($collectionResult['success']) {
        echo "✅ Collection successful!\n";
        echo "   Transaction ID: " . ($collectionTransaction->transaction_id) . "\n";
        echo "   Status: " . ($collectionResult['response']['status'] ?? 'N/A') . "\n";
        $transactionId = $collectionTransaction->transaction_id;
        $temboTransactionId = $collectionResult['response']['transaction_id'] ?? null;
    } else {
        echo "❌ Collection failed: " . ($collectionResult['error'] ?? 'Unknown error') . "\n";
        $transactionId = null;
        $temboTransactionId = null;
    }
    echo "\n";

    // Test 2: Collection Balance
    echo "💳 Test 2: Collection Balance\n";
    echo "-----------------------------\n";
    
    $balanceMapping = ServiceMapping::where('client_id', $client->id)
        ->whereHas('service', function($q) {
            $q->where('code', 'COLLECTION_BALANCE');
        })->first();
    
    $balanceTransaction = Transaction::create([
        'client_id' => $client->id,
        'aggregator_id' => $tembo->id,
        'service_id' => $balanceMapping->service->id,
        'service_mapping_id' => $balanceMapping->id,
        'transaction_id' => 'BALANCE_COMP_' . time(),
        'client_reference' => 'BALANCE_COMP_' . time(),
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
        echo "   Status: " . ($balanceData['status'] ?? 'N/A') . "\n";
        echo "   Currency: " . ($balanceData['currency'] ?? 'N/A') . "\n";
    } else {
        echo "❌ Balance retrieval failed: " . ($balanceResult['error'] ?? 'Unknown error') . "\n";
    }
    echo "\n";

    // Test 3: Collection Statement
    echo "📊 Test 3: Collection Statement\n";
    echo "-------------------------------\n";
    
    $statementMapping = ServiceMapping::where('client_id', $client->id)
        ->whereHas('service', function($q) {
            $q->where('code', 'COLLECTION_STATEMENT');
        })->first();
    
    $statementTransaction = Transaction::create([
        'client_id' => $client->id,
        'aggregator_id' => $tembo->id,
        'service_id' => $statementMapping->service->id,
        'service_mapping_id' => $statementMapping->id,
        'transaction_id' => 'STATEMENT_COMP_' . time(),
        'client_reference' => 'STATEMENT_COMP_' . time(),
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
    } else {
        echo "❌ Statement retrieval failed: " . ($statementResult['error'] ?? 'Unknown error') . "\n";
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
        
        $statusTransaction = Transaction::create([
            'client_id' => $client->id,
            'aggregator_id' => $tembo->id,
            'service_id' => $statusMapping->service->id,
            'service_mapping_id' => $statusMapping->id,
            'transaction_id' => 'STATUS_COMP_' . time(),
            'client_reference' => 'STATUS_COMP_' . time(),
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
        } else {
            echo "❌ Payment status check failed: " . ($statusResult['error'] ?? 'Unknown error') . "\n";
        }
        echo "\n";
    } else {
        echo "⏭️  Test 4: Payment Status - Skipped (no transaction ID available)\n\n";
    }

    // Summary
    echo "📋 TEST SUMMARY\n";
    echo "===============\n";
    echo "✅ Money Collection: " . ($collectionResult['success'] ? 'WORKING' : 'FAILED') . "\n";
    echo "✅ Collection Balance: " . ($balanceResult['success'] ? 'WORKING' : 'FAILED') . "\n";
    echo "✅ Collection Statement: " . ($statementResult['success'] ? 'WORKING' : 'FAILED') . "\n";
    echo "✅ Payment Status: " . (isset($statusResult) && $statusResult['success'] ? 'WORKING' : 'FAILED') . "\n";
    
    $workingServices = 0;
    $totalServices = 4;
    if ($collectionResult['success']) $workingServices++;
    if ($balanceResult['success']) $workingServices++;
    if ($statementResult['success']) $workingServices++;
    if (isset($statusResult) && $statusResult['success']) $workingServices++;
    
    echo "\n🎯 Overall Result: {$workingServices}/{$totalServices} services working\n";
    
    if ($workingServices == $totalServices) {
        echo "🎉 ALL TEMBO SERVICES ARE WORKING PERFECTLY!\n";
    } elseif ($workingServices >= 2) {
        echo "✅ MOST TEMBO SERVICES ARE WORKING!\n";
    } else {
        echo "⚠️  SOME TEMBO SERVICES NEED ATTENTION\n";
    }

    echo "\n🎉 Comprehensive testing completed!\n";
    echo "==================================\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 