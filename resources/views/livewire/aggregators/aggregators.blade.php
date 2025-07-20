<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-blue-900 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Aggregators Management</h1>
                        <p class="text-gray-600 mt-1">Manage mobile money aggregators and their configurations</p>
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
                                <p class="text-sm font-medium text-gray-500">Active Aggregators</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $aggregators->where('status', 1)->count() }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Aggregators</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $aggregators->total() }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m-9 0h10M7 4v10a2 2 0 002 2h6a2 2 0 002-2V4M7 4H5a2 2 0 00-2 2v1M21 7V6a2 2 0 00-2-2h-2"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Services</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $aggregators->sum('services_count') }}</p>
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
                            placeholder="Search aggregators, codes, or contacts..."
                            class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white focus:bg-white"
                            aria-label="Search aggregators"
                        />
                    </div>
                </div>

                <!-- Navigation Menu -->
                <div class="p-4">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 px-2">Navigation</h3>
                    
                    @php
                        $aggregator_sections = [
                            [
                                'id' => 1, 
                                'label' => 'All Aggregators', 
                                'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
                                'description' => 'View all aggregators'
                            ],
                            [
                                'id' => 2, 
                                'label' => 'Add New Aggregator', 
                                'icon' => 'M12 6v6m0 0v6m0-6h6m-6 0H6',
                                'description' => 'Create new aggregator'
                            ],
                            [
                                'id' => 3, 
                                'label' => 'Active Aggregators', 
                                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                'description' => 'View active aggregators only'
                            ],
                            [
                                'id' => 4, 
                                'label' => 'Inactive Aggregators', 
                                'icon' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18 21l-2.636-2.636M6 6l12 12',
                                'description' => 'View inactive aggregators'
                            ],
                            [
                                'id' => 5, 
                                'label' => 'API Monitoring', 
                                'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                                'description' => 'Monitor API performance'
                            ],
                            [
                                'id' => 6, 
                                'label' => 'Settings', 
                                'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                                'description' => 'Aggregator management settings'
                            ],
                        ];
                    @endphp

                    <nav class="space-y-2">
                        @foreach ($aggregator_sections as $section)
                            @php
                                $count = 0;
                                if ($section['id'] == 4) {
                                    $count = $aggregators->where('status', 0)->count();
                                }
                                $isActive = ($this->selectedMenuItem ?? 1) == $section['id'];
                            @endphp

                            <button
                                wire:click="selectedMenu({{ $section['id'] }})"
                                class="relative w-full group transition-all duration-200"
                                aria-label="{{ $section['label'] }}"
                            >
                                <div class="flex items-center p-3 rounded-xl transition-all duration-200
                                    @if ($isActive) 
                                        bg-blue-900 text-white shadow-lg 
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

                                    <!-- Notification Badge -->
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

                <!-- Filters Section -->
                <div class="p-4 border-t border-gray-100">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-2">Filters</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select wire:model.live="statusFilter" 
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">All Status</option>
                                <option value="active">Active Only</option>
                                <option value="inactive">Inactive Only</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="p-4 border-t border-gray-100 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-2">Quick Actions</h3>
                    <div class="space-y-2">
                        <button wire:click="openModal()" class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            New Aggregator
                        </button>
                        <button class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Generate Report
                        </button>
                        <button class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            Export Data
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
                                    @switch($this->selectedMenuItem ?? 1)
                                        @case(1) All Aggregators @break
                                        @case(2) Add New Aggregator @break
                                        @case(3) Active Aggregators @break
                                        @case(4) Inactive Aggregators @break
                                        @case(5) API Monitoring @break
                                        @case(6) Settings @break
                                        @default All Aggregators
                                    @endswitch
                                </h2>
                                <p class="text-gray-600 mt-1">
                                    @switch($this->selectedMenuItem ?? 1)
                                        @case(1) Manage all registered aggregators @break
                                        @case(2) Create and configure new aggregator @break
                                        @case(3) View all active aggregators @break
                                        @case(4) Review inactive or disabled aggregators @break
                                        @case(5) Monitor API performance and health @break
                                        @case(6) Configure aggregator management settings @break
                                        @default Manage all registered aggregators
                                    @endswitch
                                </p>
                            </div>
                            
                            <!-- Breadcrumb -->
                            <nav class="flex" aria-label="Breadcrumb">
                                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                                    <li class="inline-flex items-center">
                                        <a href="#" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                            </svg>
                                            Aggregators
                                        </a>
                                    </li>
                                    <li>
                                        <div class="flex items-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">
                                                @switch($this->selectedMenuItem ?? 1)
                                                    @case(1) All @break
                                                    @case(2) New @break
                                                    @case(3) Active @break
                                                    @case(4) Inactive @break
                                                    @case(5) Monitoring @break
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

                    <!-- Success Message -->
                    @if (session()->has('message'))
                        <div class="mx-8 mt-6">
                            <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-green-800 font-medium">{{ session('message') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Main Content -->
                    <div class="p-8">
                        <!-- Dynamic Content -->
                        <div wire:loading.remove wire:target="selectedMenu" class="min-h-[400px]">
                            @switch($this->selectedMenuItem ?? 1)
                                @case(1)
                                @case(3)
                                @case(4)
                                    <!-- Aggregators Table -->
                                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-700 tracking-wide">
                                                            <div class="flex items-center gap-2">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                                </svg>
                                                                Aggregator Information
                                                            </div>
                                                        </th>
                                                        <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-700 tracking-wide">
                                                            <div class="flex items-center gap-2">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                                                </svg>
                                                                API Endpoint
                                                            </div>
                                                        </th>
                                                        <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-700 tracking-wide">
                                                            <div class="flex items-center gap-2">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m-9 0h10M7 4v10a2 2 0 002 2h6a2 2 0 002-2V4M7 4H5a2 2 0 00-2 2v1M21 7V6a2 2 0 00-2-2h-2"></path>
                                                                </svg>
                                                                Services & Clients
                                                            </div>
                                                        </th>
                                                        <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-700 tracking-wide">
                                                            <div class="flex items-center gap-2">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                </svg>
                                                                Status
                                                            </div>
                                                        </th>
                                                        <th scope="col" class="px-6 py-4 text-right text-sm font-semibold text-gray-700 tracking-wide">
                                                            Actions
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @forelse ($aggregators as $aggregator)
                                                        @if (
                                                            ($this->selectedMenuItem == 1) ||
                                                            ($this->selectedMenuItem == 3 && $aggregator->status) ||
                                                            ($this->selectedMenuItem == 4 && !$aggregator->status)
                                                        )
                                                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                                                <td class="px-6 py-4 whitespace-nowrap">
                                                                    <div class="flex items-center gap-4">
                                                                        <div class="relative">
                                                                            <div class="flex-shrink-0 h-12 w-12 rounded-2xl bg-gradient-to-br from-blue-500 via-purple-500 to-indigo-500 flex items-center justify-center text-white font-bold text-lg shadow-lg">
                                                                                {{ strtoupper(substr($aggregator->name, 0, 2)) }}
                                                                            </div>
                                                                            @if($aggregator->status)
                                                                                <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-gradient-to-r from-emerald-400 to-emerald-500 rounded-full flex items-center justify-center">
                                                                                    <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                                                    </svg>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                        <div class="min-w-0 flex-1">
                                                                            <div class="font-semibold text-slate-900 text-lg">{{ $aggregator->name }}</div>
                                                                            <div class="text-sm text-slate-600 font-mono bg-slate-100 px-2 py-1 rounded-lg inline-block mt-1">{{ $aggregator->code }}</div>
                                                                            @if($aggregator->contact_person)
                                                                                <div class="text-sm text-slate-500 mt-1 flex items-center gap-1">
                                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                                                    </svg>
                                                                                    {{ $aggregator->contact_person }}
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap">
                                                                    <div class="space-y-2">
                                                                        <div class="flex items-center gap-3">
                                                                            <div class="bg-gradient-to-r from-slate-100 to-slate-50 px-3 py-2 rounded-xl border border-slate-200">
                                                                                <span class="text-xs font-mono text-slate-700">{{ Str::limit($aggregator->api_endpoint, 30) }}</span>
                                                                            </div>
                                                                            <button onclick="copyToClipboard('{{ $aggregator->api_endpoint }}')" 
                                                                                class="group/copy p-2 rounded-xl bg-white border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50 transition-all duration-200"
                                                                                title="Copy API Endpoint">
                                                                                <svg class="w-4 h-4 text-slate-400 group-hover/copy:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                                                </svg>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap">
                                                                    <div class="flex items-center gap-4">
                                                                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m-9 0h10M7 4v10a2 2 0 002 2h6a2 2 0 002-2V4M7 4H5a2 2 0 00-2 2v1M21 7V6a2 2 0 00-2-2h-2"></path>
                                                                            </svg>
                                                                            {{ $aggregator->services_count }} Services
                                                                        </div>
                                                                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                                            </svg>
                                                                            {{ $aggregator->clients_count }} Clients
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap">
                                                                    @if($aggregator->status)
                                                                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                                                            Active
                                                                        </div>
                                                                    @else
                                                                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                                            <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                                                            Inactive
                                                                        </div>
                                                                    @endif
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                                    <div class="flex justify-end gap-1">
                                                                        <button wire:click="toggleStatus({{ $aggregator->id }})" 
                                                                            class="p-2.5 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all duration-200"
                                                                            title="{{ $aggregator->status ? 'Deactivate' : 'Activate' }}">
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                                            </svg>
                                                                        </button>
                                                                        <button wire:click="openModal({{ $aggregator->id }})" 
                                                                            class="p-2.5 rounded-xl text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-all duration-200"
                                                                            title="Edit Aggregator">
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                                            </svg>
                                                                        </button>
                                                                        <button wire:click="confirmDelete({{ $aggregator->id }})" 
                                                                            class="p-2.5 rounded-xl text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all duration-200"
                                                                            title="Delete Aggregator">
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                                            </svg>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="py-20 text-center">
                                                                <div class="flex flex-col items-center justify-center max-w-lg mx-auto">
                                                                    <div class="relative mb-8">
                                                                        <div class="p-8 bg-gradient-to-br from-slate-50 to-white rounded-3xl shadow-xl">
                                                                            <svg class="w-16 h-16 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                                            </svg>
                                                                        </div>
                                                                    </div>
                                                                    <h3 class="text-2xl font-bold text-slate-900 mb-3">No aggregators found</h3>
                                                                    <p class="text-slate-600 mb-8 text-lg leading-relaxed">Start managing mobile money aggregators by creating your first integration.</p>
                                                                    <button wire:click="openModal()" 
                                                                        class="group inline-flex items-center gap-3 rounded-2xl bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 py-4 px-8 text-white font-semibold shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105 active:scale-95">
                                                                        <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                                        </svg>
                                                                        Create Your First Aggregator
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Enhanced Pagination -->
                                    @if($aggregators->hasPages())
                                        <div class="flex flex-col sm:flex-row items-center justify-between gap-6 mt-8">
                                            <div class="bg-white rounded-xl px-6 py-4 shadow-sm border border-gray-100">
                                                <div class="text-sm text-slate-600 flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                    </svg>
                                                    Showing <span class="font-semibold text-slate-900">{{ $aggregators->firstItem() }}</span> to <span class="font-semibold text-slate-900">{{ $aggregators->lastItem() }}</span> of <span class="font-semibold text-slate-900">{{ $aggregators->total() }}</span> results
                                                </div>
                                            </div>
                                            <div class="bg-white rounded-xl p-2 shadow-sm border border-gray-100">
                                                {{ $aggregators->links() }}
                                            </div>
                                        </div>
                                    @endif
                                    @break
                                    
                                @case(2)
                                    <!-- Add New Aggregator Form -->
                                    <div class="max-w-4xl">
                                        <form wire:submit.prevent="save">
                                            <div class="space-y-8">
                                                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
                                                    <!-- Basic Information -->
                                                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                                                        <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center gap-2">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                            </svg>
                                                            Basic Information
                                                        </h3>
                                                        
                                                        <div class="space-y-4">
                                                            <div>
                                                                <label class="block text-sm font-medium text-blue-800 mb-2">Aggregator Name *</label>
                                                                <input wire:model="name" type="text" placeholder="Enter aggregator name" 
                                                                    class="w-full rounded-xl border border-blue-200 py-3 px-4 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 shadow-sm bg-white">
                                                                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                                            </div>
                                                            
                                                            <div>
                                                                <label class="block text-sm font-medium text-blue-800 mb-2">Aggregator Code *</label>
                                                                <input wire:model="code" type="text" placeholder="Enter unique code" 
                                                                    class="w-full rounded-xl border border-blue-200 py-3 px-4 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 shadow-sm bg-white">
                                                                @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                                            </div>
                                                            
                                                            <div>
                                                                <label class="block text-sm font-medium text-blue-800 mb-2">Description</label>
                                                                <textarea wire:model="description" rows="3" placeholder="Enter description" 
                                                                    class="w-full rounded-xl border border-blue-200 py-3 px-4 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 shadow-sm bg-white"></textarea>
                                                                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- API Configuration -->
                                                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                                                        <h3 class="text-lg font-semibold text-green-900 mb-4 flex items-center gap-2">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                                            </svg>
                                                            API Configuration
                                                        </h3>
                                                        
                                                        <div class="space-y-4">
                                                            <div>
                                                                <label class="block text-sm font-medium text-green-800 mb-2">API Endpoint *</label>
                                                                <input wire:model="api_endpoint" type="url" placeholder="https://api.aggregator.com" 
                                                                    class="w-full rounded-xl border border-green-200 py-3 px-4 focus:border-green-500 focus:ring-2 focus:ring-green-200 shadow-sm bg-white">
                                                                @error('api_endpoint') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                                            </div>
                                                            
                                                            <div>
                                                                <label class="block text-sm font-medium text-green-800 mb-2">API Key</label>
                                                                <input wire:model="api_key" type="text" placeholder="Enter API key" 
                                                                    class="w-full rounded-xl border border-green-200 py-3 px-4 focus:border-green-500 focus:ring-2 focus:ring-green-200 shadow-sm bg-white">
                                                                @error('api_key') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                                            </div>
                                                            
                                                            <div>
                                                                <label class="block text-sm font-medium text-green-800 mb-2">API Secret</label>
                                                                <input wire:model="api_secret" type="password" placeholder="Enter API secret" 
                                                                    class="w-full rounded-xl border border-green-200 py-3 px-4 focus:border-green-500 focus:ring-2 focus:ring-green-200 shadow-sm bg-white">
                                                                @error('api_secret') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Performance Settings -->
                                                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
                                                    <h3 class="text-lg font-semibold text-purple-900 mb-4 flex items-center gap-2">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                        </svg>
                                                        Performance Settings
                                                    </h3>
                                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                                        <div>
                                                            <label class="block text-sm font-medium text-purple-800 mb-2">Rate Limit *</label>
                                                            <input wire:model="rate_limit" type="number" min="1" placeholder="1000" 
                                                                class="w-full rounded-xl border border-purple-200 py-3 px-4 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 shadow-sm bg-white">
                                                            @error('rate_limit') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                                        </div>
                                                        
                                                        <div>
                                                            <label class="block text-sm font-medium text-purple-800 mb-2">Timeout (seconds) *</label>
                                                            <input wire:model="timeout" type="number" min="1" max="300" placeholder="30" 
                                                                class="w-full rounded-xl border border-purple-200 py-3 px-4 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 shadow-sm bg-white">
                                                            @error('timeout') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                                        </div>
                                                        
                                                        <div>
                                                            <label class="block text-sm font-medium text-purple-800 mb-2">Retry Attempts *</label>
                                                            <input wire:model="retry_attempts" type="number" min="0" max="10" placeholder="3" 
                                                                class="w-full rounded-xl border border-purple-200 py-3 px-4 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 shadow-sm bg-white">
                                                            @error('retry_attempts') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Contact Information -->
                                                <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-6 border border-amber-200">
                                                    <h3 class="text-lg font-semibold text-amber-900 mb-4 flex items-center gap-2">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                        </svg>
                                                        Contact Information
                                                    </h3>
                                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-4">
                                                        <div>
                                                            <label class="block text-sm font-medium text-amber-800 mb-2">Contact Person</label>
                                                            <input wire:model="contact_person" type="text" placeholder="Enter contact person" 
                                                                class="w-full rounded-xl border border-amber-200 py-3 px-4 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 shadow-sm bg-white">
                                                            @error('contact_person') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                                        </div>
                                                        
                                                        <div>
                                                            <label class="block text-sm font-medium text-amber-800 mb-2">Email</label>
                                                            <input wire:model="contact_email" type="email" placeholder="Enter email address" 
                                                                class="w-full rounded-xl border border-amber-200 py-3 px-4 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 shadow-sm bg-white">
                                                            @error('contact_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                                        </div>
                                                        
                                                        <div>
                                                            <label class="block text-sm font-medium text-amber-800 mb-2">Phone</label>
                                                            <input wire:model="contact_phone" type="text" placeholder="Enter phone number" 
                                                                class="w-full rounded-xl border border-amber-200 py-3 px-4 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 shadow-sm bg-white">
                                                            @error('contact_phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                                        </div>
                                                    </div>
                                                    
                                                    <div>
                                                        <label class="block text-sm font-medium text-amber-800 mb-2">Webhook URL</label>
                                                        <input wire:model="webhook_url" type="url" placeholder="https://your-webhook-url.com" 
                                                            class="w-full rounded-xl border border-amber-200 py-3 px-4 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 shadow-sm bg-white">
                                                        @error('webhook_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                                    </div>
                                                </div>
                                                
                                                <!-- Form Actions -->
                                                <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                                                    <button type="button" wire:click="selectedMenu(1)" 
                                                        class="px-6 py-3 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" 
                                                        class="px-6 py-3 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-blue-900 hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                                        {{ $editingAggregator ? 'Update Aggregator' : 'Create Aggregator' }}
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    @break
                                    
                                @case(5)
                                    <!-- API Monitoring Dashboard -->
                                    <div class="space-y-6">
                                        <!-- Monitoring Cards -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                                                <div class="flex items-center justify-between mb-4">
                                                    <h3 class="text-lg font-semibold text-blue-900">API Requests</h3>
                                                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                    </svg>
                                                </div>
                                                <div class="text-3xl font-bold text-blue-900 mb-2">2,845</div>
                                                <p class="text-sm text-blue-700">Today</p>
                                            </div>

                                            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                                                <div class="flex items-center justify-between mb-4">
                                                    <h3 class="text-lg font-semibold text-green-900">Success Rate</h3>
                                                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                                <div class="text-3xl font-bold text-green-900 mb-2">97.8%</div>
                                                <p class="text-sm text-green-700">Last 24 hours</p>
                                            </div>

                                            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-6 border border-yellow-200">
                                                <div class="flex items-center justify-between mb-4">
                                                    <h3 class="text-lg font-semibold text-yellow-900">Avg Response</h3>
                                                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                                <div class="text-3xl font-bold text-yellow-900 mb-2">180ms</div>
                                                <p class="text-sm text-yellow-700">Average time</p>
                                            </div>

                                            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
                                                <div class="flex items-center justify-between mb-4">
                                                    <h3 class="text-lg font-semibold text-purple-900">Active Connections</h3>
                                                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                                    </svg>
                                                </div>
                                                <div class="text-3xl font-bold text-purple-900 mb-2">{{ $aggregators->where('status', 1)->count() }}</div>
                                                <p class="text-sm text-purple-700">Online aggregators</p>
                                            </div>
                                        </div>

                                        <!-- Charts Section -->
                                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                            <div class="bg-white rounded-xl p-6 border border-gray-200">
                                                <h3 class="text-lg font-semibold text-gray-900 mb-4">API Performance Trend</h3>
                                                <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                                                    <p class="text-gray-500">Performance chart would go here</p>
                                                </div>
                                            </div>

                                            <div class="bg-white rounded-xl p-6 border border-gray-200">
                                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Aggregator Health Status</h3>
                                                <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                                                    <p class="text-gray-500">Health status chart would go here</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @break
                                    
                                @case(6)
                                    <!-- Settings -->
                                    <div class="max-w-4xl space-y-6">
                                        <div class="bg-white rounded-xl p-6 border border-gray-200">
                                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Aggregator Management Settings</h3>
                                            <div class="space-y-4">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <h4 class="font-medium text-gray-900">Auto-retry failed requests</h4>
                                                        <p class="text-sm text-gray-600">Automatically retry failed API requests</p>
                                                    </div>
                                                    <button class="relative inline-flex h-6 w-11 items-center rounded-full bg-blue-600 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                                        <span class="sr-only">Enable auto-retry</span>
                                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out translate-x-6"></span>
                                                    </button>
                                                </div>
                                                
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <h4 class="font-medium text-gray-900">Health monitoring</h4>
                                                        <p class="text-sm text-gray-600">Monitor aggregator health and performance</p>
                                                    </div>
                                                    <button class="relative inline-flex h-6 w-11 items-center rounded-full bg-blue-600 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                                        <span class="sr-only">Enable monitoring</span>
                                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out translate-x-6"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @break
                                    
                                @default
                                    <!-- Default View -->
                                    <div class="text-center py-12">
                                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">Welcome to Aggregator Management</h3>
                                        <p class="text-gray-600">Select a section from the sidebar to get started</p>
                                    </div>
                            @endswitch
                        </div>

                        <!-- Loading State -->
                        <div wire:loading wire:target="selectedMenu" class="flex items-center justify-center py-12">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-900"></div>
                            <span class="ml-3 text-gray-600">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Keep existing modals here -->
<!-- Add/Edit Modal -->
@if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 transition-opacity" aria-hidden="true" wire:click="closeModal">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <!-- Modal container -->
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="p-8">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between mb-8">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-gradient-to-r from-blue-500 to-purple-500 rounded-2xl">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">
                                    {{ $editingAggregator ? 'Edit Aggregator' : 'Create New Aggregator' }}
                                </h3>
                                <p class="text-gray-600 mt-1">
                                    {{ $editingAggregator ? 'Update aggregator information and settings' : 'Add a new aggregator to manage mobile money integrations' }}
                                </p>
                            </div>
                        </div>
                        <button wire:click="closeModal" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-all duration-200">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal content -->
                    <form wire:submit.prevent="save">
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <!-- Basic Information -->
                                <div class="space-y-4">
                                    <h4 class="text-lg font-medium text-gray-900 border-b pb-2">Basic Information</h4>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Aggregator Name *</label>
                                        <input wire:model="name" type="text" placeholder="Enter aggregator name" 
                                            class="w-full rounded-lg border border-gray-300 py-2 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Aggregator Code *</label>
                                        <input wire:model="code" type="text" placeholder="Enter unique code" 
                                            class="w-full rounded-lg border border-gray-300 py-2 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                        @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                        <textarea wire:model="description" rows="3" placeholder="Enter description" 
                                            class="w-full rounded-lg border border-gray-300 py-2 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"></textarea>
                                        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                
                                <!-- API Configuration -->
                                <div class="space-y-4">
                                    <h4 class="text-lg font-medium text-gray-900 border-b pb-2">API Configuration</h4>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">API Endpoint *</label>
                                        <input wire:model="api_endpoint" type="url" placeholder="https://api.aggregator.com" 
                                            class="w-full rounded-lg border border-gray-300 py-2 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                        @error('api_endpoint') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                                        <input wire:model="api_key" type="text" placeholder="Enter API key" 
                                            class="w-full rounded-lg border border-gray-300 py-2 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                        @error('api_key') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">API Secret</label>
                                        <input wire:model="api_secret" type="password" placeholder="Enter API secret" 
                                            class="w-full rounded-lg border border-gray-300 py-2 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                        @error('api_secret') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Performance Settings -->
                            <div class="space-y-4">
                                <h4 class="text-lg font-medium text-gray-900 border-b pb-2">Performance Settings</h4>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Rate Limit *</label>
                                        <input wire:model="rate_limit" type="number" min="1" placeholder="1000" 
                                            class="w-full rounded-lg border border-gray-300 py-2 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                        @error('rate_limit') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Timeout (seconds) *</label>
                                        <input wire:model="timeout" type="number" min="1" max="300" placeholder="30" 
                                            class="w-full rounded-lg border border-gray-300 py-2 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                        @error('timeout') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Retry Attempts *</label>
                                        <input wire:model="retry_attempts" type="number" min="0" max="10" placeholder="3" 
                                            class="w-full rounded-lg border border-gray-300 py-2 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                        @error('retry_attempts') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Contact Information -->
                            <div class="space-y-4">
                                <h4 class="text-lg font-medium text-gray-900 border-b pb-2">Contact Information</h4>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                                        <input wire:model="contact_person" type="text" placeholder="Enter contact person" 
                                            class="w-full rounded-lg border border-gray-300 py-2 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                        @error('contact_person') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                        <input wire:model="contact_email" type="email" placeholder="Enter email address" 
                                            class="w-full rounded-lg border border-gray-300 py-2 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                        @error('contact_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                        <input wire:model="contact_phone" type="text" placeholder="Enter phone number" 
                                            class="w-full rounded-lg border border-gray-300 py-2 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                        @error('contact_phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Webhook URL</label>
                                    <input wire:model="webhook_url" type="url" placeholder="https://your-webhook-url.com" 
                                        class="w-full rounded-lg border border-gray-300 py-2 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                    @error('webhook_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Modal footer -->
                        <div class="mt-6 flex justify-end gap-3 border-t pt-4">
                            <button type="button" wire:click="closeModal" 
                                class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                            </button>
                            <button type="submit" 
                                class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ $editingAggregator ? 'Update Aggregator' : 'Create Aggregator' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Delete Confirmation Modal -->
@if($showDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <!-- Modal container -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Aggregator</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Are you sure you want to delete this aggregator? This action cannot be undone.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="deleteAggregator" type="button" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Delete
                    </button>
                    <button wire:click="$set('showDeleteModal', false)" type="button" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show a temporary success message
        const button = event.target.closest('button');
        const originalHTML = button.innerHTML;
        button.innerHTML = '<svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
        setTimeout(() => {
            button.innerHTML = originalHTML;
        }, 2000);
    });
}
</script>