@php
    $user = auth()->user();
    $currentRoute = request()->route() ? request()->route()->getName() : '';
@endphp

<div class="mt-5 flex-1 px-3 space-y-6">
    <!-- Main Group -->
    <div>
        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Core</p>
        <div class="space-y-1">
            <a href="{{ route('dashboard') }}" class="group flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-xl transition {{ str_starts_with($currentRoute, 'dashboard') || $currentRoute === 'home' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-gauge-high w-4 text-center"></i> Dashboard
            </a>

            @if($user && $user->canProcessSales())
                <a href="{{ route('pos.index') }}" class="group flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-xl transition {{ str_starts_with($currentRoute, 'pos') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-cash-register w-4 text-center text-emerald-400"></i> Point of Sale (POS)
                </a>

                <a href="{{ route('sales.index') }}" class="group flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-xl transition {{ str_starts_with($currentRoute, 'sales') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-receipt w-4 text-center"></i> Sales History
                </a>
            @endif

            @if($user && $user->canProcessReturns())
                <a href="{{ route('returns.index') }}" class="group flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-xl transition {{ str_starts_with($currentRoute, 'returns') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-rotate-left w-4 text-center"></i> Sales Returns
                </a>
            @endif
        </div>
    </div>

    <!-- Inventory & Catalog Group -->
    @if($user && $user->canManageInventory())
    <div>
        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Inventory & Catalog</p>
        <div class="space-y-1">
            <a href="{{ route('medicines.index') }}" class="group flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-xl transition {{ str_starts_with($currentRoute, 'medicines') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-pills w-4 text-center"></i> Medicines Catalog
            </a>

            <a href="{{ route('categories.index') }}" class="group flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-xl transition {{ str_starts_with($currentRoute, 'categories') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-layer-group w-4 text-center"></i> Categories
            </a>

            <a href="{{ route('inventory.index') }}" class="group flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-xl transition {{ $currentRoute === 'inventory.index' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-boxes-stacked w-4 text-center"></i> Stock Overview
            </a>

            <a href="{{ route('inventory.batches') }}" class="group flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-xl transition {{ $currentRoute === 'inventory.batches' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-barcode w-4 text-center"></i> Batch Ledger (FEFO)
            </a>

            <a href="{{ route('inventory.low-stock') }}" class="group flex items-center justify-between px-3 py-2 text-xs font-semibold rounded-xl transition {{ $currentRoute === 'inventory.low-stock' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span class="flex items-center gap-3"><i class="fa-solid fa-triangle-exclamation w-4 text-center text-amber-400"></i> Low Stock Alerts</span>
                @if(isset($lowStockCount) && $lowStockCount > 0)
                    <span class="text-[10px] bg-amber-500 text-slate-950 font-bold px-1.5 py-0.5 rounded-full">{{ $lowStockCount }}</span>
                @endif
            </a>

            <a href="{{ route('inventory.expiries') }}" class="group flex items-center justify-between px-3 py-2 text-xs font-semibold rounded-xl transition {{ $currentRoute === 'inventory.expiries' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span class="flex items-center gap-3"><i class="fa-solid fa-clock-rotate-left w-4 text-center text-rose-400"></i> Expiry Monitoring</span>
                @if(isset($expiryCounts) && ($expiryCounts['expired'] + $expiryCounts['expiring_30']) > 0)
                    <span class="text-[10px] bg-rose-500 text-white font-bold px-1.5 py-0.5 rounded-full">{{ $expiryCounts['expired'] + $expiryCounts['expiring_30'] }}</span>
                @endif
            </a>

            @if($user->canAdjustStock())
                <a href="{{ route('inventory.adjustments') }}" class="group flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-xl transition {{ str_starts_with($currentRoute, 'inventory.adjustments') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-sliders w-4 text-center"></i> Stock Adjustments
                </a>
            @endif

            <a href="{{ route('inventory.movements') }}" class="group flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-xl transition {{ $currentRoute === 'inventory.movements' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-arrow-right-arrow-left w-4 text-center"></i> Stock Movement Ledger
            </a>
        </div>
    </div>
    @endif

    <!-- Procurement Group -->
    @if($user && $user->canProcessPurchases())
    <div>
        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Procurement</p>
        <div class="space-y-1">
            <a href="{{ route('purchases.index') }}" class="group flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-xl transition {{ str_starts_with($currentRoute, 'purchases') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-cart-flatbed w-4 text-center"></i> Purchases & Inward
            </a>

            <a href="{{ route('suppliers.index') }}" class="group flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-xl transition {{ str_starts_with($currentRoute, 'suppliers') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-truck-field w-4 text-center"></i> Suppliers
            </a>
        </div>
    </div>
    @endif

    <!-- Customers Group -->
    <div>
        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">People</p>
        <div class="space-y-1">
            <a href="{{ route('customers.index') }}" class="group flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-xl transition {{ str_starts_with($currentRoute, 'customers') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-users w-4 text-center"></i> Customers
            </a>
        </div>
    </div>

    <!-- Reports & Audit Group -->
    @if($user && ($user->canViewReports() || $user->canViewAuditLogs()))
    <div>
        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Analytics & Compliance</p>
        <div class="space-y-1">
            @if($user->canViewReports())
                <a href="{{ route('reports.index') }}" class="group flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-xl transition {{ str_starts_with($currentRoute, 'reports') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-chart-pie w-4 text-center"></i> Reports & Analytics
                </a>
            @endif

            @if($user->canViewAuditLogs())
                <a href="{{ route('audit-logs.index') }}" class="group flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-xl transition {{ str_starts_with($currentRoute, 'audit-logs') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-clipboard-check w-4 text-center"></i> Audit Trail
                </a>
            @endif
        </div>
    </div>
    @endif

    <!-- System Administration -->
    @if($user && $user->isAdmin())
    <div>
        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Administration</p>
        <div class="space-y-1">
            <a href="{{ route('users.index') }}" class="group flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-xl transition {{ str_starts_with($currentRoute, 'users') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-user-shield w-4 text-center"></i> Users & Roles
            </a>

            <a href="{{ route('settings.index') }}" class="group flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-xl transition {{ str_starts_with($currentRoute, 'settings') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/40' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-sliders w-4 text-center"></i> Pharmacy Settings
            </a>
        </div>
    </div>
    @endif
</div>
