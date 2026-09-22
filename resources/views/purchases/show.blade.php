@extends('layouts.app')

@section('title', 'Purchase #' . $purchase->invoice_number)
@section('page-title', 'Purchase Order Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                    {{ $purchase->payment_status }}
                </span>
                <span class="text-xs text-slate-500">{{ $purchase->purchase_date->format('d M Y') }}</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 font-mono">{{ $purchase->invoice_number }}</h1>
            <p class="text-xs text-slate-500">Supplier: <strong class="text-slate-800">{{ $purchase->supplier?->name ?? 'N/A' }}</strong> • Received By: {{ $purchase->creator?->name ?? 'Staff' }}</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('purchases.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                &larr; Purchases
            </a>
        </div>
    </div>

    <!-- Purchase Items Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Received Batches & Line Items</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/75 text-slate-500 border-b border-slate-200 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">Medicine</th>
                        <th class="py-3 px-4">Batch Number</th>
                        <th class="py-3 px-4">Expiry Date</th>
                        <th class="py-3 px-4 text-center">Quantity</th>
                        <th class="py-3 px-4 text-right">Unit Cost</th>
                        <th class="py-3 px-4 text-right">Selling Price</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($purchase->items as $item)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $item->medicine->name }}</td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-700">{{ $item->batch_number }}</td>
                            <td class="py-3 px-4 font-mono text-slate-600">{{ $item->expiry_date->format('Y-m-d') }}</td>
                            <td class="py-3 px-4 font-bold text-center text-slate-900">{{ $item->quantity }}</td>
                            <td class="py-3 px-4 text-right text-slate-600">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($item->unit_cost, 2) }}</td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-700">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($item->selling_price, 2) }}</td>
                            <td class="py-3 px-4 text-right font-black font-mono text-slate-900">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-bold text-slate-900 border-t border-slate-200">
                        <td colspan="6" class="py-3 px-4 text-right uppercase text-[10px] tracking-wider">Grand Total:</td>
                        <td class="py-3 px-4 text-right font-black font-mono text-base text-slate-900">
                            {{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($purchase->total, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
