<?php

namespace App\Livewire\ServicesMapping;

use App\Models\ServiceMapping;
use App\Models\Client;
use App\Models\Service;
use App\Models\Aggregator;
use Livewire\Component;
use Livewire\WithPagination;

class ServicesMapping extends Component
{
    use WithPagination;

    public $search = '';
    public $clientFilter = '';
    public $serviceFilter = '';
    public $aggregatorFilter = '';
    public $statusFilter = '';
    public $showModal = false;
    public $editingMapping = null;
    public $deleteMappingId = null;
    public $showDeleteModal = false;
    public $selectedMenuItem = 1;

    // Form fields
    public $client_id = '';
    public $service_id = '';
    public $aggregator_id = '';
    public $name = '';
    public $description = '';
    public $request_mapping = [];
    public $response_mapping = [];
    public $transformation_rules = [];
    public $status = true;
    public $priority = 1;

    protected $rules = [
        'client_id' => 'required|exists:clients,id',
        'service_id' => 'required|exists:services,id',
        'aggregator_id' => 'required|exists:aggregators,id',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'request_mapping' => 'array',
        'response_mapping' => 'array',
        'transformation_rules' => 'array',
        'status' => 'boolean',
        'priority' => 'integer|min:1'
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

    public function openModal($mappingId = null)
    {
        if ($mappingId) {
            $this->editingMapping = ServiceMapping::find($mappingId);
            $this->client_id = $this->editingMapping->client_id;
            $this->service_id = $this->editingMapping->service_id;
            $this->aggregator_id = $this->editingMapping->aggregator_id;
            $this->name = $this->editingMapping->name;
            $this->description = $this->editingMapping->description;
            $this->request_mapping = $this->editingMapping->request_mapping ?? [];
            $this->response_mapping = $this->editingMapping->response_mapping ?? [];
            $this->transformation_rules = $this->editingMapping->transformation_rules ?? [];
            $this->status = $this->editingMapping->status;
            $this->priority = $this->editingMapping->priority;
        } else {
            $this->resetForm();
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->editingMapping = null;
    }

    public function resetForm()
    {
        $this->client_id = '';
        $this->service_id = '';
        $this->aggregator_id = '';
        $this->name = '';
        $this->description = '';
        $this->request_mapping = [];
        $this->response_mapping = [];
        $this->transformation_rules = [];
        $this->status = true;
        $this->priority = 1;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'client_id' => $this->client_id,
            'service_id' => $this->service_id,
            'aggregator_id' => $this->aggregator_id,
            'name' => $this->name,
            'description' => $this->description,
            'request_mapping' => $this->request_mapping,
            'response_mapping' => $this->response_mapping,
            'transformation_rules' => $this->transformation_rules,
            'status' => $this->status,
            'priority' => $this->priority,
        ];

        if ($this->editingMapping) {
            $this->editingMapping->update($data);
            session()->flash('message', 'Service mapping updated successfully!');
        } else {
            ServiceMapping::create($data);
            session()->flash('message', 'Service mapping created successfully!');
        }

        $this->closeModal();
    }

    public function confirmDelete($mappingId)
    {
        $this->deleteMappingId = $mappingId;
        $this->showDeleteModal = true;
    }

    public function deleteMapping()
    {
        $mapping = ServiceMapping::find($this->deleteMappingId);
        if ($mapping) {
            $mapping->delete();
            session()->flash('message', 'Service mapping deleted successfully!');
        }
        $this->showDeleteModal = false;
        $this->deleteMappingId = null;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteMappingId = null;
    }

    public function selectedMenu($menuId)
    {
        $this->selectedMenuItem = $menuId;
        
        // If selecting "Add New Mapping", also open the modal
        if ($menuId == 2) {
            $this->openModal();
        }
    }

    public function toggleStatus($mappingId)
    {
        $mapping = ServiceMapping::find($mappingId);
        if ($mapping) {
            $mapping->update(['status' => !$mapping->status]);
            session()->flash('message', 'Service mapping status updated!');
        }
    }

    public function render()
    {
        $mappings = ServiceMapping::query()
            ->with(['client', 'service', 'aggregator'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%')
                      ->orWhereHas('client', function($c) {
                          $c->where('name', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('service', function($s) {
                          $s->where('name', 'like', '%' . $this->search . '%');
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
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('status', $this->statusFilter === 'active');
            })
            ->orderBy('client_id')
            ->orderBy('priority')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $clients = Client::where('status', true)->orderBy('name')->get();
        $services = Service::where('status', true)->orderBy('name')->get();
        $aggregators = Aggregator::where('status', true)->orderBy('name')->get();

        return view('livewire.services-mapping.services-mapping', compact('mappings', 'clients', 'services', 'aggregators'));
    }
}
