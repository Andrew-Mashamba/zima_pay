<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\Aggregator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SimulateCallback extends Command
{
    protected $signature = 'callback:simulate
                            {transaction_id : The transaction ID to simulate callback for}
                            {status=success : The status to simulate (success, failed, timeout, cancelled)}
                            {--local : Send to local instead of production URL}';

    protected $description = 'Simulate a payment callback for testing purposes';

    public function handle()
    {
        $transactionId = $this->argument('transaction_id');
        $status = $this->argument('status');
        $useLocal = $this->option('local');

        // Find the transaction
        $transaction = Transaction::where('transaction_id', $transactionId)->first();

        if (!$transaction) {
            $this->error("Transaction not found: {$transactionId}");
            return 1;
        }

        $this->info("Found transaction:");
        $this->table(
            ['Field', 'Value'],
            [
                ['Transaction ID', $transaction->transaction_id],
                ['Amount', number_format($transaction->amount, 2)],
                ['Current Status', $transaction->status],
                ['Customer Phone', $transaction->customer_phone ?? 'N/A'],
            ]
        );

        // Get aggregator
        $aggregator = Aggregator::first();
        if (!$aggregator) {
            $this->error("No aggregator found in database");
            return 1;
        }

        // Build callback payload
        $payload = [
            'transaction_id' => $transaction->transaction_id,
            'transactionRef' => $transaction->aggregator_reference ?: $transaction->transaction_id,
            'status' => $status,
            'amount' => $transaction->amount,
            'msisdn' => $transaction->customer_phone ?? '255712345678',
            'transactionId' => 'SIMULATED_' . strtoupper(uniqid()),
            'timestamp' => now()->toIso8601String(),
            'message' => $this->getStatusMessage($status),
        ];

        $this->info("\nCallback payload:");
        $this->line(json_encode($payload, JSON_PRETTY_PRINT));

        // Determine URL
        $baseUrl = $useLocal ? 'http://127.0.0.1:8000' : config('app.url', 'https://zimapay.co.tz');
        $callbackUrl = "{$baseUrl}/api/callback/{$aggregator->code}";

        $this->info("\nSending callback to: {$callbackUrl}");

        if (!$this->confirm('Do you want to proceed?', true)) {
            $this->info('Cancelled.');
            return 0;
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Simulated' => 'true',
                ])
                ->post($callbackUrl, $payload);

            $this->newLine();

            if ($response->successful()) {
                $this->info("✓ Callback sent successfully!");
                $this->info("Response status: {$response->status()}");
                $this->line("Response body: " . $response->body());

                // Refresh transaction and show updated status
                $transaction->refresh();
                $this->newLine();
                $this->info("Updated transaction status:");
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['Transaction ID', $transaction->transaction_id],
                        ['Status', $transaction->status],
                        ['Aggregator Status', $transaction->aggregator_status ?? 'N/A'],
                        ['Updated At', $transaction->updated_at],
                    ]
                );
            } else {
                $this->error("✗ Callback failed!");
                $this->error("Response status: {$response->status()}");
                $this->line("Response body: " . $response->body());
            }

        } catch (\Exception $e) {
            $this->error("Error sending callback: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    protected function getStatusMessage($status): string
    {
        return match($status) {
            'success' => 'Payment completed successfully',
            'failed' => 'Payment failed - insufficient funds',
            'timeout' => 'Payment request timed out',
            'cancelled' => 'Payment cancelled by user',
            default => 'Unknown status',
        };
    }
}
