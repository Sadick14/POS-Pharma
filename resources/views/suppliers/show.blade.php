@extends('layouts.app')

@section('title', $supplier->name)
@section('page-title', 'Supplier Profile')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $supplier->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                    {{ $supplier->status }}
                </span>
                <span class="text-xs font-mono text-slate-500">Reg: {{ $supplier->registration_number ?? 'N/A' }}</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900">{{ $supplier->name }}</h1>
            <p class="text-xs text-slate-500">Contact: {{ $supplier->contact_person ?? 'N/A' }} • Phone: {{ $supplier->phone ?? 'N/A' }} • Email: {{ $supplier->email ?? 'N/A' }}</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('purchases.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                <i class="fa-solid fa-cart-flatbed mr-1"></i> Receive Inward Stock
            </a>
            <a href="{{ route('suppliers.edit', $supplier) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                Edit
            </a>
        </div>
    </div>

    <!-- Purchases History from Supplier -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Purchase Invoices History</h3>
            <p class="text-[11px] text-slate-500">Orders received from {{ $supplier->name }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/75 text-slate-500 border-b border-slate-200 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">Invoice Number</th>
                        <th class="py-3 px-4">Purchase Date</th>
                        <th class="py-3 px-4">Total Amount</th>
                        <th class="py-3 px-4">Payment Status</th>
                        <th class="py-3 px-4">Received By</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($supplier->purchases as $p)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $p->invoice_number }}</td>
                            <td class="py-3 px-4 text-slate-700">{{ $p->purchase_date->format('d M Y') }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($p->total, 2) }}</td>
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
                            <td colspan="6" class="py-6 text-center text-slate-400">No purchase records found for this supplier.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
