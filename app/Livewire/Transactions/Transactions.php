<?php

namespace App\Livewire\Transactions;

use App\Models\Transaction;
use App\Models\Client;
use App\Models\Service;
use App\Models\Aggregator;
use Livewire\Component;
use Livewire\WithPagination;

class Transactions extends Component
{
    use WithPagination;

    public $search = '';
    public $clientFilter = '';
    public $serviceFilter = '';
    public $aggregatorFilter = '';
    public $statusFilter = '';
    public $riskFilter = '';
    public $dateFromFilter = '';
    public $dateToFilter = '';
    public $amountFromFilter = '';
    public $amountToFilter = '';
    public $selectedMenuItem = 1;
    public $viewTransactionId = null;
    public $showViewModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'clientFilter' => ['except' => ''],
        'serviceFilter' => ['except' => ''],
        'aggregatorFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'riskFilter' => ['except' => ''],
        'dateFromFilter' => ['except' => ''],
        'dateToFilter' => ['except' => ''],
        'amountFromFilter' => ['except' => ''],
        'amountToFilter' => ['except' => ''],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedClientFilter()
    {
        $this->resetPage();
    }

    public function updatedServiceFilter()
    {
        $this->resetPage();
    }

    public function updatedAggregatorFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedRiskFilter()
    {
        $this->resetPage();
    }

    public function updatedDateFromFilter()
    {
        $this->resetPage();
    }

    public function updatedDateToFilter()
    {
        $this->resetPage();
    }

    public function updatedAmountFromFilter()
    {
        $this->resetPage();
    }

    public function updatedAmountToFilter()
    {
        $this->resetPage();
    }

    public function selectedMenu($menuId)
    {
        $this->selectedMenuItem = $menuId;
        
        // Reset filters based on selected menu
        switch ($menuId) {
            case 1: // All Transactions
                $this->resetFilters();
                break;
            case 3: // Pending
                $this->statusFilter = 'pending';
                break;
            case 4: // Success
                $this->statusFilter = 'success';
                break;
            case 5: // Failed
                $this->statusFilter = 'failed';
                break;
            case 6: // High Risk
                $this->riskFilter = 'high';
                break;
        }
        
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->clientFilter = '';
        $this->serviceFilter = '';
        $this->aggregatorFilter = '';
        $this->statusFilter = '';
        $this->riskFilter = '';
        $this->dateFromFilter = '';
        $this->dateToFilter = '';
        $this->amountFromFilter = '';
        $this->amountToFilter = '';
        $this->resetPage();
    }

    public function viewTransaction($transactionId)
    {
        $this->viewTransactionId = $transactionId;
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewTransactionId = null;
    }

    public function reconcileTransaction($transactionId)
    {
        $transaction = Transaction::find($transactionId);
        if ($transaction && !$transaction->is_reconciled) {
            $transaction->markAsReconciled('System Admin', 'Manual reconciliation via dashboard');
            session()->flash('message', 'Transaction reconciled successfully!');
        }
    }

    public function settleTransaction($transactionId)
    {
        $transaction = Transaction::find($transactionId);
        if ($transaction && !$transaction->is_settled) {
            $transaction->markAsSettled($transaction->transaction_id, $transaction->amount);
            session()->flash('message', 'Transaction settled successfully!');
        }
    }

    public function flagSuspicious($transactionId)
    {
        $transaction = Transaction::find($transactionId);
        if ($transaction) {
            $transaction->update([
                'is_suspicious' => !$transaction->is_suspicious,
                'requires_manual_review' => true
            ]);
            $action = $transaction->is_suspicious ? 'flagged as suspicious' : 'unflagged';
            session()->flash('message', "Transaction {$action} successfully!");
        }
    }

    public function getStatsProperty()
    {
        $baseQuery = Transaction::query();
        
        return [
            'total' => $baseQuery->count(),
            'today' => $baseQuery->today()->count(),
            'successful' => $baseQuery->successful()->count(),
            'failed' => $baseQuery->failed()->count(),
            'pending' => $baseQuery->where('status', 'pending')->count(),
            'total_amount' => $baseQuery->successful()->sum('amount'),
            'avg_response_time' => $baseQuery->successful()->avg('total_processing_time'),
            'reconciled' => $baseQuery->reconciled()->count(),
            'unreconciled' => $baseQuery->unreconciled()->count(),
            'high_risk' => $baseQuery->highRisk()->count(),
            'suspicious' => $baseQuery->suspicious()->count(),
        ];
    }

    public function render()
    {
        $transactions = Transaction::query()
            ->with(['client', 'service', 'aggregator'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('transaction_id', 'like', '%' . $this->search . '%')
                      ->orWhere('external_transaction_id', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_phone', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%')
                      ->orWhereHas('client', function($c) {
                          $c->where('name', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('service', function($s) {
                          $s->where('name', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('aggregator', function($a) {
                          $a->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->clientFilter, function ($query) {
                $query->where('client_id', $this->clientFilter);
            })
            ->when($this->serviceFilter, function ($query) {
                $query->where('service_id', $this->serviceFilter);
            })
            ->when($this->aggregatorFilter, function ($query) {
                $query->where('aggregator_id', $this->aggregatorFilter);
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->riskFilter, function ($query) {
                $query->where('risk_level', $this->riskFilter);
            })
            ->when($this->dateFromFilter, function ($query) {
                $query->whereDate('created_at', '>=', $this->dateFromFilter);
            })
            ->when($this->dateToFilter, function ($query) {
                $query->whereDate('created_at', '<=', $this->dateToFilter);
            })
            ->when($this->amountFromFilter, function ($query) {
                $query->where('amount', '>=', $this->amountFromFilter);
            })
            ->when($this->amountToFilter, function ($query) {
                $query->where('amount', '<=', $this->amountToFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $clients = Client::where('status', true)->orderBy('name')->get();
        $services = Service::where('status', true)->orderBy('name')->get();
        $aggregators = Aggregator::where('status', true)->orderBy('name')->get();
        
        $viewTransaction = null;
        if ($this->viewTransactionId) {
            $viewTransaction = Transaction::with(['client', 'service', 'aggregator'])
                ->find($this->viewTransactionId);
        }

        return view('livewire.transactions.transactions', compact(
            'transactions', 
            'clients', 
            'services', 
            'aggregators',
            'viewTransaction'
        ));
    }
}