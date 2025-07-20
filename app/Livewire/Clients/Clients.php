<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class Clients extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $showModal = false;
    public $editingClient = null;
    public $deleteClientId = null;
    public $showDeleteModal = false;
    public $showBalanceModal = false;
    public $showStatementModal = false;
    public $selectedClient = null;
    public $clientBalance = null;
    public $clientStatement = [];
    public $loadingBalance = false;
    public $loadingStatement = false;
    public $selectedMenuItem = 1;

    // Form fields
    public $name = '';
    public $code = '';
    public $description = '';
    public $api_key = '';
    public $api_secret = '';
    public $webhook_url = '';
    public $contact_person = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $status = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:50|unique:clients,code',
        'description' => 'nullable|string',
        'api_key' => 'required|string|unique:clients,api_key',
        'api_secret' => 'required|string',
        'webhook_url' => 'nullable|url',
        'contact_person' => 'nullable|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string',
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

    public function openModal($clientId = null)
    {
        if ($clientId) {
            $this->editingClient = Client::find($clientId);
            $this->name = $this->editingClient->name;
            $this->code = $this->editingClient->code;
            $this->description = $this->editingClient->description;
            $this->api_key = $this->editingClient->api_key;
            $this->api_secret = $this->editingClient->api_secret;
            $this->webhook_url = $this->editingClient->webhook_url;
            $this->contact_person = $this->editingClient->contact_person;
            $this->email = $this->editingClient->email;
            $this->phone = $this->editingClient->phone;
            $this->address = $this->editingClient->address;
            $this->status = $this->editingClient->status;
        } else {
            $this->resetForm();
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->editingClient = null;
    }

    public function resetForm()
    {
        $this->name = '';
        $this->code = '';
        $this->description = '';
        $this->api_key = '';
        $this->api_secret = '';
        $this->webhook_url = '';
        $this->contact_person = '';
        $this->email = '';
        $this->phone = '';
        $this->address = '';
        $this->status = true;
    }

    public function save()
    {
        if ($this->editingClient) {
            $this->rules['code'] = 'required|string|max:50|unique:clients,code,' . $this->editingClient->id;
            $this->rules['api_key'] = 'required|string|unique:clients,api_key,' . $this->editingClient->id;
        }

        $this->validate();

        $data = [
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'api_key' => $this->api_key,
            'api_secret' => $this->api_secret,
            'webhook_url' => $this->webhook_url,
            'contact_person' => $this->contact_person,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'status' => $this->status,
        ];

        if ($this->editingClient) {
            $this->editingClient->update($data);
            session()->flash('message', 'Client updated successfully!');
        } else {
            Client::create($data);
            session()->flash('message', 'Client created successfully!');
        }

        $this->closeModal();
    }

    public function confirmDelete($clientId)
    {
        $this->deleteClientId = $clientId;
        $this->showDeleteModal = true;
    }

    public function deleteClient()
    {
        $client = Client::find($this->deleteClientId);
        if ($client) {
            $client->delete();
            session()->flash('message', 'Client deleted successfully!');
        }
        $this->showDeleteModal = false;
        $this->deleteClientId = null;
    }

    public function toggleStatus($clientId)
    {
        $client = Client::find($clientId);
        if ($client) {
            $client->update(['status' => !$client->status]);
            session()->flash('message', 'Client status updated!');
        }
    }

    public function showBalance($clientId)
    {
        $this->selectedClient = Client::find($clientId);
        $this->loadingBalance = true;
        $this->showBalanceModal = true;
        
        try {
            // Call the ESB service to get balance
            $esbService = app(\App\Services\EsbService::class);
            $serviceMapping = \App\Models\ServiceMapping::where('client_id', $clientId)
                ->whereHas('service', function($q) {
                    $q->where('code', 'COLLECTION_BALANCE');
                })
                ->first();
            
            if ($serviceMapping) {
                $response = $esbService->processRequest($serviceMapping, [], new \App\Models\Transaction());
                $this->clientBalance = $response['response'] ?? null;
            } else {
                $this->clientBalance = ['error' => 'No balance service configured for this client'];
            }
        } catch (\Exception $e) {
            $this->clientBalance = ['error' => 'Failed to fetch balance: ' . $e->getMessage()];
        }
        
        $this->loadingBalance = false;
    }

    public function showStatement($clientId)
    {
        $this->selectedClient = Client::find($clientId);
        $this->loadingStatement = true;
        $this->showStatementModal = true;
        
        try {
            // Call the ESB service to get statement
            $esbService = app(\App\Services\EsbService::class);
            $serviceMapping = \App\Models\ServiceMapping::where('client_id', $clientId)
                ->whereHas('service', function($q) {
                    $q->where('code', 'COLLECTION_STATEMENT');
                })
                ->first();
            
            if ($serviceMapping) {
                $statementData = [
                    'start_date' => date('Y-m-d', strtotime('-30 days')),
                    'end_date' => date('Y-m-d', strtotime('+1 day'))
                ];
                
                $response = $esbService->processRequest($serviceMapping, $statementData, new \App\Models\Transaction());
                $this->clientStatement = $response['response'] ?? [];
            } else {
                $this->clientStatement = ['error' => 'No statement service configured for this client'];
            }
        } catch (\Exception $e) {
            $this->clientStatement = ['error' => 'Failed to fetch statement: ' . $e->getMessage()];
        }
        
        $this->loadingStatement = false;
    }

    public function closeBalanceModal()
    {
        $this->showBalanceModal = false;
        $this->selectedClient = null;
        $this->clientBalance = null;
        $this->loadingBalance = false;
    }

    public function closeStatementModal()
    {
        $this->showStatementModal = false;
        $this->selectedClient = null;
        $this->clientStatement = [];
        $this->loadingStatement = false;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteClientId = null;
    }

    public function selectedMenu($menuId)
    {
        $this->selectedMenuItem = $menuId;
        
        // If selecting "Add New Client", also open the modal
        if ($menuId == 2) {
            $this->openModal();
        }
    }

    public function render()
    {
        $clients = Client::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%')
                      ->orWhere('contact_person', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('status', $this->statusFilter === 'active');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.clients.clients', compact('clients'));
    }
}
