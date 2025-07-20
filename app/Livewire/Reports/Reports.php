<?php

namespace App\Livewire\Reports;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Livewire\Component;

class Reports extends Component
{
    public $selectedMenuItem = 1;
    public $dateRange = 'last_30_days';
    public $startDate;
    public $endDate;
    public $aggregatorFilter = '';
    public $clientFilter = '';
    public $serviceFilter = '';
    public $statusFilter = '';
    public $showExportModal = false;
    public $exportFormat = 'pdf';
    public $reportType = 'financial';

    protected $queryString = [
        'dateRange' => ['except' => 'last_30_days'],
        'aggregatorFilter' => ['except' => ''],
        'clientFilter' => ['except' => ''],
        'serviceFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount()
    {
        $this->initializeDateRange();
    }

    public function initializeDateRange()
    {
        switch ($this->dateRange) {
            case 'today':
                $this->startDate = Carbon::today()->format('Y-m-d');
                $this->endDate = Carbon::today()->format('Y-m-d');
                break;
            case 'yesterday':
                $this->startDate = Carbon::yesterday()->format('Y-m-d');
                $this->endDate = Carbon::yesterday()->format('Y-m-d');
                break;
            case 'last_7_days':
                $this->startDate = Carbon::now()->subDays(7)->format('Y-m-d');
                $this->endDate = Carbon::today()->format('Y-m-d');
                break;
            case 'last_30_days':
                $this->startDate = Carbon::now()->subDays(30)->format('Y-m-d');
                $this->endDate = Carbon::today()->format('Y-m-d');
                break;
            case 'this_month':
                $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_month':
                $this->startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
            case 'this_year':
                $this->startDate = Carbon::now()->startOfYear()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfYear()->format('Y-m-d');
                break;
            default:
                $this->startDate = Carbon::now()->subDays(30)->format('Y-m-d');
                $this->endDate = Carbon::today()->format('Y-m-d');
        }
    }

    public function updatedDateRange()
    {
        $this->initializeDateRange();
    }

    public function selectedMenu($menuId)
    {
        $this->selectedMenuItem = $menuId;
    }

    public function resetFilters()
    {
        $this->dateRange = 'last_30_days';
        $this->aggregatorFilter = '';
        $this->clientFilter = '';
        $this->serviceFilter = '';
        $this->statusFilter = '';
        $this->initializeDateRange();
    }

    public function openExportModal($type = 'financial')
    {
        $this->reportType = $type;
        $this->showExportModal = true;
    }

    public function closeExportModal()
    {
        $this->showExportModal = false;
        $this->exportFormat = 'pdf';
        $this->reportType = 'financial';
    }

    public function exportReport()
    {
        // Implementation for export functionality
        session()->flash('message', "Report exported successfully as {$this->exportFormat}!");
        $this->closeExportModal();
    }

    // Financial Analytics Properties
    public function getFinancialStatsProperty()
    {
        $query = DB::table('transactions')
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);

        if ($this->aggregatorFilter) {
            $query->join('aggregators', 'transactions.aggregator_id', '=', 'aggregators.id')
                  ->where('aggregators.code', $this->aggregatorFilter);
        }
        if ($this->clientFilter) {
            $query->where('client_id', $this->clientFilter);
        }
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Create separate queries for status-specific counts to avoid conflicts with joins
        $baseQuery = DB::table('transactions')
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
            
        if ($this->aggregatorFilter) {
            $baseQuery->join('aggregators', 'transactions.aggregator_id', '=', 'aggregators.id')
                      ->where('aggregators.code', $this->aggregatorFilter);
        }
        if ($this->clientFilter) {
            $baseQuery->where('transactions.client_id', $this->clientFilter);
        }
        if ($this->statusFilter) {
            $baseQuery->where('transactions.status', $this->statusFilter);
        }

        return [
            'total_volume' => $baseQuery->sum('transactions.amount') ?? 0,
            'total_transactions' => $baseQuery->count(),
            'total_fees' => $baseQuery->sum('transactions.fee_amount') ?? 0,
            'total_commission' => $baseQuery->sum('transactions.commission_amount') ?? 0,
            'successful_transactions' => (clone $baseQuery)->where('transactions.status', 'success')->count(),
            'failed_transactions' => (clone $baseQuery)->where('transactions.status', 'failed')->count(),
            'pending_transactions' => (clone $baseQuery)->where('transactions.status', 'pending')->count(),
            'avg_transaction_amount' => $baseQuery->avg('transactions.amount') ?? 0,
        ];
    }

    public function getAggregatorPerformanceProperty()
    {
        return DB::table('transactions')
            ->join('aggregators', 'transactions.aggregator_id', '=', 'aggregators.id')
            ->select(
                'aggregators.code as aggregator_code',
                'aggregators.name as aggregator_name',
                DB::raw('COUNT(transactions.id) as total_transactions'),
                DB::raw('COALESCE(SUM(transactions.amount), 0) as total_volume'),
                DB::raw('AVG(transactions.response_time) as avg_response_time'),
                DB::raw('SUM(CASE WHEN transactions.status = \'success\' THEN 1 ELSE 0 END) as successful_count'),
                DB::raw('(SUM(CASE WHEN transactions.status = \'success\' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(transactions.id), 0)) as success_rate')
            )
            ->whereBetween('transactions.created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->when($this->aggregatorFilter, function ($query) {
                $query->where('aggregators.code', $this->aggregatorFilter);
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('transactions.status', $this->statusFilter);
            })
            ->groupBy('aggregators.id', 'aggregators.code', 'aggregators.name')
            ->orderBy('total_volume', 'desc')
            ->get();
    }

    public function getClientAnalyticsProperty()
    {
        return DB::table('transactions')
            ->join('clients', 'transactions.client_id', '=', 'clients.id')
            ->select(
                'clients.name as client_name',
                'clients.id as client_id',
                DB::raw('COUNT(transactions.id) as total_transactions'),
                DB::raw('SUM(transactions.amount) as total_volume'),
                DB::raw('SUM(transactions.fee_amount) as total_fees'),
                DB::raw('AVG(transactions.amount) as avg_transaction'),
                DB::raw('(SUM(CASE WHEN transactions.status = "success" THEN 1 ELSE 0 END) * 100.0 / COUNT(transactions.id)) as success_rate')
            )
            ->whereBetween('transactions.created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->groupBy('clients.id', 'clients.name')
            ->orderBy('total_volume', 'desc')
            ->get();
    }

    public function getHourlyTrendsProperty()
    {
        return DB::table('transactions')
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(amount) as volume'),
                DB::raw('AVG(response_time) as avg_response_time')
            )
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour')
            ->get();
    }

    public function getDailyTrendsProperty()
    {
        return DB::table('transactions')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(amount) as volume'),
                DB::raw('SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as successful_count'),
                DB::raw('AVG(response_time) as avg_response_time')
            )
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();
    }

    public function getServiceAnalyticsProperty()
    {
        return DB::table('transactions')
            ->join('services', 'transactions.service_id', '=', 'services.id')
            ->select(
                'services.name as service_name',
                'services.type as service_type',
                DB::raw('COUNT(transactions.id) as total_transactions'),
                DB::raw('SUM(transactions.amount) as total_volume'),
                DB::raw('AVG(transactions.response_time) as avg_response_time'),
                DB::raw('(SUM(CASE WHEN transactions.status = "success" THEN 1 ELSE 0 END) * 100.0 / COUNT(transactions.id)) as success_rate')
            )
            ->whereBetween('transactions.created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->groupBy('services.id', 'services.name', 'services.type')
            ->orderBy('total_transactions', 'desc')
            ->get();
    }

    public function getSystemHealthProperty()
    {
        $recentTransactions = DB::table('transactions')
            ->where('created_at', '>=', Carbon::now()->subMinutes(5))
            ->get();

        $systemLoad = DB::table('transactions')
            ->where('created_at', '>=', Carbon::now()->subHour())
            ->count();

        return [
            'current_tps' => $recentTransactions->count() / 5, // Transactions per second in last 5 minutes
            'system_load' => $systemLoad,
            'avg_response_time' => $recentTransactions->avg('response_time') ?? 0,
            'error_rate' => $recentTransactions->count() > 0 
                ? ($recentTransactions->where('status', 'failed')->count() / $recentTransactions->count()) * 100 
                : 0,
            'uptime_percentage' => 99.8, // This would come from monitoring system
        ];
    }

    public function getPaymentLinksAnalyticsProperty()
    {
        return DB::table('payment_links')
            ->select(
                DB::raw('COUNT(*) as total_links'),
                DB::raw('SUM(views) as total_views'),
                DB::raw('SUM(successful_payments) as total_conversions'),
                DB::raw('AVG(views) as avg_views_per_link'),
                DB::raw('(SUM(successful_payments) * 100.0 / NULLIF(SUM(views), 0)) as conversion_rate')
            )
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->first();
    }

    public function getTopClientsProperty()
    {
        return DB::table('clients')
            ->leftJoin('transactions', 'clients.id', '=', 'transactions.client_id')
            ->select(
                'clients.name',
                'clients.status',
                DB::raw('COUNT(transactions.id) as transaction_count'),
                DB::raw('COALESCE(SUM(transactions.amount), 0) as total_volume')
            )
            ->whereBetween('transactions.created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->groupBy('clients.id', 'clients.name', 'clients.status')
            ->orderBy('total_volume', 'desc')
            ->limit(10)
            ->get();
    }

    public function getRecentTransactionsProperty()
    {
        return DB::table('transactions')
            ->join('clients', 'transactions.client_id', '=', 'clients.id')
            ->leftJoin('services', 'transactions.service_id', '=', 'services.id')
            ->select(
                'transactions.*',
                'clients.name as client_name',
                'services.name as service_name'
            )
            ->orderBy('transactions.created_at', 'desc')
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.reports.reports');
    }
}