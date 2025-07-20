<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-emerald-900 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Service Mappings</h1>
                        <p class="text-gray-600 mt-1">Connect clients, services, and aggregators in powerful combinations</p>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="flex items-center space-x-4">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Active Mappings</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $mappings->where('status', true)->count() }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Mappings</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $mappings->count() }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-emerald-100 rounded-lg">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Connected Clients</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $clients->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-6">
            <!-- Enhanced Sidebar -->
            <div class="w-80 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <!-- Search Section -->
                <div class="p-6 border-b border-gray-100">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            wire:model.live="search" 
                            placeholder="Search mappings, clients, or services..."
                            class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white focus:bg-white"
                            aria-label="Search mappings"
                        />
                    </div>
                </div>

                <!-- Navigation Menu -->
                <div class="p-4">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 px-2">Navigation</h3>
                    
                    @php
                        $mapping_sections = [
                            [
                                'id' => 1, 
                                'label' => 'All Mappings', 
                                'icon' => 'M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                                'description' => 'View all connections'
                            ],
                            [
                                'id' => 2, 
                                'label' => 'Add New Mapping', 
                                'icon' => 'M12 6v6m0 0v6m0-6h6m-6 0H6',
                                'description' => 'Create new mapping'
                            ],
                            [
                                'id' => 3, 
                                'label' => 'Active Mappings', 
                                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                'description' => 'Running connections'
                            ],
                            [
                                'id' => 4, 
                                'label' => 'Inactive Mappings', 
                                'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
                                'description' => 'Disabled connections'
                            ],
                            [
                                'id' => 5, 
                                'label' => 'Connection Matrix', 
                                'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
                                'description' => 'Visual overview'
                            ],
                            [
                                'id' => 6, 
                                'label' => 'Settings', 
                                'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                                'description' => 'Mapping settings'
                            ],
                        ];
                    @endphp

                    <nav class="space-y-2">
                        @foreach ($mapping_sections as $section)
                            @php
                                $count = 0;
                                if ($section['id'] == 3) {
                                    $count = $mappings->where('status', true)->count();
                                } elseif ($section['id'] == 4) {
                                    $count = $mappings->where('status', false)->count();
                                }
                                $isActive = $this->selectedMenuItem == $section['id'];
                            @endphp

                            <button
                                wire:click="selectedMenu({{ $section['id'] }})"
                                class="relative w-full group transition-all duration-200"
                                aria-label="{{ $section['label'] }}"
                            >
                                <div class="flex items-center p-3 rounded-xl transition-all duration-200
                                    @if ($isActive) 
                                        bg-emerald-900 text-white shadow-lg 
                                    @else 
                                        bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900 
                                    @endif">
                                    
                                    <!-- Loading State -->
                                    <div wire:loading wire:target="selectedMenu({{ $section['id'] }})" class="mr-3">
                                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>

                                    <!-- Icon -->
                                    <div wire:loading.remove wire:target="selectedMenu({{ $section['id'] }})" class="mr-3">
                                        <svg class="w-5 h-5 @if ($isActive) text-white @else text-gray-500 group-hover:text-gray-700 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $section['icon'] }}"></path>
                                        </svg>
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-1 text-left">
                                        <div class="font-medium text-sm">{{ $section['label'] }}</div>
                                        <div class="text-xs opacity-75">{{ $section['description'] }}</div>
                                    </div>

                                    <!-- Count Badge -->
                                    @if ($count > 0)
                                        <div class="ml-2">
                                            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full min-w-[20px] h-5">
                                                {{ $count > 99 ? '99+' : $count }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </nav>
                </div>

                <!-- Quick Actions -->
                <div class="p-4 border-t border-gray-100 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-2">Quick Actions</h3>
                    <div class="space-y-2">
                        <button wire:click="selectedMenu(2)" class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            New Mapping
                        </button>
                        <button wire:click="selectedMenu(5)" class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                            </svg>
                            View Matrix
                        </button>
                        <button class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            Export Config
                        </button>
                    </div>
                </div>
            </div>

            <!-- Enhanced Main Content Area -->
            <div class="flex-1">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <!-- Content Header -->
                    <div class="px-8 py-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">
                                    @switch($this->selectedMenuItem)
                                        @case(1) All Mappings @break
                                        @case(2) Add New Mapping @break
                                        @case(3) Active Mappings @break
                                        @case(4) Inactive Mappings @break
                                        @case(5) Connection Matrix @break
                                        @case(6) Settings @break
                                        @default All Mappings
                                    @endswitch
                                </h2>
                                <p class="text-gray-600 mt-1">
                                    @switch($this->selectedMenuItem)
                                        @case(1) Manage all your service mapping configurations @break
                                        @case(2) Create a new client-service-aggregator mapping @break
                                        @case(3) Monitor active service mappings @break
                                        @case(4) Review disabled service mappings @break
                                        @case(5) Visual overview of all service connections @break
                                        @case(6) Configure global mapping settings @break
                                        @default Manage all your service mapping configurations
                                    @endswitch
                                </p>
                            </div>
                            
                            <!-- Breadcrumb -->
                            <nav class="flex" aria-label="Breadcrumb">
                                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                                    <li class="inline-flex items-center">
                                        <a href="#" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-emerald-600">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                            </svg>
                                            Mappings
                                        </a>
                                    </li>
                                    <li>
                                        <div class="flex items-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">
                                                @switch($this->selectedMenuItem)
                                                    @case(1) All @break
                                                    @case(2) New @break
                                                    @case(3) Active @break
                                                    @case(4) Inactive @break
                                                    @case(5) Matrix @break
                                                    @case(6) Settings @break
                                                    @default All
                                                @endswitch
                                            </span>
                                        </div>
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="p-8">
                        <!-- Success Message -->
                        @if (session()->has('message'))
                            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 py-3 px-4 text-sm text-green-700">
                                {{ session('message') }}
                            </div>
                        @endif

                        <!-- Dynamic Content -->
                        <div wire:loading.remove wire:target="selectedMenu" class="min-h-[400px]">
                            @switch($this->selectedMenuItem)
                                @case(1)
                                @case(3)  
                                @case(4)
                                    <!-- Service Mappings List -->
                                    @php
                                        $filteredMappings = $mappings;
                                        if ($this->selectedMenuItem == 3) {
                                            $filteredMappings = $mappings->where('status', true);
                                        } elseif ($this->selectedMenuItem == 4) {
                                            $filteredMappings = $mappings->where('status', false);
                                        }
                                    @endphp

                                    <!-- Filters -->
                                    <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                                            <select wire:model.live="clientFilter" class="rounded-lg border border-gray-200 bg-white py-2 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                                <option value="">All Clients</option>
                                                @foreach($clients as $client)
                                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                                @endforeach
                                            </select>
                                            
                                            <select wire:model.live="serviceFilter" class="rounded-lg border border-gray-200 bg-white py-2 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                                <option value="">All Services</option>
                                                @foreach($services as $service)
                                                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                                                @endforeach
                                            </select>
                                            
                                            <select wire:model.live="aggregatorFilter" class="rounded-lg border border-gray-200 bg-white py-2 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                                <option value="">All Aggregators</option>
                                                @foreach($aggregators as $aggregator)
                                                    <option value="{{ $aggregator->id }}">{{ $aggregator->name }}</option>
                                                @endforeach
                                            </select>
                                            
                                            <select wire:model.live="statusFilter" class="rounded-lg border border-gray-200 bg-white py-2 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                                <option value="">All Status</option>
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                        
                                        <div class="flex items-center gap-2 text-sm text-gray-500">
                                            <span>Total: {{ $filteredMappings->count() }} mappings</span>
                                        </div>
                                    </div>

                                    <!-- Mappings Grid -->
                                    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                                        @forelse ($filteredMappings as $mapping)
                                            <div class="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-all duration-200 group">
                                                <!-- Mapping Header -->
                                                <div class="flex items-start justify-between mb-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $mapping->status ? 'bg-emerald-100' : 'bg-red-100' }}">
                                                            <svg class="w-6 h-6 {{ $mapping->status ? 'text-emerald-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <h5 class="font-semibold text-gray-900 group-hover:text-emerald-600 transition-colors">
                                                                {{ $mapping->name }}
                                                            </h5>
                                                            <p class="text-sm text-gray-500">Priority: {{ $mapping->priority }}</p>
                                                        </div>
                                                    </div>
                                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $mapping->status ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                                        {{ $mapping->status ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </div>

                                                <!-- Connection Flow -->
                                                <div class="space-y-3 mb-4">
                                                    <!-- Client -->
                                                    <div class="flex items-center justify-between bg-blue-50 rounded-lg p-3">
                                                        <div class="flex items-center gap-2">
                                                            <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center">
                                                                <span class="text-xs font-semibold text-blue-600">C</span>
                                                            </div>
                                                            <div>
                                                                <p class="text-sm font-medium text-gray-900">{{ $mapping->client->name }}</p>
                                                                <p class="text-xs text-gray-500">Client</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Arrow Down -->
                                                    <div class="flex justify-center">
                                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                                        </svg>
                                                    </div>
                                                    
                                                    <!-- Service -->
                                                    <div class="flex items-center justify-between bg-green-50 rounded-lg p-3">
                                                        <div class="flex items-center gap-2">
                                                            <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center">
                                                                <span class="text-xs font-semibold text-green-600">S</span>
                                                            </div>
                                                            <div>
                                                                <p class="text-sm font-medium text-gray-900">{{ $mapping->service->name }}</p>
                                                                <p class="text-xs text-gray-500">{{ $mapping->service->code }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Arrow Down -->
                                                    <div class="flex justify-center">
                                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                                        </svg>
                                                    </div>
                                                    
                                                    <!-- Aggregator -->
                                                    <div class="flex items-center justify-between bg-purple-50 rounded-lg p-3">
                                                        <div class="flex items-center gap-2">
                                                            <div class="w-6 h-6 rounded-full bg-purple-100 flex items-center justify-center">
                                                                <span class="text-xs font-semibold text-purple-600">A</span>
                                                            </div>
                                                            <div>
                                                                <p class="text-sm font-medium text-gray-900">{{ $mapping->aggregator->name }}</p>
                                                                <p class="text-xs text-gray-500">Aggregator</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Actions -->
                                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                                    <div class="flex space-x-2">
                                                        <button wire:click="toggleStatus({{ $mapping->id }})" class="p-2 text-gray-400 hover:text-emerald-600 transition-colors rounded-lg hover:bg-emerald-50">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                            </svg>
                                                        </button>
                                                        <button wire:click="openModal({{ $mapping->id }})" class="p-2 text-gray-400 hover:text-emerald-600 transition-colors rounded-lg hover:bg-emerald-50">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    
                                                    <button wire:click="confirmDelete({{ $mapping->id }})" class="p-2 text-gray-400 hover:text-red-600 transition-colors rounded-lg hover:bg-red-50">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-span-full text-center py-12">
                                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                <h3 class="text-lg font-medium text-gray-900 mb-2">No service mappings found</h3>
                                                <p class="text-gray-500 mb-4">Get started by creating your first service mapping.</p>
                                                <button wire:click="selectedMenu(2)" class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 py-2 px-4 text-sm font-medium text-white hover:bg-emerald-700 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    </svg>
                                                    Add First Mapping
                                                </button>
                                            </div>
                                        @endforelse
                                    </div>

                                    <!-- Pagination -->
                                    @if($mappings->hasPages())
                                        <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-100">
                                            <div class="text-sm text-gray-600">
                                                Showing {{ $mappings->firstItem() }} to {{ $mappings->lastItem() }} of {{ $mappings->total() }} results
                                            </div>
                                            <div>
                                                {{ $mappings->links() }}
                                            </div>
                                        </div>
                                    @endif
                                    @break
                                    
                                @case(2)
                                    <!-- New Service Mapping Form -->
                                    <div class="max-w-4xl">
                                        <form wire:submit.prevent="save" class="space-y-8">
                                            <!-- Basic Information Section -->
                                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                                                <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    Mapping Information
                                                </h3>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                    <div>
                                                        <label class="block text-sm font-medium text-blue-900 mb-2">Mapping Name *</label>
                                                        <input wire:model="name" type="text" placeholder="Enter mapping name" class="w-full rounded-lg border border-blue-200 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                        @error('name') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-blue-900 mb-2">Priority *</label>
                                                        <input wire:model="priority" type="number" min="1" placeholder="1" class="w-full rounded-lg border border-blue-200 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                        @error('priority') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="block text-sm font-medium text-blue-900 mb-2">Description</label>
                                                        <textarea wire:model="description" rows="3" placeholder="Enter mapping description" class="w-full rounded-lg border border-blue-200 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                                                        @error('description') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Connection Configuration Section -->
                                            <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl p-6 border border-emerald-200">
                                                <h3 class="text-lg font-semibold text-emerald-900 mb-4 flex items-center">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    Connection Configuration
                                                </h3>
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                                    <div>
                                                        <label class="block text-sm font-medium text-emerald-900 mb-2">Client *</label>
                                                        <select wire:model="client_id" class="w-full rounded-lg border border-emerald-200 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                                            <option value="">Select Client</option>
                                                            @foreach($clients as $client)
                                                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('client_id') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-emerald-900 mb-2">Service *</label>
                                                        <select wire:model="service_id" class="w-full rounded-lg border border-emerald-200 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                                            <option value="">Select Service</option>
                                                            @foreach($services as $service)
                                                                <option value="{{ $service->id }}">{{ $service->name }} ({{ $service->code }})</option>
                                                            @endforeach
                                                        </select>
                                                        @error('service_id') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-emerald-900 mb-2">Aggregator *</label>
                                                        <select wire:model="aggregator_id" class="w-full rounded-lg border border-emerald-200 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                                            <option value="">Select Aggregator</option>
                                                            @foreach($aggregators as $aggregator)
                                                                <option value="{{ $aggregator->id }}">{{ $aggregator->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('aggregator_id') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Status Section -->
                                            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-6 border border-amber-200">
                                                <h3 class="text-lg font-semibold text-amber-900 mb-4 flex items-center">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    Mapping Status
                                                </h3>
                                                <div class="flex items-center space-x-3">
                                                    <label class="relative inline-flex cursor-pointer items-center">
                                                        <input wire:model="status" type="checkbox" class="peer sr-only">
                                                        <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-emerald-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300"></div>
                                                        <span class="ml-3 text-sm font-medium text-amber-900">Enable Mapping</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Submit Buttons -->
                                            <div class="flex items-center justify-end space-x-4 pt-6">
                                                <button type="button" wire:click="selectedMenu(1)" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                                                    Cancel
                                                </button>
                                                <button type="submit" class="px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium">
                                                    {{ $editingMapping ? 'Update Mapping' : 'Create Mapping' }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    @break
                                    
                                @case(5)
                                    <!-- Connection Matrix Section -->
                                    <div class="space-y-6">
                                        <!-- Matrix Overview -->
                                        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-6 border border-indigo-200">
                                            <h3 class="text-lg font-semibold text-indigo-900 mb-4 flex items-center">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                                </svg>
                                                Service Connection Matrix
                                            </h3>
                                            <p class="text-indigo-700 mb-4">Visual overview of all client-service-aggregator connections</p>
                                            
                                            <!-- Connection Stats -->
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div class="bg-white rounded-lg p-4">
                                                    <div class="flex items-center justify-between">
                                                        <div>
                                                            <p class="text-sm font-medium text-gray-600">Total Connections</p>
                                                            <p class="text-2xl font-bold text-gray-900">{{ $mappings->count() }}</p>
                                                        </div>
                                                        <div class="p-2 bg-indigo-100 rounded-lg">
                                                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="bg-white rounded-lg p-4">
                                                    <div class="flex items-center justify-between">
                                                        <div>
                                                            <p class="text-sm font-medium text-gray-600">Active Paths</p>
                                                            <p class="text-2xl font-bold text-emerald-600">{{ $mappings->where('status', true)->count() }}</p>
                                                        </div>
                                                        <div class="p-2 bg-emerald-100 rounded-lg">
                                                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="bg-white rounded-lg p-4">
                                                    <div class="flex items-center justify-between">
                                                        <div>
                                                            <p class="text-sm font-medium text-gray-600">Inactive Paths</p>
                                                            <p class="text-2xl font-bold text-red-600">{{ $mappings->where('status', false)->count() }}</p>
                                                        </div>
                                                        <div class="p-2 bg-red-100 rounded-lg">
                                                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Visual Matrix -->
                                        <div class="bg-white rounded-xl p-6 border border-gray-200">
                                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Connection Flow Diagram</h3>
                                            <div class="overflow-x-auto">
                                                <div class="min-w-[800px] p-4">
                                                    <!-- Diagram placeholder -->
                                                    <div class="flex items-center justify-center h-64 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                                                        <div class="text-center">
                                                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                                            </svg>
                                                            <p class="text-gray-600">Interactive connection matrix visualization</p>
                                                            <p class="text-sm text-gray-500 mt-2">Coming soon - Visual representation of all service mappings</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @break
                                    
                                @case(6)
                                    <!-- Settings Section -->
                                    <div class="space-y-6">
                                        <!-- Global Settings -->
                                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200">
                                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                </svg>
                                                Global Mapping Settings
                                            </h3>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <div class="space-y-4">
                                                    <div class="flex items-center justify-between">
                                                        <div>
                                                            <label class="text-sm font-medium text-gray-900">Auto-Routing</label>
                                                            <p class="text-xs text-gray-500">Automatically route to best aggregator</p>
                                                        </div>
                                                        <input type="checkbox" class="toggle">
                                                    </div>
                                                    <div class="flex items-center justify-between">
                                                        <div>
                                                            <label class="text-sm font-medium text-gray-900">Priority Enforcement</label>
                                                            <p class="text-xs text-gray-500">Respect mapping priority order</p>
                                                        </div>
                                                        <input type="checkbox" checked class="toggle">
                                                    </div>
                                                    <div class="flex items-center justify-between">
                                                        <div>
                                                            <label class="text-sm font-medium text-gray-900">Load Balancing</label>
                                                            <p class="text-xs text-gray-500">Distribute load across mappings</p>
                                                        </div>
                                                        <input type="checkbox" class="toggle">
                                                    </div>
                                                </div>
                                                <div class="space-y-4">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-900 mb-2">Default Priority</label>
                                                        <input type="number" value="1" class="w-full rounded-lg border border-gray-300 py-2 px-3 text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-900 mb-2">Connection Timeout</label>
                                                        <input type="number" value="30" class="w-full rounded-lg border border-gray-300 py-2 px-3 text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-900 mb-2">Retry Strategy</label>
                                                        <select class="w-full rounded-lg border border-gray-300 py-2 px-3 text-sm">
                                                            <option>Failover</option>
                                                            <option>Round Robin</option>
                                                            <option>Priority Based</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex justify-end mt-6">
                                                <button class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                                                    Save Settings
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @break
                                    
                                @default
                                    <!-- Default View -->
                                    <div class="text-center py-12">
                                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">Welcome to Service Mappings</h3>
                                        <p class="text-gray-600">Select a section from the sidebar to get started</p>
                                    </div>
                            @endswitch
                        </div>

                        <!-- Loading State -->
                        <div wire:loading wire:target="selectedMenu" class="min-h-[400px] flex items-center justify-center">
                            <div class="text-center">
                                <svg class="w-8 h-8 animate-spin text-emerald-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="text-gray-600">Loading...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden outline-none focus:outline-none">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
        
        <div class="relative mx-auto my-6 w-full max-w-4xl">
            <div class="relative flex w-full flex-col rounded-lg border-0 bg-white shadow-lg outline-none focus:outline-none">
                <!-- Modal Header -->
                <div class="flex items-start justify-between rounded-t border-b border-gray-200 p-6">
                    <h3 class="text-xl font-semibold text-gray-900">
                        {{ $editingMapping ? 'Edit Service Mapping' : 'Add New Service Mapping' }}
                    </h3>
                    <button wire:click="closeModal" class="float-right ml-auto border-0 bg-transparent p-1 text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Modal Body -->
                <div class="relative flex-auto p-6">
                    <form wire:submit.prevent="save" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Basic Information -->
                            <div class="space-y-4">
                                <h4 class="text-lg font-medium text-gray-900">Basic Information</h4>
                                
                                <div>
                                    <label class="mb-2.5 block text-gray-900 font-medium">Mapping Name *</label>
                                    <input wire:model="name" type="text" placeholder="Enter mapping name" class="w-full rounded-lg border border-gray-300 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                    @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="mb-2.5 block text-gray-900 font-medium">Priority *</label>
                                    <input wire:model="priority" type="number" min="1" placeholder="1" class="w-full rounded-lg border border-gray-300 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                    @error('priority') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="mb-2.5 block text-gray-900 font-medium">Description</label>
                                    <textarea wire:model="description" rows="3" placeholder="Enter description" class="w-full rounded-lg border border-gray-300 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"></textarea>
                                    @error('description') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <!-- Connection Configuration -->
                            <div class="space-y-4">
                                <h4 class="text-lg font-medium text-gray-900">Connection Configuration</h4>
                                
                                <div>
                                    <label class="mb-2.5 block text-gray-900 font-medium">Client *</label>
                                    <select wire:model="client_id" class="w-full rounded-lg border border-gray-300 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                        <option value="">Select Client</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('client_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="mb-2.5 block text-gray-900 font-medium">Service *</label>
                                    <select wire:model="service_id" class="w-full rounded-lg border border-gray-300 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                        <option value="">Select Service</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}">{{ $service->name }} ({{ $service->code }})</option>
                                        @endforeach
                                    </select>
                                    @error('service_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="mb-2.5 block text-gray-900 font-medium">Aggregator *</label>
                                    <select wire:model="aggregator_id" class="w-full rounded-lg border border-gray-300 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                        <option value="">Select Aggregator</option>
                                        @foreach($aggregators as $aggregator)
                                            <option value="{{ $aggregator->id }}">{{ $aggregator->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('aggregator_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Status -->
                        <div class="flex items-center space-x-3">
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input wire:model="status" type="checkbox" class="peer sr-only">
                                <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-emerald-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300"></div>
                                <span class="ml-3 text-sm font-medium text-gray-900">Active Status</span>
                            </label>
                        </div>
                    </form>
                </div>
                
                <!-- Modal Footer -->
                <div class="flex items-center justify-end rounded-b border-t border-gray-200 p-6">
                    <button wire:click="closeModal" class="mr-3 rounded-lg border border-gray-300 bg-white py-2 px-6 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="save" class="rounded-lg bg-emerald-600 py-2 px-6 text-sm font-medium text-white hover:bg-emerald-700 transition-colors">
                        {{ $editingMapping ? 'Update Mapping' : 'Create Mapping' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Delete Confirmation Modal -->
@if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden outline-none focus:outline-none">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
        
        <div class="relative mx-auto my-6 w-full max-w-md">
            <div class="relative flex w-full flex-col rounded-lg bg-white shadow-lg outline-none focus:outline-none">
                <div class="flex items-start justify-between rounded-t border-b border-gray-200 p-6">
                    <h3 class="text-xl font-semibold text-gray-900">
                        Confirm Delete
                    </h3>
                    <button wire:click="closeDeleteModal" class="float-right ml-auto border-0 bg-transparent p-1 text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="relative flex-auto p-6">
                    <p class="text-sm text-gray-600">
                        Are you sure you want to delete this service mapping? This action cannot be undone.
                    </p>
                </div>
                
                <div class="flex items-center justify-end rounded-b border-t border-gray-200 p-6">
                    <button wire:click="closeDeleteModal" class="mr-3 rounded-lg border border-gray-300 bg-white py-2 px-6 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="deleteMapping" class="rounded-lg bg-red-600 py-2 px-6 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                        Delete Mapping
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif