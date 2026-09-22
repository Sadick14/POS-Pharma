@extends('layouts.app')

@section('title', $customer->name)
@section('page-title', 'Customer Purchases Profile')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900">{{ $customer->name }}</h1>
            <p class="text-xs text-slate-500">Phone: {{ $customer->phone ?? 'N/A' }} • Email: {{ $customer->email ?? 'N/A' }} • Address: {{ $customer->address ?? 'N/A' }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('customers.edit', $customer) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition">
                <i class="fa-solid fa-pencil mr-1"></i> Edit Profile
            </a>
            <a href="{{ route('customers.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                Directory
            </a>
        </div>
    </div>

    <!-- Purchases History Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Purchase History</h3>
            <p class="text-[11px] text-slate-500">Invoices and prescriptions issued to {{ $customer->name }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/75 text-slate-500 border-b border-slate-200 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">Invoice #</th>
                        <th class="py-3 px-4">Date & Time</th>
                        <th class="py-3 px-4">Items Count</th>
                        <th class="py-3 px-4">Total Amount</th>
                        <th class="py-3 px-4">Payment Method</th>
                        <th class="py-3 px-4">Dispensed By</th>
                        <th class="py-3 px-4 text-right">Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customer->sales as $sale)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                <a href="{{ route('sales.show', $sale) }}" class="hover:text-emerald-600">{{ $sale->invoice_number }}</a>
                            </td>
                            <td class="py-3 px-4 text-slate-700">{{ $sale->sale_date->format('d M Y, H:i') }}</td>
                            <td class="py-3 px-4 text-slate-700">{{ $sale->items->count() }} line item(s)</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($sale->total, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                    {{ $sale->payment_method }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $sale->seller?->name ?? 'Staff' }}</td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('pos.receipt', $sale) }}" target="_blank" class="p-1.5 text-slate-400 hover:text-slate-700">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">No purchases recorded for this customer yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
