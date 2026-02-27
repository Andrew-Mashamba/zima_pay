<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaymentLink;
use Carbon\Carbon;

class ViewPaymentLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:links 
                            {--status= : Filter by status (active|expired|completed|cancelled)}
                            {--client= : Filter by client ID}
                            {--limit=20 : Number of links to display}
                            {--recent : Show most recent links first}
                            {--details= : Show detailed info for specific link ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View payment links from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // If requesting details for a specific link
        if ($linkId = $this->option('details')) {
            $this->showLinkDetails($linkId);
            return 0;
        }

        // Build query
        $query = PaymentLink::query();

        // Apply filters
        if ($status = $this->option('status')) {
            $query->where('status', $status);
        }

        if ($clientId = $this->option('client')) {
            $query->where('client_id', $clientId);
        }

        // Order by
        if ($this->option('recent')) {
            $query->orderBy('created_at', 'desc');
        } else {
            $query->orderBy('id', 'desc');
        }

        // Limit results
        $limit = (int) $this->option('limit');
        $links = $query->with(['client', 'serviceMapping.service'])->limit($limit)->get();

        if ($links->isEmpty()) {
            $this->warn('No payment links found with the specified criteria.');
            return 0;
        }

        $this->info("Found {$links->count()} payment link(s):");
        $this->newLine();

        // Display links in a table
        $this->displayLinksTable($links);

        // Show summary statistics
        $this->showStatistics();

        return 0;
    }

    /**
     * Display payment links in a table format
     */
    protected function displayLinksTable($links)
    {
        $tableData = [];

        foreach ($links as $link) {
            $tableData[] = [
                'ID' => $link->id,
                'Link ID' => substr($link->link_id ?? 'N/A', 0, 20) . '...',
                'Short Code' => $link->short_code,
                'Amount' => 'TZS ' . number_format($link->amount, 2),
                'Status' => $this->getStatusBadge($link->status),
                'Client' => $link->client->name ?? 'N/A',
                'Customer' => $link->customer_name ?? 'N/A',
                'Phone' => $link->customer_phone ?? 'N/A',
                'Uses' => $link->current_uses . '/' . ($link->max_uses ?? '∞'),
                'Created' => Carbon::parse($link->created_at)->format('Y-m-d H:i'),
                'Expires' => $link->expires_at ? Carbon::parse($link->expires_at)->format('Y-m-d') : 'Never',
            ];
        }

        $this->table([
            'ID', 
            'Link ID', 
            'Short Code', 
            'Amount', 
            'Status', 
            'Client',
            'Customer',
            'Phone',
            'Uses',
            'Created',
            'Expires'
        ], $tableData);

        // Show payment URLs
        $this->newLine();
        $this->info('Payment URLs:');
        $baseUrl = 'https://zimapay.co.tz';
        foreach ($links as $link) {
            $url = $baseUrl . '/pay/' . $link->short_code;
            $this->line("  [{$link->short_code}] → {$url}");
        }
    }

    /**
     * Show detailed information for a specific payment link
     */
    protected function showLinkDetails($linkId)
    {
        $link = PaymentLink::with(['client', 'serviceMapping.service', 'transactions', 'items'])
            ->where(function($query) use ($linkId) {
                $query->where('link_id', $linkId)
                      ->orWhere('short_code', $linkId);
                // Only check ID if it's numeric
                if (is_numeric($linkId)) {
                    $query->orWhere('id', $linkId);
                }
            })
            ->first();

        if (!$link) {
            $this->error("Payment link not found: {$linkId}");
            return;
        }

        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('                    PAYMENT LINK DETAILS                    ');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        // Basic Information
        $this->info('📋 Basic Information:');
        $this->table([], [
            ['Link ID', $link->link_id],
            ['Short Code', $link->short_code],
            ['Status', $this->getStatusBadge($link->status)],
            ['Payment URL', 'https://zimapay.co.tz/pay/' . $link->short_code],
            ['Created', Carbon::parse($link->created_at)->format('Y-m-d H:i:s')],
            ['Expires', $link->expires_at ? Carbon::parse($link->expires_at)->format('Y-m-d H:i:s') : 'Never'],
        ]);

        // Financial Information
        $this->newLine();
        $this->info('💰 Financial Information:');
        $this->table([], [
            ['Amount', 'TZS ' . number_format($link->amount, 2)],
            ['Currency', $link->currency],
            ['Allow Partial', $link->allow_partial_payment ? 'Yes' : 'No'],
            ['Min Amount', $link->minimum_amount ? 'TZS ' . number_format($link->minimum_amount, 2) : 'N/A'],
            ['Max Amount', $link->maximum_amount ? 'TZS ' . number_format($link->maximum_amount, 2) : 'N/A'],
            ['Total Collected', 'TZS ' . number_format($link->total_collected, 2)],
        ]);

        // Customer Information
        $this->newLine();
        $this->info('👤 Customer Information:');
        $this->table([], [
            ['Name', $link->customer_name ?? 'N/A'],
            ['Phone', $link->customer_phone ?? 'N/A'],
            ['Email', $link->customer_email ?? 'N/A'],
            ['Reference', $link->client_reference ?? 'N/A'],
        ]);

        // Usage Statistics
        $this->newLine();
        $this->info('📊 Usage Statistics:');
        $this->table([], [
            ['Current Uses', $link->current_uses],
            ['Max Uses', $link->max_uses ?? 'Unlimited'],
            ['Is Reusable', $link->is_reusable ? 'Yes' : 'No'],
            ['Views Count', $link->views_count],
            ['Last Viewed', $link->last_viewed_at ? Carbon::parse($link->last_viewed_at)->format('Y-m-d H:i:s') : 'Never'],
            ['Conversion Rate', $link->conversion_rate . '%'],
        ]);

        // Payment Methods
        if ($link->allowed_networks) {
            $this->newLine();
            $this->info('📱 Allowed Payment Networks:');
            foreach ($link->allowed_networks as $network) {
                $this->line("  • {$network}");
            }
        }

        // Items
        if ($link->items && $link->items->count() > 0) {
            $this->newLine();
            $this->info('🛒 Payment Items:');
            $itemsData = [];
            foreach ($link->items as $item) {
                $itemsData[] = [
                    $item->item_reference,
                    $item->item_name,
                    'TZS ' . number_format($item->amount, 2),
                    'TZS ' . number_format($item->paid_amount, 2),
                    $item->status,
                ];
            }
            $this->table(['Reference', 'Name', 'Amount', 'Paid', 'Status'], $itemsData);
        }

        // Transactions
        if ($link->transactions && $link->transactions->count() > 0) {
            $this->newLine();
            $this->info('💳 Transaction History:');
            $transData = [];
            foreach ($link->transactions as $trans) {
                $transData[] = [
                    $trans->transaction_reference,
                    'TZS ' . number_format($trans->amount, 2),
                    $trans->payment_method ?? 'N/A',
                    $trans->status,
                    Carbon::parse($trans->created_at)->format('Y-m-d H:i'),
                ];
            }
            $this->table(['Reference', 'Amount', 'Method', 'Status', 'Date'], $transData);
        }

        // Additional Info
        if ($link->description || $link->narration) {
            $this->newLine();
            $this->info('📝 Additional Information:');
            if ($link->description) {
                $this->line("Description: {$link->description}");
            }
            if ($link->narration) {
                $this->line("Narration: {$link->narration}");
            }
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════');
    }

    /**
     * Show overall statistics
     */
    protected function showStatistics()
    {
        $this->newLine();
        $this->info('📊 Overall Statistics:');
        
        $stats = [
            'Total Links' => PaymentLink::count(),
            'Active Links' => PaymentLink::where('status', 'active')->count(),
            'Expired Links' => PaymentLink::where('status', 'expired')->count(),
            'Completed Links' => PaymentLink::where('status', 'completed')->count(),
            'Total Amount' => 'TZS ' . number_format(PaymentLink::sum('amount'), 2),
            'Total Collected' => 'TZS ' . number_format(
                PaymentLink::whereHas('transactions', function($q) {
                    $q->where('status', 'success');
                })->withSum(['transactions' => function($q) {
                    $q->where('status', 'success');
                }], 'amount')->get()->sum('transactions_sum_amount'), 2
            ),
        ];

        $tableData = [];
        foreach ($stats as $key => $value) {
            $tableData[] = [$key, $value];
        }

        $this->table(['Metric', 'Value'], $tableData);
    }

    /**
     * Get status badge with color
     */
    protected function getStatusBadge($status)
    {
        return match($status) {
            'active' => "🟢 {$status}",
            'expired' => "🔴 {$status}",
            'completed' => "✅ {$status}",
            'cancelled' => "⚫ {$status}",
            'pending' => "🟡 {$status}",
            default => $status
        };
    }
}