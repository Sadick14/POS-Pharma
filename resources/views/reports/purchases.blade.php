@extends('layouts.app')

@section('title', 'Purchases Report')
@section('page-title', 'Procurement & Spend Analytics')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Procurement & Purchases Report</h1>
            <p class="text-xs text-slate-500">Expenditure breakdown by supplier, invoice date, and volume.</p>
        </div>
        <a href="{{ route('reports.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
            Reports Directory
        </a>
    </div>

    <!-- Filter -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('reports.purchases') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
            <div class="sm:col-span-4">
                <label class="block font-bold text-slate-600 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
            </div>

            <div class="sm:col-span-4">
                <label class="block font-bold text-slate-600 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
            </div>

            <div class="sm:col-span-4 flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold transition">
                    Filter Spend
                </button>
            </div>
        </form>
    </div>

    <!-- Total Expenditure Card -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Purchase Spend (Selected Period)</span>
        <p class="text-2xl font-black text-slate-900 mt-1">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($totalExpenditure, 2) }}</p>
    </div>

    <!-- Purchases Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/75 text-slate-500 border-b border-slate-200 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">Invoice #</th>
                        <th class="py-3 px-4">Supplier</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Items Count</th>
                        <th class="py-3 px-4">Total Amount</th>
                        <th class="py-3 px-4">Payment</th>
                        <th class="py-3 px-4 text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($purchases as $p)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $p->invoice_number }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $p->supplier?->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $p->purchase_date->format('d M Y') }}</td>
                            <td class="py-3 px-4 text-slate-700">{{ $p->items->count() }} line item(s)</td>
                            <td class="py-3 px-4 font-black text-slate-900">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($p->total, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700">
                                    {{ $p->payment_status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('purchases.show', $p) }}" class="text-emerald-600 font-bold hover:underline">View &rarr;</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No purchases found for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
