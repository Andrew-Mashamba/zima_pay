<div>
    <!-- Sidebar -->
    <div class="flex w-80 flex-col glass border-r border-white/20 shadow-xl">
        <!-- Logo Section -->
        <div class="flex items-center justify-center h-20 px-6 border-b border-white/10">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-500/25">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">MICROPAY ESB</h1>
                    <p class="text-xs text-gray-500 font-medium">Enterprise Service Bus</p>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <div class="flex-1 px-6 py-8">
            <nav class="space-y-3">
                <!-- Menu Section -->
                <div class="mb-8">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 mb-4">Main Menu</h3>
                    
                    <!-- Dashboard -->
                    <a wire:click="setPage('dashboard')" class="group flex items-center px-4 py-3.5 text-sm font-medium rounded-2xl transition-all duration-300 {{ $page === 'dashboard' ? 'bg-blue-900 text-white shadow-lg shadow-blue-500/25' : 'text-gray-600 hover:bg-white/50 hover:text-gray-900 hover:shadow-md' }}">
                        <div class="flex items-center justify-center w-10 h-10 mr-4 rounded-xl {{ $page === 'dashboard' ? 'bg-white/20' : 'bg-gray-100 group-hover:bg-white' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        Dashboard
                    </a>
                </div>

                <!-- Management Section -->
                <div class="mb-8">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 mb-4">Management</h3>
                    
                    <!-- Clients -->
                    <a wire:click="setPage('clients')" class="group flex items-center px-4 py-3.5 text-sm font-medium rounded-2xl transition-all duration-300 {{ $page === 'clients' ? 'bg-blue-900 text-white shadow-lg shadow-blue-500/25' : 'text-gray-600 hover:bg-white/50 hover:text-gray-900 hover:shadow-md' }}">
                        <div class="flex items-center justify-center w-10 h-10 mr-4 rounded-xl {{ $page === 'clients' ? 'bg-white/20' : 'bg-gray-100 group-hover:bg-white' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        Clients
                    </a>

                    <!-- Aggregators -->
                    <a wire:click="setPage('aggregators')" class="group flex items-center px-4 py-3.5 text-sm font-medium rounded-2xl transition-all duration-300 {{ $page === 'aggregators' ? 'bg-blue-900 text-white shadow-lg shadow-blue-500/25' : 'text-gray-600 hover:bg-white/50 hover:text-gray-900 hover:shadow-md' }}">
                        <div class="flex items-center justify-center w-10 h-10 mr-4 rounded-xl {{ $page === 'aggregators' ? 'bg-white/20' : 'bg-gray-100 group-hover:bg-white' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        Aggregators
                    </a>

                    <!-- Services -->
                    <a wire:click="setPage('services')" class="group flex items-center px-4 py-3.5 text-sm font-medium rounded-2xl transition-all duration-300 {{ $page === 'services' ? 'bg-blue-900 text-white shadow-lg shadow-blue-500/25' : 'text-gray-600 hover:bg-white/50 hover:text-gray-900 hover:shadow-md' }}">
                        <div class="flex items-center justify-center w-10 h-10 mr-4 rounded-xl {{ $page === 'services' ? 'bg-white/20' : 'bg-gray-100 group-hover:bg-white' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                            </svg>
                        </div>
                        Services
                    </a>

                    <!-- Service Mapping -->
                    <a wire:click="setPage('services-mapping')" class="group flex items-center px-4 py-3.5 text-sm font-medium rounded-2xl transition-all duration-300 {{ $page === 'services-mapping' ? 'bg-blue-900 text-white shadow-lg shadow-blue-500/25' : 'text-gray-600 hover:bg-white/50 hover:text-gray-900 hover:shadow-md' }}">
                        <div class="flex items-center justify-center w-10 h-10 mr-4 rounded-xl {{ $page === 'services-mapping' ? 'bg-white/20' : 'bg-gray-100 group-hover:bg-white' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m-6 3l6-3"></path>
                            </svg>
                        </div>
                        Service Mapping
                    </a>

                    <!-- Transactions -->
                    <a wire:click="setPage('transactions')" class="group flex items-center px-4 py-3.5 text-sm font-medium rounded-2xl transition-all duration-300 {{ $page === 'transactions' ? 'bg-blue-900 text-white shadow-lg shadow-blue-500/25' : 'text-gray-600 hover:bg-white/50 hover:text-gray-900 hover:shadow-md' }}">
                        <div class="flex items-center justify-center w-10 h-10 mr-4 rounded-xl {{ $page === 'transactions' ? 'bg-white/20' : 'bg-gray-100 group-hover:bg-white' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m9-6a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        Transactions
                    </a>
                </div>

                <!-- Reports Section -->
                <div class="mb-8">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 mb-4">Analytics</h3>
                    
                    <!-- Reports -->
                    <a wire:click="setPage('reports')" class="group flex items-center px-4 py-3.5 text-sm font-medium rounded-2xl transition-all duration-300 {{ $page === 'reports' ? 'bg-blue-900 text-white shadow-lg shadow-blue-500/25' : 'text-gray-600 hover:bg-white/50 hover:text-gray-900 hover:shadow-md' }}">
                        <div class="flex items-center justify-center w-10 h-10 mr-4 rounded-xl {{ $page === 'reports' ? 'bg-white/20' : 'bg-gray-100 group-hover:bg-white' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        Reports
                    </a>
                </div>

                <!-- Security Section -->
                <div class="mb-8">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 mb-4">Security</h3>
                    
                    <!-- Security Settings -->
                    <a wire:click="setPage('security-settings')" class="group flex items-center px-4 py-3.5 text-sm font-medium rounded-2xl transition-all duration-300 {{ $page === 'security-settings' ? 'bg-blue-900 text-white shadow-lg shadow-blue-500/25' : 'text-gray-600 hover:bg-white/50 hover:text-gray-900 hover:shadow-md' }}">
                        <div class="flex items-center justify-center w-10 h-10 mr-4 rounded-xl {{ $page === 'security-settings' ? 'bg-white/20' : 'bg-gray-100 group-hover:bg-white' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        Security Settings
                    </a>
                </div>

                <!-- Administration Section -->
                <div>
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 mb-4">Administration</h3>
                    
                    <!-- Users -->
                    <a 
                     wire:click="setPage('users')" 
                     class="group flex items-center px-4 py-3.5 text-sm font-medium rounded-2xl transition-all duration-300 {{ $page === 'users' ? 'bg-blue-900 text-white shadow-lg shadow-blue-500/25' : 'text-gray-600 hover:bg-white/50 hover:text-gray-900 hover:shadow-md' }}">
                        <div class="flex items-center justify-center w-10 h-10 mr-4 rounded-xl {{ $page === 'users' ? 'bg-white/20' : 'bg-gray-100 group-hover:bg-white' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        Users
                    </a>
                </div>
            </nav>
        </div>

        <!-- User Profile Section -->
        <div class="p-6 border-t border-white/10">
            <div class="flex items-center space-x-4 p-4 rounded-2xl bg-white/30 backdrop-blur-sm">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 flex items-center justify-center shadow-lg">
                    <span class="text-sm font-bold text-white">{{ Auth::user()->name[0] ?? 'U' }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>