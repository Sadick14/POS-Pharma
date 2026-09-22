@extends('layouts.app')

@section('title', 'Sale #' . $sale->invoice_number)
@section('page-title', 'Sales Transaction Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                    {{ $sale->payment_status }} • {{ strtoupper($sale->payment_method) }}
                </span>
                <span class="text-xs text-slate-500">{{ $sale->sale_date->format('d M Y, H:i') }}</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 font-mono">{{ $sale->invoice_number }}</h1>
            <p class="text-xs text-slate-500">Customer: <strong class="text-slate-800">{{ $sale->customer?->name ?? 'Walk-in Customer' }}</strong> • Cashier: {{ $sale->seller?->name ?? 'Staff' }}</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('pos.receipt', $sale) }}" target="_blank" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-print"></i> Thermal Receipt
            </a>
            @if(auth()->user()->canProcessReturns())
                <a href="{{ route('returns.create', $sale) }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-rotate-left"></i> Process Return
                </a>
            @endif
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Dispensed Items & FEFO Batches</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/75 text-slate-500 border-b border-slate-200 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">Medicine</th>
                        <th class="py-3 px-4">Deducted Batch</th>
                        <th class="py-3 px-4">Batch Expiry</th>
                        <th class="py-3 px-4 text-center">Quantity</th>
                        <th class="py-3 px-4 text-right">Unit Price</th>
                        <th class="py-3 px-4 text-right">Discount</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($sale->items as $item)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $item->medicine->name }}</td>
                            <td class="py-3 px-4 font-mono text-slate-700">{{ $item->batch?->batch_number ?? 'N/A' }}</td>
                            <td class="py-3 px-4 font-mono text-slate-600">{{ $item->batch?->expiry_date?->format('Y-m-d') ?? 'N/A' }}</td>
                            <td class="py-3 px-4 font-bold text-center text-slate-900">{{ $item->quantity }}</td>
                            <td class="py-3 px-4 text-right text-slate-700">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-3 px-4 text-right text-slate-500">-{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($item->discount, 2) }}</td>
                            <td class="py-3 px-4 text-right font-black font-mono text-slate-900">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-bold text-slate-900 border-t border-slate-200">
                        <td colspan="6" class="py-3 px-4 text-right uppercase text-[10px]">Total Paid:</td>
                        <td class="py-3 px-4 text-right font-black font-mono text-base text-emerald-700">
                            {{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($sale->total, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
