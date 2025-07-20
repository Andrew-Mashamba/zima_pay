<?php

namespace App\Livewire\Services;

use App\Models\Service;
use App\Models\Aggregator;
use Livewire\Component;
use Livewire\WithPagination;

class Services extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $aggregatorFilter = '';
    public $showModal = false;
    public $editingService = null;
    public $deleteServiceId = null;
    public $showDeleteModal = false;
    public $selectedMenuItem = 1;

    // Form fields
    public $name = '';
    public $code = '';
    public $description = '';
    public $aggregator_id = '';
    public $endpoint = '';
    public $method = 'POST';
    public $request_format = 'json';
    public $response_format = 'json';
    public $rate_limit = 100;
    public $timeout = 30;
    public $retry_attempts = 3;
    public $status = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:50|unique:services,code',
        'description' => 'nullable|string',
        'aggregator_id' => 'required|exists:aggregators,id',
        'endpoint' => 'required|string|max:255',
        'method' => 'required|in:GET,POST,PUT,PATCH,DELETE',
        'request_format' => 'required|in:json,xml,form',
        'response_format' => 'required|in:json,xml',
        'rate_limit' => 'required|integer|min:1',
        'timeout' => 'required|integer|min:1|max:300',
        'retry_attempts' => 'required|integer|min:0|max:10',
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

    public function updatedAggregatorFilter()
    {
        $this->resetPage();
    }

    public function openModal($serviceId = null)
    {
        if ($serviceId) {
            $this->editingService = Service::find($serviceId);
            $this->name = $this->editingService->name;
            $this->code = $this->editingService->code;
            $this->description = $this->editingService->description;
            $this->aggregator_id = $this->editingService->aggregator_id;
            $this->endpoint = $this->editingService->endpoint;
            $this->method = $this->editingService->method;
            $this->request_format = $this->editingService->request_format;
            $this->response_format = $this->editingService->response_format;
            $this->rate_limit = $this->editingService->rate_limit;
            $this->timeout = $this->editingService->timeout;
            $this->retry_attempts = $this->editingService->retry_attempts;
            $this->status = $this->editingService->status;
        } else {
            $this->resetForm();
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->editingService = null;
    }

    public function resetForm()
    {
        $this->name = '';
        $this->code = '';
        $this->description = '';
        $this->aggregator_id = '';
        $this->endpoint = '';
        $this->method = 'POST';
        $this->request_format = 'json';
        $this->response_format = 'json';
        $this->rate_limit = 100;
        $this->timeout = 30;
        $this->retry_attempts = 3;
        $this->status = true;
    }

    public function save()
    {
        if ($this->editingService) {
            $this->rules['code'] = 'required|string|max:50|unique:services,code,' . $this->editingService->id;
        }

        $this->validate();

        $data = [
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'aggregator_id' => $this->aggregator_id,
            'endpoint' => $this->endpoint,
            'method' => $this->method,
            'request_format' => $this->request_format,
            'response_format' => $this->response_format,
            'rate_limit' => $this->rate_limit,
            'timeout' => $this->timeout,
            'retry_attempts' => $this->retry_attempts,
            'status' => $this->status,
        ];

        if ($this->editingService) {
            $this->editingService->update($data);
            session()->flash('message', 'Service updated successfully!');
        } else {
            Service::create($data);
            session()->flash('message', 'Service created successfully!');
        }

        $this->closeModal();
    }

    public function confirmDelete($serviceId)
    {
        $this->deleteServiceId = $serviceId;
        $this->showDeleteModal = true;
    }

    public function deleteService()
    {
        $service = Service::find($this->deleteServiceId);
        if ($service) {
            $service->delete();
            session()->flash('message', 'Service deleted successfully!');
        }
        $this->showDeleteModal = false;
        $this->deleteServiceId = null;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteServiceId = null;
    }

    public function selectedMenu($menuId)
    {
        $this->selectedMenuItem = $menuId;
        
        // If selecting "Add New Service", also open the modal
        if ($menuId == 2) {
            $this->openModal();
        }
    }

    public function toggleStatus($serviceId)
    {
        $service = Service::find($serviceId);
        if ($service) {
            $service->update(['status' => !$service->status]);
            session()->flash('message', 'Service status updated!');
        }
    }

    public function render()
    {
        $services = Service::query()
            ->with('aggregator')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('status', $this->statusFilter === 'active');
            })
            ->when($this->aggregatorFilter, function ($query) {
                $query->where('aggregator_id', $this->aggregatorFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $aggregators = Aggregator::where('status', true)->orderBy('name')->get();

        return view('livewire.services.services', compact('services', 'aggregators'));
    }
}
