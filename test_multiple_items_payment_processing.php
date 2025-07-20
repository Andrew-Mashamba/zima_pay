<?php

require_once 'vendor/autoload.php';

use App\Models\PaymentLink;
use App\Services\UniversalPaymentLinkService;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Multiple Items Payment Processing Test ===\n\n";

try {
    // Get the payment link we just created
    $shortCode = 'iuPKIOxQ'; // From the previous script output
    $paymentLink = PaymentLink::where('short_code', $shortCode)->first();
    
    if (!$paymentLink) {
        throw new Exception("Payment link with short code '{$shortCode}' not found.");
    }

    echo "✅ Found Payment Link: {$paymentLink->link_id}\n";
    echo "   - Description: {$paymentLink->description}\n";
    echo "   - Total Amount: " . number_format($paymentLink->total_items_amount) . " TZS\n";
    echo "   - Items Count: {$paymentLink->items->count()}\n\n";

    // Show items
    echo "📋 Payment Items:\n";
    foreach ($paymentLink->items as $item) {
        $status = $item->is_required ? 'Required' : 'Optional';
        $partial = $item->allow_partial ? 'Partial Allowed' : 'Full Only';
        
        echo "   - {$item->item_name}\n";
        echo "     Amount: " . number_format($item->amount) . " TZS | Status: {$status} | {$partial}\n";
        echo "     Reference: {$item->metadata['product_service_reference']}\n\n";
    }

    // Test payment data
    $paymentData = [
        'customer_name' => 'Jane Smith',
        'customer_phone' => '255712345678',
        'customer_email' => 'jane.smith@example.com',
        'mobile_network' => 'TZ-TIGO-C2B',
        'items' => [
            // Required items (must be paid in full)
            'SCHOOL_FEES_2024' => 500000, // Tuition Fees - Required, Full Only
            'LAB_FEES' => 75000, // Laboratory Fees - Required, Full Only
            
            // Optional items (can be paid partially or skipped)
            'LIBRARY_FEES' => 15000, // Library Fees - Optional, Partial (paying 15k out of 25k)
            'SPORTS_FEES' => 10000, // Sports Fees - Optional, Partial (paying 10k out of 15k)
            
            // Skipping: UNIFORM_SET (optional, not paying)
        ]
    ];

    echo "💰 Payment Data:\n";
    echo "   - Customer: {$paymentData['customer_name']}\n";
    echo "   - Phone: {$paymentData['customer_phone']}\n";
    echo "   - Network: {$paymentData['mobile_network']}\n";
    echo "   - Total Payment: " . number_format(array_sum($paymentData['items'])) . " TZS\n\n";

    echo "📋 Item Payments:\n";
    foreach ($paymentData['items'] as $reference => $amount) {
        $item = $paymentLink->items->where('metadata.product_service_reference', $reference)->first();
        if ($item) {
            $percentage = ($amount / $item->amount) * 100;
            echo "   - {$item->item_name}: " . number_format($amount) . " TZS ({$percentage}%)\n";
        }
    }
    echo "\n";

    // Process the payment
    $service = new UniversalPaymentLinkService();
    $result = $service->processUniversalPayment($paymentLink, $paymentData);

    if ($result['success']) {
        echo "✅ Payment Processed Successfully!\n\n";
        echo "🔗 Transaction Details:\n";
        echo "   - Transaction ID: {$result['response']['transaction_id'] ?? 'N/A'}\n";
        echo "   - Status: {$result['response']['status'] ?? 'N/A'}\n";
        echo "   - Message: {$result['response']['message'] ?? 'N/A'}\n";
        echo "   - Response Time: " . number_format($result['response_time'], 3) . " seconds\n\n";

        // Show updated payment link stats
        echo "📊 Updated Payment Link Stats:\n";
        $stats = $service->getUniversalPaymentLinkStats($paymentLink);
        echo "   - Views: {$stats['views_count']}\n";
        echo "   - Uses: {$stats['current_uses']}/{$stats['max_uses']}\n";
        echo "   - Total Collected: " . number_format($stats['total_collected']) . " TZS\n";
        echo "   - Payment Progress: " . number_format($stats['payment_progress'], 2) . "%\n\n";

        echo "📋 Updated Items Status:\n";
        foreach ($stats['items'] as $item) {
            $status = ucfirst($item['status']);
            $paidAmount = number_format($item['paid_amount']);
            $remaining = number_format($item['remaining_amount']);
            $percentage = number_format($item['payment_percentage'], 1);
            
            echo "   - {$item['product_service_name']}\n";
            echo "     Status: {$status} | Paid: {$paidAmount} TZS | Remaining: {$remaining} TZS | Progress: {$percentage}%\n";
        }

    } else {
        echo "❌ Payment Processing Failed!\n";
        echo "   Error: {$result['error']}\n";
        
        if (isset($result['aggregator_response'])) {
            echo "   Aggregator Status: {$result['aggregator_response']['status_code'] ?? 'N/A'}\n";
            echo "   Aggregator Message: {$result['aggregator_response']['data']['message'] ?? 'N/A'}\n";
        }
    }

} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}\n";
    echo "Line: {$e->getLine()}\n";
}

echo "\n=== End ===\n"; 