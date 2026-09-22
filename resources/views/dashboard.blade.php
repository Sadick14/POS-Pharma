@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Pharmacy Executive Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Banner & Quick Action Buttons -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-slate-900/10 border border-slate-700/50 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="space-y-2">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                    Role: {{ auth()->user()->role_name }}
                </span>
                <span class="text-xs text-slate-400">• {{ now()->format('l, d F Y') }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                Welcome back, {{ auth()->user()->name }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 max-w-xl">
                Real-time inventory balances, FEFO batch distributions, POS checkout telemetry, and compliance monitoring.
            </p>
        </div>

        <div class="flex flex-wrap gap-2.5">
            @if(auth()->user()->canProcessSales())
                <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-950/40 transition">
                    <i class="fa-solid fa-cash-register"></i> Open POS
                </a>
            @endif

            @if(auth()->user()->canProcessPurchases())
                <a href="{{ route('purchases.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white border border-slate-600 rounded-xl text-xs font-bold transition">
                    <i class="fa-solid fa-cart-flatbed"></i> Receive Stock
                </a>
            @endif

            @if(auth()->user()->canManageInventory())
                <a href="{{ route('medicines.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white border border-slate-600 rounded-xl text-xs font-bold transition">
                    <i class="fa-solid fa-plus"></i> New Medicine
                </a>
            @endif
        </div>
    </div>

    <!-- Metrics Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- Today's Sales -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Today's Sales</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-black text-slate-900">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($metrics['today_sales'], 2) }}</p>
                <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-500">
                    <span class="font-semibold text-emerald-600">{{ $metrics['today_sales_count'] }}</span> transactions processed today
                </div>
            </div>
        </div>

        <!-- Inventory Valuation -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Stock Valuation</span>
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-vault"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-black text-slate-900">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($metrics['total_inventory_retail'], 2) }}</p>
                <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-500">
                    Cost: <span class="font-semibold text-slate-700">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($metrics['total_inventory_cost'], 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Low Stock Medicines</span>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-black {{ $metrics['low_stock_count'] > 0 ? 'text-amber-600' : 'text-slate-900' }}">
                    {{ $metrics['low_stock_count'] }}
                </p>
                <div class="mt-1 flex items-center justify-between text-xs">
                    <span class="text-slate-500">&le; Reorder threshold</span>
                    <a href="{{ route('inventory.low-stock') }}" class="font-bold text-amber-600 hover:underline">View items &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Expiry Alerts -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Expiry Status</span>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-calendar-xmark"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-black text-rose-600">{{ $metrics['expiry_counts']['expired'] }}</span>
                    <span class="text-xs text-rose-700 font-bold">Expired</span>
                    <span class="text-slate-300">|</span>
                    <span class="text-lg font-black text-orange-600">{{ $metrics['expiry_counts']['expiring_30'] }}</span>
                    <span class="text-xs text-orange-700 font-bold">&lt; 30d</span>
                </div>
                <div class="mt-1 flex items-center justify-between text-xs">
                    <span class="text-slate-500">FEFO priority items</span>
                    <a href="{{ route('inventory.expiries') }}" class="font-bold text-rose-600 hover:underline">Monitor &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Split Section: Critical Alerts & Action Items -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Low Stock Action List -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-boxes-packing"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Reorder Level Warning</h3>
                        <p class="text-[11px] text-slate-500">Medicines requiring replenishment from suppliers</p>
                    </div>
                </div>
                <a href="{{ route('inventory.low-stock') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">View All</a>
            </div>

            <div class="mt-4 divide-y divide-slate-100">
                @forelse($lowStockMedicines as $med)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div>
                            <p class="font-bold text-slate-800">{{ $med->name }}</p>
                            <p class="text-[11px] text-slate-500">{{ $med->dosage_form }} • {{ $med->strength }} ({{ $med->category?->name ?? 'N/A' }})</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                Stock: {{ $med->current_stock }} / Min: {{ $med->reorder_level }}
                            </span>
                            @if(auth()->user()->canProcessPurchases())
                                <a href="{{ route('purchases.create') }}" class="block text-[11px] font-bold text-emerald-600 hover:underline mt-0.5">Order Stock</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-slate-400">
                        <i class="fa-regular fa-circle-check text-2xl text-emerald-500 mb-1"></i>
                        <p class="text-xs font-semibold">No low stock items</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Soon Expiring Batches -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-orange-100 text-orange-700 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-stopwatch"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Batches Expiring in &lt; 30 Days</h3>
                        <p class="text-[11px] text-slate-500">FEFO priority dispensing queue</p>
                    </div>
                </div>
                <a href="{{ route('inventory.expiries') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">View All</a>
            </div>

            <div class="mt-4 divide-y divide-slate-100">
                @forelse($expiringBatches as $batch)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div>
                            <p class="font-bold text-slate-800">{{ $batch->medicine->name }}</p>
                            <p class="text-[11px] text-slate-500">Batch: <span class="font-mono font-semibold">{{ $batch->batch_number }}</span> • Supplier: {{ $batch->supplier?->name ?? 'N/A' }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-800">
                                Expires: {{ $batch->expiry_date->format('M d, Y') }} ({{ $batch->days_until_expiry }}d left)
                            </span>
                            <p class="text-[11px] text-slate-500 mt-0.5">Qty in batch: <span class="font-bold text-slate-800">{{ $batch->quantity }}</span></p>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-slate-400">
                        <i class="fa-regular fa-circle-check text-2xl text-emerald-500 mb-1"></i>
                        <p class="text-xs font-semibold">No batches expiring within 30 days</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Sales & Movements -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Sales Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Recent Sales Transactions</h3>
                    <p class="text-[11px] text-slate-500">Latest completed checkout receipts</p>
                </div>
                <a href="{{ route('sales.index') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">All Sales</a>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-100 font-bold uppercase text-[10px]">
                            <th class="pb-2">Invoice #</th>
                            <th class="pb-2">Customer</th>
                            <th class="pb-2">Total</th>
                            <th class="pb-2">Method</th>
                            <th class="pb-2 text-right">Receipt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($metrics['recent_sales'] as $sale)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-2.5 font-mono font-bold text-slate-800">
                                    <a href="{{ route('sales.show', $sale->id) }}" class="hover:text-emerald-600">{{ $sale->invoice_number }}</a>
                                </td>
                                <td class="py-2.5 text-slate-600">{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                                <td class="py-2.5 font-bold text-slate-900">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($sale->total, 2) }}</td>
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700 uppercase">{{ $sale->payment_method }}</span>
                                </td>
                                <td class="py-2.5 text-right">
                                    <a href="{{ route('pos.receipt', $sale->id) }}" target="_blank" class="p-1 text-slate-400 hover:text-slate-700" title="Print Receipt">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-slate-400">No sales recorded yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Stock Movements Audit Feed -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Stock Ledger Feed</h3>
                    <p class="text-[11px] text-slate-500">Immutable ledger entries (Sales, Inward, Adjustments)</p>
                </div>
                <a href="{{ route('inventory.movements') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">Full Ledger</a>
            </div>

            <div class="mt-4 space-y-3">
                @forelse($metrics['recent_movements'] as $sm)
                    <div class="flex items-start justify-between text-xs">
                        <div class="flex items-start gap-2.5">
                            <div class="w-6 h-6 rounded-md flex items-center justify-center text-[10px] mt-0.5 {{ $sm->quantity > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                <i class="fa-solid {{ $sm->quantity > 0 ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
                            </div>
                            <div>
                                <p class="font-bold text-slate-800">{{ $sm->medicine->name }}</p>
                                <p class="text-[10px] text-slate-500">{{ $sm->description }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-mono font-bold {{ $sm->quantity > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $sm->quantity > 0 ? '+' : '' }}{{ $sm->quantity }}
                            </span>
                            <p class="text-[9px] text-slate-400">{{ $sm->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-slate-400">
                        <p class="text-xs">No stock movements recorded yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
