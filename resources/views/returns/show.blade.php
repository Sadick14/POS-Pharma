@extends('layouts.app')

@section('title', 'Return #' . $return->return_number)
@section('page-title', 'Return Receipt Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 font-mono">{{ $return->return_number }}</h1>
            <p class="text-xs text-slate-500">Original Sale: <strong class="text-slate-800">{{ $return->sale->invoice_number }}</strong> • Customer: {{ $return->sale->customer?->name ?? 'Walk-in' }}</p>
        </div>
        <a href="{{ route('returns.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
            &larr; Back to Returns
        </a>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Returned Line Items</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/75 text-slate-500 border-b border-slate-200 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">Medicine</th>
                        <th class="py-3 px-4">Batch Number</th>
                        <th class="py-3 px-4 text-center">Returned Qty</th>
                        <th class="py-3 px-4 text-right">Unit Refund</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                        <th class="py-3 px-4">Restocked?</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($return->items as $it)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $it->medicine->name }}</td>
                            <td class="py-3 px-4 font-mono text-slate-700">{{ $it->batch?->batch_number ?? 'N/A' }}</td>
                            <td class="py-3 px-4 font-bold text-center text-slate-900">{{ $it->quantity }}</td>
                            <td class="py-3 px-4 text-right text-slate-700">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($it->unit_refund_price, 2) }}</td>
                            <td class="py-3 px-4 text-right font-black font-mono text-rose-600">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($it->subtotal, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $it->is_resalable ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $it->is_resalable ? 'Restocked (FEFO)' : 'Disposed / Damaged' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-bold text-slate-900 border-t border-slate-200">
                        <td colspan="4" class="py-3 px-4 text-right uppercase text-[10px]">Total Refund Issued:</td>
                        <td class="py-3 px-4 text-right font-black font-mono text-base text-rose-600">
                            {{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($return->total_refund, 2) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
