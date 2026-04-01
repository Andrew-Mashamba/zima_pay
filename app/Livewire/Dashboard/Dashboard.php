<?php

namespace App\Livewire\Dashboard;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public $refreshInterval = 30; // seconds

    public function mount()
    {
        // Auto-refresh every 30 seconds
        $this->dispatch('startAutoRefresh', interval: $this->refreshInterval * 1000);
    }

    public function getStatsProperty()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisMonth = Carbon::now()->startOfMonth();
        
        return [
            // Basic counts
            'total_clients' => DB::table('clients')->count(),
            'active_clients' => DB::table('clients')->where('status', true)->count(),
            'total_aggregators' => DB::table('aggregators')->count(),
            'active_aggregators' => DB::table('aggregators')->where('status', true)->count(),
            'total_services' => DB::table('services')->count(),
            'active_services' => DB::table('services')->where('status', true)->count(),
            
            // Transaction stats
            'total_transactions' => DB::table('transactions')->count(),
            'today_transactions' => DB::table('transactions')->whereDate('created_at', $today)->count(),
            'yesterday_transactions' => DB::table('transactions')->whereDate('created_at', $yesterday)->count(),
            'this_month_transactions' => DB::table('transactions')->where('created_at', '>=', $thisMonth)->count(),
            
            'successful_transactions' => DB::table('transactions')->where('status', 'success')->count(),
            'failed_transactions' => DB::table('transactions')->where('status', 'failed')->count(),
            'pending_transactions' => DB::table('transactions')->where('status', 'pending')->count(),
            
            // Financial stats
            'total_volume' => DB::table('transactions')->where('status', 'success')->sum('amount') ?? 0,
            'today_volume' => DB::table('transactions')->where('status', 'success')->whereDate('created_at', $today)->sum('amount') ?? 0,
            'total_fees' => DB::table('transactions')->where('status', 'success')->sum('fee_amount') ?? 0,
            'total_commission' => DB::table('transactions')->where('status', 'success')->sum('commission_amount') ?? 0,
            
            // Performance stats
            'avg_response_time' => DB::table('transactions')->whereNotNull('response_time')->avg('response_time') ?? 0,
            'success_rate' => $this->calculateSuccessRate(),
        ];
    }

    public function getRecentTransactionsProperty()
    {
        return DB::table('transactions')
            ->join('clients', 'transactions.client_id', '=', 'clients.id')
            ->join('aggregators', 'transactions.aggregator_id', '=', 'aggregators.id')
            ->leftJoin('services', 'transactions.service_id', '=', 'services.id')
            ->select(
                'transactions.*',
                'clients.name as client_name',
                'aggregators.name as aggregator_name',
                'services.name as service_name'
            )
            ->orderBy('transactions.created_at', 'desc')
            ->limit(10)
            ->get();
    }

    public function getTopClientsProperty()
    {
        $transactionStats = DB::table('transactions')
            ->select(
                'client_id',
                DB::raw('COUNT(id) as transactions_count'),
                DB::raw('COALESCE(SUM(amount), 0) as total_volume')
            )
            ->groupBy('client_id');

        return DB::table('clients')
            ->leftJoinSub($transactionStats, 'stats', 'clients.id', '=', 'stats.client_id')
            ->select(
                'clients.*',
                DB::raw('COALESCE(stats.transactions_count, 0) as transactions_count'),
                DB::raw('COALESCE(stats.total_volume, 0) as total_volume')
            )
            ->orderByDesc('transactions_count')
            ->limit(5)
            ->get();
    }

    public function getTopAggregatorsProperty()
    {
        $transactionStats = DB::table('transactions')
            ->select(
                'aggregator_id',
                DB::raw('COUNT(id) as transactions_count'),
                DB::raw('COALESCE(SUM(amount), 0) as total_volume'),
                DB::raw('AVG(response_time) as avg_response_time')
            )
            ->groupBy('aggregator_id');

        return DB::table('aggregators')
            ->leftJoinSub($transactionStats, 'stats', 'aggregators.id', '=', 'stats.aggregator_id')
            ->select(
                'aggregators.*',
                DB::raw('COALESCE(stats.transactions_count, 0) as transactions_count'),
                DB::raw('COALESCE(stats.total_volume, 0) as total_volume'),
                DB::raw('stats.avg_response_time as avg_response_time')
            )
            ->orderByDesc('transactions_count')
            ->limit(5)
            ->get();
    }

    public function getHourlyStatsProperty()
    {
        return DB::table('transactions')
            ->select(
                DB::raw('EXTRACT(HOUR FROM created_at) as hour'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(CASE WHEN status = \'success\' THEN 1 ELSE 0 END) as successful'),
                DB::raw('COALESCE(SUM(amount), 0) as volume')
            )
            ->whereDate('created_at', Carbon::today())
            ->groupBy(DB::raw('EXTRACT(HOUR FROM created_at)'))
            ->orderBy('hour')
            ->get();
    }

    public function getSystemHealthProperty()
    {
        $recentTransactions = DB::table('transactions')
            ->where('created_at', '>=', Carbon::now()->subMinutes(5))
            ->get();

        return [
            'current_tps' => $recentTransactions->count() / 5, // Transactions per second
            'active_connections' => DB::table('clients')->where('status', true)->count(),
            'system_load' => DB::table('transactions')->where('created_at', '>=', Carbon::now()->subHour())->count(),
            'error_rate' => $recentTransactions->count() > 0 
                ? ($recentTransactions->where('status', 'failed')->count() / $recentTransactions->count()) * 100 
                : 0,
            'avg_response_time' => $recentTransactions->avg('response_time') ?? 0,
        ];
    }

    public function getRecentAlertsProperty()
    {
        // Simulated alerts based on system conditions
        $alerts = collect();
        
        $failedTransactions = DB::table('transactions')
            ->where('status', 'failed')
            ->where('created_at', '>=', Carbon::now()->subHour())
            ->count();
            
        if ($failedTransactions > 10) {
            $alerts->push((object)[
                'type' => 'warning',
                'message' => "High failure rate detected: {$failedTransactions} failed transactions in the last hour",
                'created_at' => Carbon::now()->subMinutes(rand(1, 60))
            ]);
        }
        
        $highResponseTime = DB::table('transactions')
            ->where('created_at', '>=', Carbon::now()->subMinutes(30))
            ->avg('response_time');
            
        if ($highResponseTime > 5000) {
            $alerts->push((object)[
                'type' => 'error',
                'message' => "High response time detected: {$highResponseTime}ms average",
                'created_at' => Carbon::now()->subMinutes(rand(1, 30))
            ]);
        }
        
        return $alerts->sortByDesc('created_at')->take(5);
    }

    private function calculateSuccessRate()
    {
        $total = DB::table('transactions')->count();
        $successful = DB::table('transactions')->where('status', 'success')->count();
        
        return $total > 0 ? ($successful / $total) * 100 : 0;
    }

    public function refresh()
    {
        // Force refresh of cached properties
        $this->dispatch('refreshed');
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard');
    }
}
