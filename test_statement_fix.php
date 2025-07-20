<?php

require_once 'vendor/autoload.php';

use App\Models\Client;
use App\Models\ServiceMapping;
use App\Models\Aggregator;
use App\Models\Transaction;
use App\Services\EsbService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔧 TEST: Collection Statement Service Fix\n";
echo "========================================\n\n";

try {
    // Get the Sample Payment Gateway client
    $client = Client::where('name', 'Sample Payment Gateway')->first();
    $tembo = Aggregator::where('code', 'TEMBO')->first();
    $esbService = new EsbService();
    
    echo "✅ Found client: {$client->name}\n";
    echo "✅ Found aggregator: {$tembo->name}\n\n";
    
    // Get the collection statement mapping
    $statementMapping = ServiceMapping::where('client_id', $client->id)
        ->whereHas('service', function($q) {
            $q->where('code', 'COLLECTION_STATEMENT');
        })->first();
    
    echo "🔧 Collection Statement Mapping:\n";
    echo "   Request Mapping: " . json_encode($statementMapping->request_mapping) . "\n";
    echo "   Response Mapping: " . json_encode($statementMapping->response_mapping) . "\n\n";
    
    // Test the fix
    $statementData = [
        'startDate' => date('Y-m-d', strtotime('-7 days')),
        'endDate' => date('Y-m-d')
    ];
    
    $statementTransaction = Transaction::create([
        'client_id' => $client->id,
        'aggregator_id' => $tembo->id,
        'service_id' => $statementMapping->service->id,
        'service_mapping_id' => $statementMapping->id,
        'transaction_id' => 'TEST_FIX_' . time(),
        'client_reference' => 'TEST_FIX_' . time(),
        'amount' => 0,
        'currency' => 'TZS',
        'status' => 'pending',
        'request_data' => $statementData
    ]);
    
    echo "📤 Testing Collection Statement with fix...\n";
    echo "   Request data: " . json_encode($statementData, JSON_PRETTY_PRINT) . "\n";
    
    $statementResult = $esbService->processRequest($statementMapping, $statementData, $statementTransaction);
    
    if ($statementResult['success']) {
        echo "✅ Collection Statement FIXED!\n";
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
        echo "❌ Collection Statement still failing: " . ($statementResult['error'] ?? 'Unknown error') . "\n";
    }

    echo "\n🎉 Collection Statement fix test completed!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} 