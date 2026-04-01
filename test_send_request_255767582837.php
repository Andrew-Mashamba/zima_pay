<?php

/**
 * Test script: send a single Push USSD / money collection request to Selcom (Tembo API)
 * with phone number 255767582837.
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Client;
use App\Models\ServiceMapping;
use App\Models\Transaction;
use App\Services\EsbService;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$phone = '0767582837';
// API expects 255XXXXXXXXX; normalize if local format (0XXXXXXXXX)
if (strpos($phone, '0') === 0) {
    $phone = '255' . substr($phone, 1);
}
$amount = 1000; // Tembo live requires amount >= 1000 TZS
$reference = 'TEST_' . time() . '_' . substr(md5(uniqid()), 0, 6);

echo "Send request test – phone: {$phone}, amount: {$amount} TZS, reference: {$reference}\n";
echo str_repeat('-', 60) . "\n";

try {
    // Use Sample Payment Gateway client and its Selcom Money Collection mapping
    $mapping = ServiceMapping::where('client_id', 3) // Sample Payment Gateway
        ->whereHas('service', fn($q) => $q->where('code', 'MONEY_COLLECTION'))
        ->with(['service', 'aggregator', 'client'])
        ->first();

    if (!$mapping) {
        throw new Exception('No MONEY_COLLECTION mapping found for client_id 3.');
    }

    echo "Client: {$mapping->client->name}\n";
    echo "Aggregator: {$mapping->aggregator->name} ({$mapping->aggregator->code})\n";
    echo "Service: {$mapping->service->name} – {$mapping->service->endpoint}\n";
    echo "URL: " . ($mapping->aggregator->api_endpoint . $mapping->service->endpoint) . "\n\n";

    $transaction = Transaction::create([
        'client_id' => $mapping->client_id,
        'aggregator_id' => $mapping->aggregator_id,
        'service_id' => $mapping->service_id,
        'service_mapping_id' => $mapping->id,
        'transaction_id' => 'TXN_' . time() . '_' . substr(uniqid(), -6),
        'client_reference' => $reference,
        'amount' => $amount,
        'currency' => 'TZS',
        'status' => 'pending',
        'request_data' => [],
    ]);

    // Same shape as UniversalPaymentLinkService
    $transactionData = [
        'customer_phone' => $phone,
        'mobile_network' => 'TZ-MPESA-C2B',
        'amount' => (float) $amount,
        'description' => 'Test request for 0767582837',
        'reference' => $reference,
        'date' => now()->format('Y-m-d H:i:s'),
        'webhook_url' => url('/api/selcom/c2b/notification'),
    ];

    echo "Sending request...\n\n";

    $esb = new EsbService();
    $result = $esb->processRequest($mapping, $transactionData, $transaction);

    if ($result['success']) {
        echo "SUCCESS\n";
        echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "FAILED\n";
        echo "Error: " . ($result['error'] ?? 'Unknown') . "\n";
        if (!empty($result['response'])) {
            echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
        }
    }
} catch (Throwable $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n";
    exit(1);
}
