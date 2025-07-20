<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-purple-900 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Services Management</h1>
                        <p class="text-gray-600 mt-1">Configure, monitor, and manage API services integration</p>
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
                                <p class="text-sm font-medium text-gray-500">Active Services</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $services->where('status', true)->count() }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Endpoints</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $services->count() }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Aggregators</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $aggregators->count() }}</p>
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
                            placeholder="Search services, endpoints, or aggregators..."
                            class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white focus:bg-white"
                            aria-label="Search services"
                        />
                    </div>
                </div>

                <!-- Navigation Menu -->
                <div class="p-4">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 px-2">Navigation</h3>
                    
                    @php
                        $service_sections = [
                            [
                                'id' => 1, 
                                'label' => 'All Services', 
                                'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
                                'description' => 'View all services'
                            ],
                            [
                                'id' => 2, 
                                'label' => 'Add New Service', 
                                'icon' => 'M12 6v6m0 0v6m0-6h6m-6 0H6',
                                'description' => 'Create new service'
                            ],
                            [
                                'id' => 3, 
                                'label' => 'Active Services', 
                                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                'description' => 'Running services'
                            ],
                            [
                                'id' => 4, 
                                'label' => 'Inactive Services', 
                                'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
                                'description' => 'Disabled services'
                            ],
                            [
                                'id' => 5, 
                                'label' => 'API Testing', 
                                'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
                                'description' => 'Test endpoints'
                            ],
                            [
                                'id' => 6, 
                                'label' => 'Settings', 
                                'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                                'description' => 'Configure settings'
                            ],
                        ];
                    @endphp

                    <nav class="space-y-2">
                        @foreach ($service_sections as $section)
                            @php
                                $count = 0;
                                if ($section['id'] == 3) {
                                    $count = $services->where('status', true)->count();
                                } elseif ($section['id'] == 4) {
                                    $count = $services->where('status', false)->count();
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
                                        bg-purple-900 text-white shadow-lg 
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
                            New Service
                        </button>
                        <button wire:click="selectedMenu(5)" class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Test API
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
                                        @case(1) All Services @break
                                        @case(2) Add New Service @break
                                        @case(3) Active Services @break
                                        @case(4) Inactive Services @break
                                        @case(5) API Testing @break
                                        @case(6) Settings @break
                                        @default All Services
                                    @endswitch
                                </h2>
                                <p class="text-gray-600 mt-1">
                                    @switch($this->selectedMenuItem)
                                        @case(1) Manage all your API service endpoints @break
                                        @case(2) Configure a new service endpoint @break
                                        @case(3) Monitor running service endpoints @break
                                        @case(4) Review disabled service endpoints @break
                                        @case(5) Test API endpoints and validate responses @break
                                        @case(6) Configure global service settings @break
                                        @default Manage all your API service endpoints
                                    @endswitch
                                </p>
                            </div>
                            
                            <!-- Breadcrumb -->
                            <nav class="flex" aria-label="Breadcrumb">
                                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                                    <li class="inline-flex items-center">
                                        <a href="#" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-purple-600">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                            </svg>
                                            Services
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
                                                    @case(5) Testing @break
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
                                    <!-- Services List -->
                                    @php
                                        $filteredServices = $services;
                                        if ($this->selectedMenuItem == 3) {
                                            $filteredServices = $services->where('status', true);
                                        } elseif ($this->selectedMenuItem == 4) {
                                            $filteredServices = $services->where('status', false);
                                        }
                                    @endphp

                                    <!-- Filters -->
                                    <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                                            <select wire:model.live="statusFilter" class="rounded-lg border border-gray-200 bg-white py-2 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                                <option value="">All Status</option>
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                            
                                            <select wire:model.live="aggregatorFilter" class="rounded-lg border border-gray-200 bg-white py-2 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                                <option value="">All Aggregators</option>
                                                @foreach($aggregators as $aggregator)
                                                    <option value="{{ $aggregator->id }}">{{ $aggregator->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div class="flex items-center gap-2 text-sm text-gray-500">
                                            <span>Total: {{ $filteredServices->count() }} services</span>
                                        </div>
                                    </div>

                                    <!-- Services Grid -->
                                    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                                        @forelse ($filteredServices as $service)
                                            <div class="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-all duration-200 group">
                                                <!-- Service Header -->
                                                <div class="flex items-start justify-between mb-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $service->status ? 'bg-green-100' : 'bg-red-100' }}">
                                                            <svg class="w-6 h-6 {{ $service->status ? 'text-green-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <h5 class="font-semibold text-gray-900 group-hover:text-purple-600 transition-colors">
                                                                {{ $service->name }}
                                                            </h5>
                                                            <p class="text-sm text-gray-500">{{ $service->code }}</p>
                                                        </div>
                                                    </div>
                                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $service->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                        {{ $service->status ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </div>

                                                <!-- Service Details -->
                                                <div class="space-y-3 mb-4">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-sm text-gray-500">Aggregator:</span>
                                                        <div class="flex items-center gap-2">
                                                            <div class="w-5 h-5 rounded-full bg-blue-100 flex items-center justify-center">
                                                                <span class="text-xs font-semibold text-blue-600">{{ strtoupper(substr($service->aggregator->name, 0, 1)) }}</span>
                                                            </div>
                                                            <span class="text-sm font-medium text-gray-900">{{ $service->aggregator->name }}</span>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-sm text-gray-500">Method:</span>
                                                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $service->method === 'GET' ? 'bg-green-100 text-green-800' : ($service->method === 'POST' ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800') }}">
                                                            {{ $service->method }}
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-sm text-gray-500">Endpoint:</span>
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-sm text-gray-700 font-mono">{{ Str::limit($service->endpoint, 20) }}</span>
                                                            <button onclick="copyToClipboard('{{ $service->endpoint }}')" class="text-purple-600 hover:text-purple-800 transition-colors">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Actions -->
                                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                                    <div class="flex space-x-2">
                                                        <button wire:click="toggleStatus({{ $service->id }})" class="p-2 text-gray-400 hover:text-purple-600 transition-colors rounded-lg hover:bg-purple-50">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                            </svg>
                                                        </button>
                                                        <button wire:click="openModal({{ $service->id }})" class="p-2 text-gray-400 hover:text-purple-600 transition-colors rounded-lg hover:bg-purple-50">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    
                                                    <button wire:click="confirmDelete({{ $service->id }})" class="p-2 text-gray-400 hover:text-red-600 transition-colors rounded-lg hover:bg-red-50">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-span-full text-center py-12">
                                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                                </svg>
                                                <h3 class="text-lg font-medium text-gray-900 mb-2">No services found</h3>
                                                <p class="text-gray-500 mb-4">Get started by creating your first service endpoint.</p>
                                                <button wire:click="selectedMenu(2)" class="inline-flex items-center justify-center gap-2 rounded-lg bg-purple-600 py-2 px-4 text-sm font-medium text-white hover:bg-purple-700 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    </svg>
                                                    Add First Service
                                                </button>
                                            </div>
                                        @endforelse
                                    </div>
                                    @break
                                    
                                @case(2)
                                    <!-- New Service Form -->
                                    <div class="max-w-4xl">
                                        <form wire:submit.prevent="save" class="space-y-8">
                                            <!-- Basic Information Section -->
                                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                                                <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    Basic Information
                                                </h3>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                    <div>
                                                        <label class="block text-sm font-medium text-blue-900 mb-2">Service Name *</label>
                                                        <input wire:model="name" type="text" placeholder="Enter service name" class="w-full rounded-lg border border-blue-200 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                        @error('name') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-blue-900 mb-2">Service Code *</label>
                                                        <input wire:model="code" type="text" placeholder="MONEY_COLLECTION" class="w-full rounded-lg border border-blue-200 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                        @error('code') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="block text-sm font-medium text-blue-900 mb-2">Description</label>
                                                        <textarea wire:model="description" rows="3" placeholder="Enter service description" class="w-full rounded-lg border border-blue-200 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                                                        @error('description') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- API Configuration Section -->
                                            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                                                <h3 class="text-lg font-semibold text-green-900 mb-4 flex items-center">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                    </svg>
                                                    API Configuration
                                                </h3>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                    <div>
                                                        <label class="block text-sm font-medium text-green-900 mb-2">Aggregator *</label>
                                                        <select wire:model="aggregator_id" class="w-full rounded-lg border border-green-200 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                                            <option value="">Select Aggregator</option>
                                                            @foreach($aggregators as $aggregator)
                                                                <option value="{{ $aggregator->id }}">{{ $aggregator->name }} ({{ $aggregator->code }})</option>
                                                            @endforeach
                                                        </select>
                                                        @error('aggregator_id') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-green-900 mb-2">Endpoint *</label>
                                                        <input wire:model="endpoint" type="text" placeholder="/MONEY_COLLECTION" class="w-full rounded-lg border border-green-200 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                                        @error('endpoint') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-green-900 mb-2">HTTP Method *</label>
                                                        <select wire:model="method" class="w-full rounded-lg border border-green-200 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                                            <option value="GET">GET</option>
                                                            <option value="POST">POST</option>
                                                            <option value="PUT">PUT</option>
                                                            <option value="PATCH">PATCH</option>
                                                            <option value="DELETE">DELETE</option>
                                                        </select>
                                                        @error('method') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-green-900 mb-2">Request Format *</label>
                                                        <select wire:model="request_format" class="w-full rounded-lg border border-green-200 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                                            <option value="json">JSON</option>
                                                            <option value="xml">XML</option>
                                                            <option value="form">Form Data</option>
                                                        </select>
                                                        @error('request_format') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="block text-sm font-medium text-green-900 mb-2">Response Format *</label>
                                                        <select wire:model="response_format" class="w-full rounded-lg border border-green-200 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                                            <option value="json">JSON</option>
                                                            <option value="xml">XML</option>
                                                        </select>
                                                        @error('response_format') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Performance Settings Section -->
                                            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
                                                <h3 class="text-lg font-semibold text-purple-900 mb-4 flex items-center">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                    </svg>
                                                    Performance Settings
                                                </h3>
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                                    <div>
                                                        <label class="block text-sm font-medium text-purple-900 mb-2">Rate Limit (req/min) *</label>
                                                        <input wire:model="rate_limit" type="number" min="1" placeholder="100" class="w-full rounded-lg border border-purple-200 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                                        @error('rate_limit') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-purple-900 mb-2">Timeout (seconds) *</label>
                                                        <input wire:model="timeout" type="number" min="1" max="300" placeholder="30" class="w-full rounded-lg border border-purple-200 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                                        @error('timeout') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-purple-900 mb-2">Retry Attempts *</label>
                                                        <input wire:model="retry_attempts" type="number" min="0" max="10" placeholder="3" class="w-full rounded-lg border border-purple-200 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                                        @error('retry_attempts') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Status Section -->
                                            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-6 border border-amber-200">
                                                <h3 class="text-lg font-semibold text-amber-900 mb-4 flex items-center">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    Service Status
                                                </h3>
                                                <div class="flex items-center space-x-3">
                                                    <label class="relative inline-flex cursor-pointer items-center">
                                                        <input wire:model="status" type="checkbox" class="peer sr-only">
                                                        <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-purple-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300"></div>
                                                        <span class="ml-3 text-sm font-medium text-amber-900">Enable Service</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Submit Buttons -->
                                            <div class="flex items-center justify-end space-x-4 pt-6">
                                                <button type="button" wire:click="selectedMenu(1)" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                                                    Cancel
                                                </button>
                                                <button type="submit" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium">
                                                    {{ $editingService ? 'Update Service' : 'Create Service' }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    @break
                                    
                                @case(5)
                                    <!-- API Testing Section -->
                                    <div class="space-y-6">
                                        <!-- Test Configuration -->
                                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                                            <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                </svg>
                                                API Testing Lab
                                            </h3>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <div>
                                                    <label class="block text-sm font-medium text-blue-900 mb-2">Select Service</label>
                                                    <select class="w-full rounded-lg border border-blue-200 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                        <option>Select a service to test</option>
                                                        @foreach($services as $service)
                                                            <option value="{{ $service->id }}">{{ $service->name }} ({{ $service->method }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-blue-900 mb-2">Test Environment</label>
                                                    <select class="w-full rounded-lg border border-blue-200 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                        <option>Sandbox</option>
                                                        <option>Staging</option>
                                                        <option>Production</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mt-6">
                                                <label class="block text-sm font-medium text-blue-900 mb-2">Test Payload (JSON)</label>
                                                <textarea rows="6" placeholder='{"customer_phone": "255712345678", "amount": 1000}' class="w-full rounded-lg border border-blue-200 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono"></textarea>
                                            </div>
                                            <div class="flex items-center justify-end mt-6">
                                                <button class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                                    Send Test Request
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Test Results -->
                                        <div class="bg-white rounded-xl p-6 border border-gray-200">
                                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Test Results</h3>
                                            <div class="bg-gray-50 rounded-lg p-4 min-h-[200px]">
                                                <p class="text-gray-500 text-center mt-16">Run a test to see results here</p>
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
                                                Global Service Settings
                                            </h3>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <div class="space-y-4">
                                                    <div class="flex items-center justify-between">
                                                        <div>
                                                            <label class="text-sm font-medium text-gray-900">Debug Mode</label>
                                                            <p class="text-xs text-gray-500">Enable detailed logging</p>
                                                        </div>
                                                        <input type="checkbox" class="toggle">
                                                    </div>
                                                    <div class="flex items-center justify-between">
                                                        <div>
                                                            <label class="text-sm font-medium text-gray-900">Auto Retry</label>
                                                            <p class="text-xs text-gray-500">Automatic request retry</p>
                                                        </div>
                                                        <input type="checkbox" checked class="toggle">
                                                    </div>
                                                    <div class="flex items-center justify-between">
                                                        <div>
                                                            <label class="text-sm font-medium text-gray-900">Rate Limiting</label>
                                                            <p class="text-xs text-gray-500">Global rate limiting</p>
                                                        </div>
                                                        <input type="checkbox" checked class="toggle">
                                                    </div>
                                                </div>
                                                <div class="space-y-4">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-900 mb-2">Default Timeout</label>
                                                        <input type="number" value="30" class="w-full rounded-lg border border-gray-300 py-2 px-3 text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-900 mb-2">Max Retries</label>
                                                        <input type="number" value="3" class="w-full rounded-lg border border-gray-300 py-2 px-3 text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-900 mb-2">Log Level</label>
                                                        <select class="w-full rounded-lg border border-gray-300 py-2 px-3 text-sm">
                                                            <option>DEBUG</option>
                                                            <option>INFO</option>
                                                            <option>WARNING</option>
                                                            <option>ERROR</option>
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">Welcome to Services Management</h3>
                                        <p class="text-gray-600">Select a section from the sidebar to get started</p>
                                    </div>
                            @endswitch
                        </div>

                        <!-- Loading State -->
                        <div wire:loading wire:target="selectedMenu" class="min-h-[400px] flex items-center justify-center">
                            <div class="text-center">
                                <svg class="w-8 h-8 animate-spin text-purple-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
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
                        {{ $editingService ? 'Edit Service' : 'Add New Service' }}
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
                                    <label class="mb-2.5 block text-gray-900 font-medium">Service Name *</label>
                                    <input wire:model="name" type="text" placeholder="Enter service name" class="w-full rounded-lg border border-gray-300 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="mb-2.5 block text-gray-900 font-medium">Service Code *</label>
                                    <input wire:model="code" type="text" placeholder="Enter unique code" class="w-full rounded-lg border border-gray-300 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    @error('code') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="mb-2.5 block text-gray-900 font-medium">Description</label>
                                    <textarea wire:model="description" rows="3" placeholder="Enter description" class="w-full rounded-lg border border-gray-300 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                                    @error('description') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <!-- API Configuration -->
                            <div class="space-y-4">
                                <h4 class="text-lg font-medium text-gray-900">API Configuration</h4>
                                
                                <div>
                                    <label class="mb-2.5 block text-gray-900 font-medium">Aggregator *</label>
                                    <select wire:model="aggregator_id" class="w-full rounded-lg border border-gray-300 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                        <option value="">Select Aggregator</option>
                                        @foreach($aggregators as $aggregator)
                                            <option value="{{ $aggregator->id }}">{{ $aggregator->name }} ({{ $aggregator->code }})</option>
                                        @endforeach
                                    </select>
                                    @error('aggregator_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="mb-2.5 block text-gray-900 font-medium">Endpoint *</label>
                                    <input wire:model="endpoint" type="text" placeholder="/api/service" class="w-full rounded-lg border border-gray-300 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    @error('endpoint') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="mb-2.5 block text-gray-900 font-medium">HTTP Method *</label>
                                        <select wire:model="method" class="w-full rounded-lg border border-gray-300 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                            <option value="GET">GET</option>
                                            <option value="POST">POST</option>
                                            <option value="PUT">PUT</option>
                                            <option value="PATCH">PATCH</option>
                                            <option value="DELETE">DELETE</option>
                                        </select>
                                        @error('method') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="mb-2.5 block text-gray-900 font-medium">Request Format *</label>
                                        <select wire:model="request_format" class="w-full rounded-lg border border-gray-300 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                            <option value="json">JSON</option>
                                            <option value="xml">XML</option>
                                            <option value="form">Form Data</option>
                                        </select>
                                        @error('request_format') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="mb-2.5 block text-gray-900 font-medium">Response Format *</label>
                                    <select wire:model="response_format" class="w-full rounded-lg border border-gray-300 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                        <option value="json">JSON</option>
                                        <option value="xml">XML</option>
                                    </select>
                                    @error('response_format') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Performance Settings -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-medium text-gray-900">Performance Settings</h4>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <label class="mb-2.5 block text-gray-900 font-medium">Rate Limit *</label>
                                    <input wire:model="rate_limit" type="number" min="1" placeholder="100" class="w-full rounded-lg border border-gray-300 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    @error('rate_limit') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="mb-2.5 block text-gray-900 font-medium">Timeout (seconds) *</label>
                                    <input wire:model="timeout" type="number" min="1" max="300" placeholder="30" class="w-full rounded-lg border border-gray-300 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    @error('timeout') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="mb-2.5 block text-gray-900 font-medium">Retry Attempts *</label>
                                    <input wire:model="retry_attempts" type="number" min="0" max="10" placeholder="3" class="w-full rounded-lg border border-gray-300 bg-white py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    @error('retry_attempts') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Status -->
                        <div class="flex items-center space-x-3">
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input wire:model="status" type="checkbox" class="peer sr-only">
                                <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-purple-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300"></div>
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
                    <button wire:click="save" class="rounded-lg bg-purple-600 py-2 px-6 text-sm font-medium text-white hover:bg-purple-700 transition-colors">
                        {{ $editingService ? 'Update Service' : 'Create Service' }}
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
                        Are you sure you want to delete this service? This action cannot be undone.
                    </p>
                </div>
                
                <div class="flex items-center justify-end rounded-b border-t border-gray-200 p-6">
                    <button wire:click="closeDeleteModal" class="mr-3 rounded-lg border border-gray-300 bg-white py-2 px-6 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="deleteService" class="rounded-lg bg-red-600 py-2 px-6 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                        Delete Service
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        const button = event.target.closest('button');
        const originalHTML = button.innerHTML;
        button.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
        button.classList.add('text-green-600');
        
        setTimeout(() => {
            button.innerHTML = originalHTML;
            button.classList.remove('text-green-600');
        }, 2000);
    });
}
</script>