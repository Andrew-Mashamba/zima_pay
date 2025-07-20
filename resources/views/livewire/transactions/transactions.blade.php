<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-blue-900 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m9-6a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Transaction Management</h1>
                        <p class="text-gray-600 mt-1">Monitor, manage, and analyze payment transactions</p>
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
                                <p class="text-sm font-medium text-gray-500">Successful</p>
                                <p class="text-lg font-semibold text-gray-900">{{ number_format($this->stats['successful']) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Failed</p>
                                <p class="text-lg font-semibold text-gray-900">{{ number_format($this->stats['failed']) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Pending</p>
                                <p class="text-lg font-semibold text-gray-900">{{ number_format($this->stats['pending']) }}</p>
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
                            wire:model.live.debounce.300ms="search" 
                            placeholder="Search transactions, customers, or IDs..."
                            class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white focus:bg-white"
                            aria-label="Search transactions"
                        />
                    </div>
                </div>

                <!-- Navigation Menu -->
                <div class="p-4">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 px-2">Navigation</h3>
                    
                    @php
                        $transaction_sections = [
                            [
                                'id' => 1, 
                                'label' => 'All Transactions', 
                                'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
                                'description' => 'View all transactions'
                            ],
                            [
                                'id' => 2, 
                                'label' => 'Analytics Dashboard', 
                                'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                                'description' => 'Performance metrics'
                            ],
                            [
                                'id' => 3, 
                                'label' => 'Pending Transactions', 
                                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                                'description' => 'Awaiting processing'
                            ],
                            [
                                'id' => 4, 
                                'label' => 'Successful Transactions', 
                                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                'description' => 'Completed successfully'
                            ],
                            [
                                'id' => 5, 
                                'label' => 'Failed Transactions', 
                                'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
                                'description' => 'Processing failed'
                            ],
                            [
                                'id' => 6, 
                                'label' => 'Risk Management', 
                                'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z',
                                'description' => 'High-risk analysis'
                            ],
                        ];
                    @endphp

                    <nav class="space-y-2">
                        @foreach ($transaction_sections as $section)
                            @php
                                $count = 0;
                                if ($section['id'] == 3) {
                                    $count = $this->stats['pending'];
                                } elseif ($section['id'] == 5) {
                                    $count = $this->stats['failed'];
                                } elseif ($section['id'] == 6) {
                                    $count = $this->stats['high_risk'];
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

                <!-- Filter Section (shown when needed) -->
                @if($selectedMenuItem == 1 || $selectedMenuItem == 2)
                <div class="p-4 border-t border-gray-100">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-2">Filters</h3>
                    <div class="space-y-3">
                        <!-- Client Filter -->
                        <select wire:model.live="clientFilter" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Clients</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>

                        <!-- Service Filter -->
                        <select wire:model.live="serviceFilter" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Services</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                            @endforeach
                        </select>

                        <!-- Aggregator Filter -->
                        <select wire:model.live="aggregatorFilter" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Aggregators</option>
                            @foreach($aggregators as $aggregator)
                                <option value="{{ $aggregator->id }}">{{ $aggregator->name }}</option>
                            @endforeach
                        </select>

                        <!-- Date Range Filters -->
                        @if($selectedMenuItem == 2)
                        <div class="grid grid-cols-2 gap-2">
                            <input 
                                type="date" 
                                wire:model.live="dateFromFilter"
                                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="From Date"
                            >
                            <input 
                                type="date" 
                                wire:model.live="dateToFilter"
                                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="To Date"
                            >
                        </div>

                        <!-- Amount Range -->
                        <div class="grid grid-cols-2 gap-2">
                            <input 
                                type="number" 
                                wire:model.live="amountFromFilter"
                                placeholder="Min Amount"
                                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                            <input 
                                type="number" 
                                wire:model.live="amountToFilter"
                                placeholder="Max Amount"
                                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Quick Actions -->
                <div class="p-4 border-t border-gray-100 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-2">Quick Actions</h3>
                    <div class="space-y-2">
                        <button wire:click="resetFilters" class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Reset Filters
                        </button>
                        <button wire:click="selectedMenu(2)" class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            View Analytics
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
                                    @switch($this->selectedMenuItem)
                                        @case(1) All Transactions @break
                                        @case(2) Analytics Dashboard @break
                                        @case(3) Pending Transactions @break
                                        @case(4) Successful Transactions @break
                                        @case(5) Failed Transactions @break
                                        @case(6) Risk Management @break
                                        @default All Transactions
                                    @endswitch
                                </h2>
                                <p class="text-gray-600 mt-1">
                                    @switch($this->selectedMenuItem)
                                        @case(1) Monitor and manage all payment transactions @break
                                        @case(2) View performance metrics and analytics @break
                                        @case(3) Review transactions awaiting processing @break
                                        @case(4) View completed successful transactions @break
                                        @case(5) Analyze failed transaction patterns @break
                                        @case(6) Manage high-risk and suspicious transactions @break
                                        @default Monitor and manage all payment transactions
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
                                            Transactions
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
                                                    @case(2) Analytics @break
                                                    @case(3) Pending @break
                                                    @case(4) Successful @break
                                                    @case(5) Failed @break
                                                    @case(6) Risk @break
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
                        <!-- Dynamic Content -->
                        <div wire:loading.remove wire:target="selectedMenu" class="min-h-[400px]">
                            @switch($this->selectedMenuItem)
                                @case(2)
                                    <!-- Analytics Dashboard -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                                        <!-- Performance Overview Card -->
                                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                                            <div class="flex items-center justify-between mb-4">
                                                <h3 class="text-lg font-semibold text-blue-900">Performance Overview</h3>
                                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                </svg>
                                            </div>
                                            <div class="space-y-3">
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-blue-700">Success Rate:</span>
                                                    <span class="font-semibold text-blue-900">{{ $this->stats['total'] > 0 ? round(($this->stats['successful'] / $this->stats['total']) * 100, 1) : 0 }}%</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-blue-700">Avg Response Time:</span>
                                                    <span class="font-semibold text-blue-900">{{ $this->stats['avg_response_time'] ? round($this->stats['avg_response_time'] * 1000) . 'ms' : 'N/A' }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-blue-700">Total Volume:</span>
                                                    <span class="font-semibold text-blue-900">{{ number_format($this->stats['total']) }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Financial Summary Card -->
                                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                                            <div class="flex items-center justify-between mb-4">
                                                <h3 class="text-lg font-semibold text-green-900">Financial Summary</h3>
                                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                </svg>
                                            </div>
                                            <div class="space-y-3">
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-green-700">Total Processed:</span>
                                                    <span class="font-semibold text-green-900">TZS {{ number_format($this->stats['total_amount'], 2) }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-green-700">Today's Volume:</span>
                                                    <span class="font-semibold text-green-900">{{ number_format($this->stats['today']) }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-green-700">Reconciled:</span>
                                                    <span class="font-semibold text-green-900">{{ number_format($this->stats['reconciled']) }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Risk Assessment Card -->
                                        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-6 border border-yellow-200">
                                            <div class="flex items-center justify-between mb-4">
                                                <h3 class="text-lg font-semibold text-yellow-900">Risk Assessment</h3>
                                                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                                </svg>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-3xl font-bold text-yellow-900 mb-2">{{ number_format($this->stats['high_risk']) }}</div>
                                                <p class="text-sm text-yellow-700">High-risk transactions</p>
                                                <button wire:click="selectedMenu(6)" class="mt-3 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors text-sm">
                                                    Review All
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Trend Chart Placeholder -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Transaction Trend</h3>
                                        <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                                            <p class="text-gray-500">Chart visualization would go here</p>
                                        </div>
                                    </div>
                                    @break
                                    
                                @default
                                    <!-- Transaction Table -->
                                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client/Service</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @forelse($transactions as $transaction)
                                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                                        <td class="px-6 py-4">
                                                            <div class="text-sm font-medium text-gray-900">{{ $transaction->transaction_id }}</div>
                                                            <div class="text-sm text-gray-500">{{ $transaction->external_transaction_id ?? 'N/A' }}</div>
                                                            @if($transaction->aggregator)
                                                                <div class="text-xs text-gray-400">via {{ $transaction->aggregator->name }}</div>
                                                            @endif
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <div class="text-sm text-gray-900">{{ $transaction->customer_name ?? 'N/A' }}</div>
                                                            <div class="text-sm text-gray-500">{{ $transaction->customer_phone ?? 'N/A' }}</div>
                                                            @if($transaction->mobile_network)
                                                                <div class="text-xs text-gray-400">{{ $transaction->mobile_network }}</div>
                                                            @endif
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            @if($transaction->client)
                                                                <div class="text-sm text-gray-900">{{ $transaction->client->name }}</div>
                                                            @endif
                                                            @if($transaction->service)
                                                                <div class="text-sm text-gray-500">{{ $transaction->service->name }}</div>
                                                            @endif
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <div class="text-sm font-medium text-gray-900">{{ $transaction->formatted_amount ?? 'N/A' }}</div>
                                                            @if($transaction->fee_amount)
                                                                <div class="text-sm text-gray-500">Fee: {{ $transaction->currency }} {{ number_format($transaction->fee_amount, 2) }}</div>
                                                            @endif
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                                                @if($transaction->status == 'success') bg-green-100 text-green-800
                                                                @elseif($transaction->status == 'failed') bg-red-100 text-red-800
                                                                @elseif($transaction->status == 'pending') bg-yellow-100 text-yellow-800
                                                                @else bg-gray-100 text-gray-800 @endif">
                                                                {{ ucfirst($transaction->status) }}
                                                            </span>
                                                            @if($transaction->is_suspicious)
                                                                <div class="mt-1">
                                                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                                                        Flagged
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td class="px-6 py-4 text-sm text-gray-900">
                                                            {{ $transaction->created_at->format('M d, Y H:i') }}
                                                            @if($transaction->total_processing_time)
                                                                <div class="text-xs text-gray-500">{{ round($transaction->total_processing_time * 1000) }}ms</div>
                                                            @endif
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <div class="flex space-x-2">
                                                                <button 
                                                                    wire:click="viewTransaction({{ $transaction->id }})"
                                                                    class="text-blue-600 hover:text-blue-800 text-sm"
                                                                    title="View Details"
                                                                >
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                                    </svg>
                                                                </button>

                                                                @if(!$transaction->is_reconciled)
                                                                <button 
                                                                    wire:click="reconcileTransaction({{ $transaction->id }})"
                                                                    class="text-green-600 hover:text-green-800 text-sm"
                                                                    title="Reconcile"
                                                                >
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                    </svg>
                                                                </button>
                                                                @endif

                                                                <button 
                                                                    wire:click="flagSuspicious({{ $transaction->id }})"
                                                                    class="{{ $transaction->is_suspicious ? 'text-red-600 hover:text-red-800' : 'text-yellow-600 hover:text-yellow-800' }} text-sm"
                                                                    title="{{ $transaction->is_suspicious ? 'Unflag' : 'Flag as Suspicious' }}"
                                                                >
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="7" class="px-6 py-12 text-center">
                                                            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                            </svg>
                                                            <h3 class="text-lg font-medium text-gray-900 mb-2">No transactions found</h3>
                                                            <p class="text-gray-600">Try adjusting your search criteria or filters</p>
                                                        </td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Pagination -->
                                        @if($transactions->hasPages())
                                        <div class="px-6 py-4 border-t border-gray-200">
                                            {{ $transactions->links() }}
                                        </div>
                                        @endif
                                    </div>
                            @endswitch
                        </div>

                        <!-- Loading State -->
                        <div wire:loading wire:target="selectedMenu" class="min-h-[400px] flex items-center justify-center">
                            <div class="text-center">
                                <svg class="w-8 h-8 animate-spin text-blue-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
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

    <!-- Transaction Detail Modal -->
    @if($showViewModal && $viewTransaction)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-black bg-opacity-50" wire:click="closeViewModal"></div>

            <div class="inline-block w-full max-w-4xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl border border-gray-200">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-medium text-gray-900">Transaction Details</h3>
                    <button wire:click="closeViewModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-4 max-h-96 overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Information -->
                        <div class="space-y-4">
                            <h4 class="font-semibold text-gray-900">Basic Information</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Transaction ID:</span>
                                    <span class="text-gray-900 font-medium">{{ $viewTransaction->transaction_id }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">External ID:</span>
                                    <span class="text-gray-900">{{ $viewTransaction->external_transaction_id ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Status:</span>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        @if($viewTransaction->status == 'success') bg-green-100 text-green-800
                                        @elseif($viewTransaction->status == 'failed') bg-red-100 text-red-800
                                        @elseif($viewTransaction->status == 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($viewTransaction->status) }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Amount:</span>
                                    <span class="text-gray-900 font-medium">{{ $viewTransaction->formatted_amount ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Created:</span>
                                    <span class="text-gray-900">{{ $viewTransaction->created_at->format('Y-m-d H:i:s') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Information -->
                        <div class="space-y-4">
                            <h4 class="font-semibold text-gray-900">Customer Information</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Name:</span>
                                    <span class="text-gray-900">{{ $viewTransaction->customer_name ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Phone:</span>
                                    <span class="text-gray-900">{{ $viewTransaction->customer_phone ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Network:</span>
                                    <span class="text-gray-900">{{ $viewTransaction->mobile_network ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Provider:</span>
                                    <span class="text-gray-900">{{ $viewTransaction->network_provider ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Service Information -->
                        <div class="space-y-4">
                            <h4 class="font-semibold text-gray-900">Service Information</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Client:</span>
                                    <span class="text-gray-900">{{ $viewTransaction->client->name ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Service:</span>
                                    <span class="text-gray-900">{{ $viewTransaction->service->name ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Aggregator:</span>
                                    <span class="text-gray-900">{{ $viewTransaction->aggregator->name ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Channel:</span>
                                    <span class="text-gray-900">{{ $viewTransaction->channel ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Performance Metrics -->
                        <div class="space-y-4">
                            <h4 class="font-semibold text-gray-900">Performance</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Response Time:</span>
                                    <span class="text-gray-900">{{ $viewTransaction->total_processing_time ? round($viewTransaction->total_processing_time * 1000) . 'ms' : 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Request Size:</span>
                                    <span class="text-gray-900">{{ $viewTransaction->request_size ? round($viewTransaction->request_size / 1024, 2) . ' KB' : 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Response Size:</span>
                                    <span class="text-gray-900">{{ $viewTransaction->response_size ? round($viewTransaction->response_size / 1024, 2) . ' KB' : 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Retry Count:</span>
                                    <span class="text-gray-900">{{ $viewTransaction->retry_count ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($viewTransaction->error_message)
                    <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                        <h4 class="text-red-900 font-semibold mb-2">Error Information</h4>
                        <p class="text-red-800 text-sm">{{ $viewTransaction->error_message }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="fixed top-4 right-4 z-50">
            <div class="bg-green-500 text-white px-6 py-3 rounded-xl shadow-lg border border-green-400">
                {{ session('message') }}
            </div>
        </div>
    @endif
</div>