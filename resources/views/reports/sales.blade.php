@extends('layouts.app')

@section('title', 'Sales Report')
@section('page-title', 'Sales & Revenue Analytics')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Sales & Revenue Report</h1>
            <p class="text-xs text-slate-500">Transaction history, cashier breakdown, and exportable CSV data.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-file-csv"></i> Export CSV
            </a>
            <a href="{{ route('reports.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                Reports Directory
            </a>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('reports.sales') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
            <div class="sm:col-span-3">
                <label class="block font-bold text-slate-600 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
            </div>

            <div class="sm:col-span-3">
                <label class="block font-bold text-slate-600 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
            </div>

            <div class="sm:col-span-3">
                <label class="block font-bold text-slate-600 mb-1">Cashier / Staff</label>
                <select name="staff_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                    <option value="">All Staff</option>
                    @foreach($staffMembers as $s)
                        <option value="{{ $s->id }}" {{ $staffId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3 flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold transition">
                    Generate Report
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Revenue</span>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($totalRevenue, 2) }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Discounts Applied</span>
            <p class="text-2xl font-black text-amber-600 mt-1">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($totalDiscounts, 2) }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Transactions</span>
            <p class="text-2xl font-black text-emerald-700 mt-1">{{ $totalTransactions }}</p>
        </div>
    </div>

    <!-- Product Sales Breakdown -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Product Performance Breakdown</h3>
            <p class="text-[11px] text-slate-500">Total units sold, revenue, and gross profit generated per medicine</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/75 text-slate-500 border-b border-slate-200 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">Medicine Formulation</th>
                        <th class="py-3 px-4 text-center">Units Sold</th>
                        <th class="py-3 px-4 text-right">Total Revenue</th>
                        <th class="py-3 px-4 text-right">Estimated Cost</th>
                        <th class="py-3 px-4 text-right">Gross Profit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($medicineSales as $ms)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $ms->name }}
                                <span class="text-[10px] text-slate-400 font-normal">({{ $ms->dosage_form }} • {{ $ms->strength }})</span>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-800">{{ $ms->total_qty_sold }}</td>
                            <td class="py-3 px-4 text-right font-bold text-slate-900">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($ms->total_revenue, 2) }}</td>
                            <td class="py-3 px-4 text-right text-slate-600">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($ms->total_cost, 2) }}</td>
                            <td class="py-3 px-4 text-right font-black font-mono text-emerald-700">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($ms->estimated_profit, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400">No product sales found for this date range.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
