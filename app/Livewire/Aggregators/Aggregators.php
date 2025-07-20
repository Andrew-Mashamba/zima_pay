<?php

namespace App\Livewire\Aggregators;

use App\Models\Aggregator;
use Livewire\Component;
use Livewire\WithPagination;

class Aggregators extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $showModal = false;
    public $editingAggregator = null;
    public $deleteAggregatorId = null;
    public $showDeleteModal = false;
    public $selectedMenuItem = 1;

    // Form fields
    public $name = '';
    public $code = '';
    public $description = '';
    public $api_endpoint = '';
    public $api_key = '';
    public $api_secret = '';
    public $webhook_url = '';
    public $rate_limit = 1000;
    public $timeout = 30;
    public $retry_attempts = 3;
    public $contact_person = '';
    public $contact_email = '';
    public $contact_phone = '';
    public $status = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:50|unique:aggregators,code',
        'description' => 'nullable|string',
        'api_endpoint' => 'required|url',
        'api_key' => 'nullable|string',
        'api_secret' => 'nullable|string',
        'webhook_url' => 'nullable|url',
        'rate_limit' => 'required|integer|min:1',
        'timeout' => 'required|integer|min:1|max:300',
        'retry_attempts' => 'required|integer|min:0|max:10',
        'contact_person' => 'nullable|string|max:255',
        'contact_email' => 'nullable|email|max:255',
        'contact_phone' => 'nullable|string|max:20',
        'status' => 'boolean'
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function openModal($aggregatorId = null)
    {
        if ($aggregatorId) {
            $this->editingAggregator = Aggregator::find($aggregatorId);
            $this->name = $this->editingAggregator->name;
            $this->code = $this->editingAggregator->code;
            $this->description = $this->editingAggregator->description;
            $this->api_endpoint = $this->editingAggregator->api_endpoint;
            $this->api_key = $this->editingAggregator->api_key;
            $this->api_secret = $this->editingAggregator->api_secret;
            $this->webhook_url = $this->editingAggregator->webhook_url;
            $this->rate_limit = $this->editingAggregator->rate_limit;
            $this->timeout = $this->editingAggregator->timeout;
            $this->retry_attempts = $this->editingAggregator->retry_attempts;
            $this->contact_person = $this->editingAggregator->contact_person;
            $this->contact_email = $this->editingAggregator->contact_email;
            $this->contact_phone = $this->editingAggregator->contact_phone;
            $this->status = $this->editingAggregator->status;
        } else {
            $this->resetForm();
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->editingAggregator = null;
    }

    public function resetForm()
    {
        $this->name = '';
        $this->code = '';
        $this->description = '';
        $this->api_endpoint = '';
        $this->api_key = '';
        $this->api_secret = '';
        $this->webhook_url = '';
        $this->rate_limit = 1000;
        $this->timeout = 30;
        $this->retry_attempts = 3;
        $this->contact_person = '';
        $this->contact_email = '';
        $this->contact_phone = '';
        $this->status = true;
    }

    public function save()
    {
        if ($this->editingAggregator) {
            $this->rules['code'] = 'required|string|max:50|unique:aggregators,code,' . $this->editingAggregator->id;
        }

        $this->validate();

        $data = [
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'api_endpoint' => $this->api_endpoint,
            'api_key' => $this->api_key,
            'api_secret' => $this->api_secret,
            'webhook_url' => $this->webhook_url,
            'rate_limit' => $this->rate_limit,
            'timeout' => $this->timeout,
            'retry_attempts' => $this->retry_attempts,
            'contact_person' => $this->contact_person,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'status' => $this->status,
        ];

        if ($this->editingAggregator) {
            $this->editingAggregator->update($data);
            session()->flash('message', 'Aggregator updated successfully!');
        } else {
            Aggregator::create($data);
            session()->flash('message', 'Aggregator created successfully!');
        }

        $this->closeModal();
    }

    public function confirmDelete($aggregatorId)
    {
        $this->deleteAggregatorId = $aggregatorId;
        $this->showDeleteModal = true;
    }

    public function deleteAggregator()
    {
        $aggregator = Aggregator::find($this->deleteAggregatorId);
        if ($aggregator) {
            $aggregator->delete();
            session()->flash('message', 'Aggregator deleted successfully!');
        }
        $this->showDeleteModal = false;
        $this->deleteAggregatorId = null;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteAggregatorId = null;
    }

    public function selectedMenu($menuId)
    {
        $this->selectedMenuItem = $menuId;
        
        // If selecting "Add New Aggregator", also open the modal
        if ($menuId == 2) {
            $this->openModal();
        }
    }

    public function toggleStatus($aggregatorId)
    {
        $aggregator = Aggregator::find($aggregatorId);
        if ($aggregator) {
            $aggregator->update(['status' => !$aggregator->status]);
            session()->flash('message', 'Aggregator status updated!');
        }
    }

    public function render()
    {
        $aggregators = Aggregator::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%')
                      ->orWhere('contact_person', 'like', '%' . $this->search . '%')
                      ->orWhere('contact_email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('status', $this->statusFilter === 'active');
            })
            ->withCount(['services', 'clients'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.aggregators.aggregators', compact('aggregators'));
    }
}
