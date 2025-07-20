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

echo "🚀 Testing Sample Payment Gateway Client\n";
echo "==========================================\n\n";

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
    echo "✅ Found aggregator: {$tembo->name}\n\n";

    // Get all services
    $services = Service::whereIn('code', [
        'MONEY_COLLECTION',
        'COLLECTION_BALANCE', 
        'COLLECTION_STATEMENT',
        'PAYMENT_STATUS'
    ])->get();

    echo "📋 Available Services:\n";
    foreach ($services as $service) {
        echo "  - {$service->name} ({$service->code})\n";
    }
    echo "\n";

    // Get service mappings for this client
    $mappings = ServiceMapping::where('client_id', $client->id)
        ->with(['service', 'aggregator'])
        ->get();

    echo "🔗 Service Mappings for {$client->name}:\n";
    foreach ($mappings as $mapping) {
        echo "  - {$mapping->service->name} via {$mapping->aggregator->name}\n";
    }
    echo "\n";

    // Initialize ESB Service
    $esbService = new EsbService();
    echo "🔧 ESB Service initialized\n\n";

    // Test 1: Money Collection
    echo "💰 Test 1: Money Collection\n";
    echo "---------------------------\n";
    
    // Get the money collection mapping
    $collectionMapping = ServiceMapping::where('client_id', $client->id)
        ->whereHas('service', function($q) {
            $q->where('code', 'MONEY_COLLECTION');
        })->first();
    
    if (!$collectionMapping) {
        throw new Exception("Money collection mapping not found for client!");
    }
    
    // Create a transaction record
    $transaction = Transaction::create([
        'client_id' => $client->id,
        'aggregator_id' => $tembo->id,
        'service_id' => $collectionMapping->service->id,
        'service_mapping_id' => $collectionMapping->id,
        'transaction_id' => 'TEST_' . time(),
        'client_reference' => 'TEST_' . time(),
        'amount' => 1000,
        'currency' => 'TZS',
        'status' => 'pending',
        'request_data' => [
            'amount' => 1000,
            'currency' => 'TZS',
            'phone_number' => '255712345678',
            'reference' => 'TEST_' . time(),
            'description' => 'Test collection for Sample Payment Gateway'
        ]
    ]);
    
    $collectionData = [
        'amount' => 1000,
        'currency' => 'TZS',
        'phone_number' => '255712345678',
        'reference' => 'TEST_' . time(),
        'description' => 'Test collection for Sample Payment Gateway'
    ];

    echo "📤 Sending collection request...\n";
    $collectionResult = $esbService->processRequest($collectionMapping, $collectionData, $transaction);
    
    if ($collectionResult['success']) {
        echo "✅ Collection successful!\n";
        echo "   Transaction ID: " . ($transaction->transaction_id) . "\n";
        echo "   Status: " . ($collectionResult['response']['status'] ?? 'N/A') . "\n";
        $transactionId = $transaction->transaction_id;
    } else {
        echo "❌ Collection failed: " . ($collectionResult['error'] ?? 'Unknown error') . "\n";
        $transactionId = null;
    }
    echo "\n";

    // Test 2: Collection Balance
    echo "💳 Test 2: Collection Balance\n";
    echo "-----------------------------\n";
    
    // Get the balance mapping
    $balanceMapping = ServiceMapping::where('client_id', $client->id)
        ->whereHas('service', function($q) {
            $q->where('code', 'COLLECTION_BALANCE');
        })->first();
    
    if (!$balanceMapping) {
        throw new Exception("Collection balance mapping not found for client!");
    }
    
    // Create a transaction record for balance check
    $balanceTransaction = Transaction::create([
        'client_id' => $client->id,
        'aggregator_id' => $tembo->id,
        'service_id' => $balanceMapping->service->id,
        'service_mapping_id' => $balanceMapping->id,
        'transaction_id' => 'BALANCE_' . time(),
        'client_reference' => 'BALANCE_' . time(),
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
    } else {
        echo "❌ Balance retrieval failed: " . ($balanceResult['error'] ?? 'Unknown error') . "\n";
    }
    echo "\n";

    // Test 3: Collection Statement
    echo "📊 Test 3: Collection Statement\n";
    echo "-------------------------------\n";
    
    // Get the statement mapping
    $statementMapping = ServiceMapping::where('client_id', $client->id)
        ->whereHas('service', function($q) {
            $q->where('code', 'COLLECTION_STATEMENT');
        })->first();
    
    if (!$statementMapping) {
        throw new Exception("Collection statement mapping not found for client!");
    }
    
    // Create a transaction record for statement
    $statementTransaction = Transaction::create([
        'client_id' => $client->id,
        'aggregator_id' => $tembo->id,
        'service_id' => $statementMapping->service->id,
        'service_mapping_id' => $statementMapping->id,
        'transaction_id' => 'STATEMENT_' . time(),
        'client_reference' => 'STATEMENT_' . time(),
        'amount' => 0,
        'currency' => 'TZS',
        'status' => 'pending',
        'request_data' => [
            'start_date' => date('Y-m-d', strtotime('-7 days')),
            'end_date' => date('Y-m-d')
        ]
    ]);
    
    echo "📤 Fetching collection statement...\n";
    $statementResult = $esbService->processRequest($statementMapping, [
        'start_date' => date('Y-m-d', strtotime('-7 days')),
        'end_date' => date('Y-m-d')
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
        
        // Get the payment status mapping
        $statusMapping = ServiceMapping::where('client_id', $client->id)
            ->whereHas('service', function($q) {
                $q->where('code', 'PAYMENT_STATUS');
            })->first();
        
        if (!$statusMapping) {
            throw new Exception("Payment status mapping not found for client!");
        }
        
        // Create a transaction record for status check
        $statusTransaction = Transaction::create([
            'client_id' => $client->id,
            'aggregator_id' => $tembo->id,
            'service_id' => $statusMapping->service->id,
            'service_mapping_id' => $statusMapping->id,
            'transaction_id' => 'STATUS_' . time(),
            'client_reference' => 'STATUS_' . time(),
            'amount' => 0,
            'currency' => 'TZS',
            'status' => 'pending',
            'request_data' => [
                'transaction_id' => $transactionId
            ]
        ]);
        
        echo "📤 Checking payment status for transaction: {$transactionId}\n";
        $statusResult = $esbService->processRequest($statusMapping, [
            'transaction_id' => $transactionId
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

    // Test 5: Multiple Collections
    echo "🔄 Test 5: Multiple Collections\n";
    echo "-------------------------------\n";
    
    $successCount = 0;
    $totalCollections = 3;
    
    for ($i = 1; $i <= $totalCollections; $i++) {
        // Create a transaction record for each collection
        $multiTransaction = Transaction::create([
            'client_id' => $client->id,
            'aggregator_id' => $tembo->id,
            'service_id' => $collectionMapping->service->id,
            'service_mapping_id' => $collectionMapping->id,
            'transaction_id' => 'MULTI_' . time() . '_' . $i,
            'client_reference' => 'MULTI_TEST_' . time() . '_' . $i,
            'amount' => 500 + ($i * 100),
            'currency' => 'TZS',
            'status' => 'pending',
            'request_data' => [
                'amount' => 500 + ($i * 100),
                'currency' => 'TZS',
                'phone_number' => '25571234567' . $i,
                'reference' => 'MULTI_TEST_' . time() . '_' . $i,
                'description' => "Multiple collection test {$i} for Sample Payment Gateway"
            ]
        ]);

        $collectionData = [
            'amount' => 500 + ($i * 100),
            'currency' => 'TZS',
            'phone_number' => '25571234567' . $i,
            'reference' => 'MULTI_TEST_' . time() . '_' . $i,
            'description' => "Multiple collection test {$i} for Sample Payment Gateway"
        ];

        echo "📤 Sending collection {$i}/{$totalCollections}...\n";
        $result = $esbService->processRequest($collectionMapping, $collectionData, $multiTransaction);
        
        if ($result['success']) {
            echo "✅ Collection {$i} successful!\n";
            $successCount++;
        } else {
            echo "❌ Collection {$i} failed: " . ($result['error'] ?? 'Unknown error') . "\n";
        }
    }
    
    echo "\n📈 Multiple Collections Summary: {$successCount}/{$totalCollections} successful\n\n";

    // Test 6: Final Statement Check
    echo "📊 Test 6: Final Statement Check\n";
    echo "--------------------------------\n";
    
    // Create a transaction record for final statement
    $finalStatementTransaction = Transaction::create([
        'client_id' => $client->id,
        'aggregator_id' => $tembo->id,
        'service_id' => $statementMapping->service->id,
        'service_mapping_id' => $statementMapping->id,
        'transaction_id' => 'FINAL_STATEMENT_' . time(),
        'client_reference' => 'FINAL_STATEMENT_' . time(),
        'amount' => 0,
        'currency' => 'TZS',
        'status' => 'pending',
        'request_data' => [
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d')
        ]
    ]);
    
    echo "📤 Fetching final statement to verify all transactions...\n";
    $finalStatementResult = $esbService->processRequest($statementMapping, [
        'start_date' => date('Y-m-d'),
        'end_date' => date('Y-m-d')
    ], $finalStatementTransaction);
    
    if ($finalStatementResult['success']) {
        echo "✅ Final statement retrieved successfully!\n";
        $finalStatementData = $finalStatementResult['response'];
        $transactions = $finalStatementData['transactions'] ?? [];
        echo "   Today's transactions: " . count($transactions) . "\n";
        
        if (!empty($transactions)) {
            echo "   Transaction summary:\n";
            $totalAmount = 0;
            foreach ($transactions as $transaction) {
                $amount = $transaction['amount'] ?? 0;
                $totalAmount += $amount;
                echo "     - " . ($transaction['reference'] ?? 'N/A') . 
                     ": " . number_format($amount) . " " . ($transaction['currency'] ?? 'TZS') . 
                     " (" . ($transaction['status'] ?? 'N/A') . ")\n";
            }
            echo "   Total amount today: " . number_format($totalAmount) . " TZS\n";
        }
    } else {
        echo "❌ Final statement retrieval failed: " . ($finalStatementResult['error'] ?? 'Unknown error') . "\n";
    }

    echo "\n🎉 Testing completed for Sample Payment Gateway!\n";
    echo "==========================================\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 