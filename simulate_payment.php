#!/usr/bin/env php
<?php
/**
 * Simulate payment for a payment link
 *
 * Usage: php simulate_payment.php <short_code> [base_url]
 * Example: php simulate_payment.php 4LSDIbKe http://127.0.0.1:8001
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$shortCode = $argv[1] ?? null;
$baseUrl = rtrim($argv[2] ?? 'http://127.0.0.1:8001', '/');

if (!$shortCode) {
    echo "Usage: php simulate_payment.php <short_code> [base_url]\n";
    echo "Example: php simulate_payment.php 4LSDIbKe http://127.0.0.1:8001\n";
    exit(1);
}

$service = app(\App\Services\UniversalPaymentLinkService::class);
$paymentLink = $service->getUniversalPaymentLink($shortCode);

if (!$paymentLink) {
    echo "❌ Payment link not found: {$shortCode}\n";
    exit(1);
}

echo "📋 Simulating payment for: {$baseUrl}/pay/{$shortCode}\n";
echo "   Link: {$paymentLink->description}\n";
echo "   Amount: {$paymentLink->amount} {$paymentLink->currency}\n";
echo "   Customer: {$paymentLink->customer_name} ({$paymentLink->customer_phone})\n\n";

$items = $paymentLink->items->map(fn($i) => [
    'item_code' => $i->item_code,
    'amount' => (float) $i->amount,
])->toArray();

$paymentData = [
    'customer_phone' => $paymentLink->customer_phone ?? '255742099713',
    'mobile_network' => $paymentLink->allowed_networks_array[0] ?? 'TZ-MPESA-C2B',
    'customer_name' => $paymentLink->customer_name ?? 'Simon Mpembee',
    'customer_email' => $paymentLink->customer_email ?? 'mpembeesimon@email.com',
    'amount' => (float) $paymentLink->amount,
    'items' => $items,
];

echo "📤 Initiating payment...\n";
$result = $service->processUniversalPayment($paymentLink, $paymentData);

$tx = \App\Models\Transaction::where('client_reference', 'like', $paymentLink->client_reference . '_%')
    ->latest()
    ->first();

if (!$tx) {
    echo "❌ No transaction created. Payment may have failed before transaction creation.\n";
    if (!$result['success']) {
        echo "   Error: " . ($result['error'] ?? 'Unknown') . "\n";
    }
    exit(1);
}

$ref = $tx->client_reference;
$txId = $tx->transaction_id;

if ($result['success']) {
    echo "✅ Payment initiated (Selcom Push USSD sent)\n";
} else {
    echo "⚠️  Selcom API may have failed, but transaction was created.\n";
    echo "   Error: " . ($result['error'] ?? 'Unknown') . "\n";
}
echo "   Transaction ID: {$txId}\n";
echo "   Reference (utilityref): {$ref}\n";
echo "\n📡 Simulating Selcom C2B notification...\n";

$c2bPayload = [
    'utilityref' => $ref,
    'transid' => $txId,
    'reference' => 'SIM-' . time(),
    'amount' => (int) $paymentLink->amount,
    'msisdn' => $paymentData['customer_phone'],
    'operator' => 'AIRTELMONEY',
    'resultcode' => '000',
];

$ch = curl_init($baseUrl . '/api/selcom/c2b/notification');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($c2bPayload),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
    CURLOPT_RETURNTRANSFER => true,
]);
$notif = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$notifData = json_decode($notif, true);
if ($code === 200 && ($notifData['resultcode'] ?? '') === '000') {
    echo "✅ C2B notification simulated – payment marked as success\n";
    echo "\n🎉 Payment simulation complete!\n";
    echo "   Status check: {$baseUrl}/api/callback/status/{$txId}\n";
} else {
    echo "⚠️  C2B notification: HTTP {$code}\n";
    echo "   " . ($notifData['message'] ?? $notif) . "\n";
    exit(1);
}
