@extends('layouts.app')

@section('title', 'Purchases & Receiving')
@section('page-title', 'Stock Purchases & Receiving')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Purchases & Inward Orders</h1>
            <p class="text-xs text-slate-500">Record supplier invoices and receive new medicine batches into the FEFO ledger.</p>
        </div>
        <a href="{{ route('purchases.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-900/20 transition">
            <i class="fa-solid fa-cart-flatbed"></i> Receive Stock Order
        </a>
    </div>

    <!-- Filter -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('purchases.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search invoice #, supplier name..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="sm:col-span-4">
                <select name="supplier_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ $supplierId == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3 flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition">
                    Filter
                </button>
                <a href="{{ route('purchases.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center justify-center">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/75 text-slate-500 border-b border-slate-200 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">Invoice #</th>
                        <th class="py-3 px-4">Supplier</th>
                        <th class="py-3 px-4">Purchase Date</th>
                        <th class="py-3 px-4">Items Received</th>
                        <th class="py-3 px-4">Total Amount</th>
                        <th class="py-3 px-4">Payment</th>
                        <th class="py-3 px-4">Received By</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($purchases as $p)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                <a href="{{ route('purchases.show', $p) }}" class="hover:text-emerald-600">{{ $p->invoice_number }}</a>
                            </td>
                            <td class="py-3 px-4 text-slate-800 font-medium">{{ $p->supplier?->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $p->purchase_date->format('d M Y') }}</td>
                            <td class="py-3 px-4 font-bold text-slate-700">{{ $p->items->count() }} line item(s)</td>
                            <td class="py-3 px-4 font-black text-slate-900">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($p->total, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700">
                                    {{ $p->payment_status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $p->creator?->name ?? 'Staff' }}</td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('purchases.show', $p) }}" class="text-emerald-600 font-bold hover:underline">View Invoice &rarr;</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">No purchase records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($purchases->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $purchases->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
