<div class="flex h-screen bg-white" x-data x-on:page-changed.window="$wire.setPage($event.detail.page)">
    <!-- Sidebar Component -->
    <livewire:layout.sidebar />
    
    <!-- Main content -->
    <div class="flex flex-col w-0 flex-1 overflow-hidden">
        <!-- Top Bar Component -->
        <livewire:layout.top-bar />
        
        <!-- Page content -->
        <main class="flex-1 relative overflow-y-auto focus:outline-none">
           @switch($page)
           @case('dashboard')
           <livewire:dashboard.dashboard />
           @break
           @case('aggregators')
           <livewire:aggregators.aggregators />
           @break
           @case('clients')
           <livewire:clients.clients />
           @break
           @case('services')
           <livewire:services.services />
           @break
           @case('services-mapping')
           <livewire:services-mapping.services-mapping />
           @break
           @case('transactions')
           <livewire:transactions.transactions />
           @break
           @case('reports')
           <livewire:reports.reports />
           @break
           @case('users')
           <livewire:users.users />
           @break
           @case('security-settings')
           <livewire:security-settings.security-settings />
           @break
           @default
           <livewire:dashboard.dashboard />
           @break
           @endswitch
        </main>
    </div>
</div>
