@extends('layouts.app')

@section('title', 'Profit & Loss')
@section('page-title', 'Profit & Loss Overview')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Gross Profit & Loss Analysis</h1>
            <p class="text-xs text-slate-500">Revenue, cost of goods sold, profit margins, and top margin contributors.</p>
        </div>
        <a href="{{ route('reports.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
            Reports Directory
        </a>
    </div>

    <!-- Date Range Filter -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('reports.profit') }}" class="flex flex-col sm:flex-row gap-3 items-end text-xs">
            <div class="flex-1">
                <label class="block font-bold text-slate-600 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
            </div>
            <div class="flex-1">
                <label class="block font-bold text-slate-600 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
            </div>
            <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold transition">
                Apply Date Range
            </button>
        </form>
    </div>

    <!-- P&L Financial Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Net Revenue</span>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($financials['net_sales'], 2) }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Cost of Goods Sold (COGS)</span>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($financials['cost_of_goods_sold'], 2) }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Gross Profit</span>
            <p class="text-2xl font-black text-emerald-700 mt-1">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($financials['gross_profit'], 2) }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Gross Margin %</span>
            <p class="text-2xl font-black text-emerald-700 mt-1">{{ number_format($financials['profit_margin'], 1) }}%</p>
        </div>
    </div>

    <!-- Top Profitable Items Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Top Revenue & Margin Generating Medicines</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/75 text-slate-500 border-b border-slate-200 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">Medicine</th>
                        <th class="py-3 px-4 text-center">Units Sold</th>
                        <th class="py-3 px-4 text-right">Revenue</th>
                        <th class="py-3 px-4 text-right">Cost</th>
                        <th class="py-3 px-4 text-right">Gross Profit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($topProfitable as $tp)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $tp->name }}</td>
                            <td class="py-3 px-4 text-center font-bold text-slate-800">{{ $tp->total_qty_sold }}</td>
                            <td class="py-3 px-4 text-right font-bold text-slate-900">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($tp->total_revenue, 2) }}</td>
                            <td class="py-3 px-4 text-right text-slate-600">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($tp->total_cost, 2) }}</td>
                            <td class="py-3 px-4 text-right font-black font-mono text-emerald-700">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($tp->estimated_profit, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">No transaction data available for this range.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
