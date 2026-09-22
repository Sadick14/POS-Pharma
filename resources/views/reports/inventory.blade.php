@extends('layouts.app')

@section('title', 'Stock Valuation')
@section('page-title', 'Inventory Valuation Report')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Inventory Valuation Report</h1>
            <p class="text-xs text-slate-500">Batch-by-batch valuation calculated at historical purchase cost vs retail selling price.</p>
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

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Stock Cost Value</span>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($totalCost, 2) }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Total acquisition expenditure</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Stock Retail Value</span>
            <p class="text-2xl font-black text-sky-700 mt-1">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($totalRetail, 2) }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Potential revenue at retail</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Potential Gross Profit</span>
            <p class="text-2xl font-black text-emerald-700 mt-1">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($potentialProfit, 2) }}</p>
            <p class="text-[11px] text-emerald-600 font-bold mt-0.5">Margin: {{ $totalRetail > 0 ? number_format(($potentialProfit / $totalRetail) * 100, 1) : 0 }}%</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/75 text-slate-500 border-b border-slate-200 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">Medicine & Category</th>
                        <th class="py-3 px-4">Batch Number</th>
                        <th class="py-3 px-4">Expiry Date</th>
                        <th class="py-3 px-4 text-center">Remaining Qty</th>
                        <th class="py-3 px-4 text-right">Unit Cost</th>
                        <th class="py-3 px-4 text-right">Unit Price</th>
                        <th class="py-3 px-4 text-right">Cost Total</th>
                        <th class="py-3 px-4 text-right">Retail Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($valuationData as $row)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $row['medicine_name'] }}</div>
                                <div class="text-[10px] text-slate-400">{{ $row['category'] }}</div>
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-700">{{ $row['batch_number'] }}</td>
                            <td class="py-3 px-4">
                                @if($row['is_expired'])
                                    <span class="text-rose-600 font-bold font-mono">{{ $row['expiry_date'] }} (Expired)</span>
                                @else
                                    <span class="font-mono text-slate-700">{{ $row['expiry_date'] }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-black text-center text-slate-900">{{ $row['quantity'] }}</td>
                            <td class="py-3 px-4 text-right text-slate-600">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($row['unit_cost'], 2) }}</td>
                            <td class="py-3 px-4 text-right text-slate-800 font-bold">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($row['unit_price'], 2) }}</td>
                            <td class="py-3 px-4 text-right font-bold font-mono text-slate-700">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($row['cost_valuation'], 2) }}</td>
                            <td class="py-3 px-4 text-right font-black font-mono text-emerald-700">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($row['retail_valuation'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">No active stock to evaluate.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
