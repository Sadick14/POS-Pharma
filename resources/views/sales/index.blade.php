@extends('layouts.app')

@section('title', 'Sales History')
@section('page-title', 'Sales Transactions')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Sales Transactions History</h1>
            <p class="text-xs text-slate-500">Review completed point-of-sale invoices, customer details, and payment methods.</p>
        </div>
        <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-900/20 transition">
            <i class="fa-solid fa-cash-register"></i> Open POS Terminal
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('sales.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-4 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search invoice #, customer name..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="sm:col-span-3">
                <select name="staff_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white">
                    <option value="">All Cashiers / Staff</option>
                    @foreach($staffMembers as $s)
                        <option value="{{ $s->id }}" {{ $staffId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="payment_method" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white">
                    <option value="">All Payment Methods</option>
                    <option value="cash" {{ $paymentMethod === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="mobile_money" {{ $paymentMethod === 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                    <option value="card" {{ $paymentMethod === 'card' ? 'selected' : '' }}>Card</option>
                </select>
            </div>

            <div class="sm:col-span-3 flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition">
                    Filter
                </button>
                <a href="{{ route('sales.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center justify-center">
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
                        <th class="py-3 px-4">Date & Time</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Items Count</th>
                        <th class="py-3 px-4">Total Amount</th>
                        <th class="py-3 px-4">Payment</th>
                        <th class="py-3 px-4">Cashier</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                <a href="{{ route('sales.show', $sale) }}" class="hover:text-emerald-600">{{ $sale->invoice_number }}</a>
                            </td>
                            <td class="py-3 px-4 text-slate-700">{{ $sale->sale_date->format('d M Y, H:i') }}</td>
                            <td class="py-3 px-4 text-slate-800">{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $sale->items->count() }} line item(s)</td>
                            <td class="py-3 px-4 font-black text-slate-900">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($sale->total, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                    {{ $sale->payment_method }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $sale->seller?->name ?? 'Staff' }}</td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('pos.receipt', $sale) }}" target="_blank" class="p-1.5 text-slate-400 hover:text-slate-700" title="Print Receipt">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                    <a href="{{ route('sales.show', $sale) }}" class="text-emerald-600 font-bold hover:underline">Details &rarr;</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">No sales transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sales->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
