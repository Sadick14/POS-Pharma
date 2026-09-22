@extends('layouts.app')

@section('title', 'Reports & Analytics')
@section('page-title', 'Reports & Analytics Suite')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Pharmacy Reports & Analytics</h1>
        <p class="text-xs text-slate-500">Comprehensive reporting suite for sales, inventory valuation, profit margins, and compliance.</p>
    </div>

    <!-- Overview Financials -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Month-to-Date Net Sales</span>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($overview['net_sales'], 2) }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Discounts given: {{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($overview['discounts'], 2) }}</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Cost of Goods Sold (COGS)</span>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($overview['cost_of_goods_sold'], 2) }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Historical batch purchase cost</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Gross Profit (MTD)</span>
            <p class="text-2xl font-black text-emerald-700 mt-1">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($overview['gross_profit'], 2) }}</p>
            <p class="text-[11px] text-emerald-600 font-bold mt-0.5">Margin: {{ number_format($overview['profit_margin'], 1) }}%</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Month Purchases</span>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($overview['total_purchases'], 2) }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Inward stock acquisitions</p>
        </div>
    </div>

    <!-- Reports Directory Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Sales Report Card -->
        <a href="{{ route('reports.sales') }}" class="bg-white p-6 rounded-3xl border border-slate-200/80 hover:border-emerald-500 hover:shadow-md transition group flex flex-col justify-between">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl group-hover:bg-emerald-600 group-hover:text-white transition">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900 group-hover:text-emerald-700 transition">Sales & Revenue Reports</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Filter sales by date range, payment method (Cash, MoMo, Card), cashier performance, and export detailed CSV reports.
                </p>
            </div>
            <span class="mt-4 inline-flex items-center gap-1 text-xs font-bold text-emerald-600">Open Report &rarr;</span>
        </a>

        <!-- Inventory Valuation Card -->
        <a href="{{ route('reports.inventory') }}" class="bg-white p-6 rounded-3xl border border-slate-200/80 hover:border-emerald-500 hover:shadow-md transition group flex flex-col justify-between">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl group-hover:bg-sky-600 group-hover:text-white transition">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900 group-hover:text-sky-700 transition">Stock Valuation & Balances</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Detailed batch-level inventory costing, retail potential valuation, stock quantities, and CSV export.
                </p>
            </div>
            <span class="mt-4 inline-flex items-center gap-1 text-xs font-bold text-sky-600">Open Report &rarr;</span>
        </a>

        <!-- Profit & Loss Card -->
        <a href="{{ route('reports.profit') }}" class="bg-white p-6 rounded-3xl border border-slate-200/80 hover:border-emerald-500 hover:shadow-md transition group flex flex-col justify-between">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl group-hover:bg-indigo-600 group-hover:text-white transition">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900 group-hover:text-indigo-700 transition">Gross Profit & Margins</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Analyze profitability per medicine formulation, total cost of goods sold, and gross margin ratios.
                </p>
            </div>
            <span class="mt-4 inline-flex items-center gap-1 text-xs font-bold text-indigo-600">Open Report &rarr;</span>
        </a>

        <!-- Expiry Forecast Card -->
        <a href="{{ route('reports.expiries') }}" class="bg-white p-6 rounded-3xl border border-slate-200/80 hover:border-emerald-500 hover:shadow-md transition group flex flex-col justify-between">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl group-hover:bg-rose-600 group-hover:text-white transition">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900 group-hover:text-rose-700 transition">Expiry Analysis & Wastage</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Comprehensive breakdown of expired medicines and upcoming batches expiring in 30, 60, and 90 days.
                </p>
            </div>
            <span class="mt-4 inline-flex items-center gap-1 text-xs font-bold text-rose-600">Open Report &rarr;</span>
        </a>

        <!-- Procurement Card -->
        <a href="{{ route('reports.purchases') }}" class="bg-white p-6 rounded-3xl border border-slate-200/80 hover:border-emerald-500 hover:shadow-md transition group flex flex-col justify-between">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl group-hover:bg-amber-600 group-hover:text-white transition">
                    <i class="fa-solid fa-cart-flatbed"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900 group-hover:text-amber-700 transition">Purchases & Supplier Spend</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Track procurement expenses per supplier, invoice payment status, and inward stock volume.
                </p>
            </div>
            <span class="mt-4 inline-flex items-center gap-1 text-xs font-bold text-amber-600">Open Report &rarr;</span>
        </a>
    </div>
</div>
@endsection
