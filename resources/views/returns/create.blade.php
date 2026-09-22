@extends('layouts.app')

@section('title', 'Return for Sale #' . $sale->invoice_number)
@section('page-title', 'Process Sales Return')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Process Return for Invoice: {{ $sale->invoice_number }}</h1>
            <p class="text-xs text-slate-500">Select items to return, refund rate, and specify whether items are resalable for restocking.</p>
        </div>
        <a href="{{ route('sales.show', $sale) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
            &larr; Back to Sale
        </a>
    </div>

    <form action="{{ route('returns.store', $sale) }}" method="POST" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6 text-xs">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-4 border-b border-slate-100">
            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Refund Method *</label>
                <select name="refund_method" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="cash">Cash Refund</option>
                    <option value="mobile_money">Mobile Money Refund</option>
                    <option value="store_credit">Store Credit</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Reason for Return *</label>
                <input type="text" name="reason" value="{{ old('reason', 'Customer return approved by Pharmacist') }}" required
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>

        <div class="space-y-4">
            <h3 class="text-sm font-bold text-slate-900">Select Return Items</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 border-b border-slate-200 font-bold uppercase text-[10px]">
                            <th class="py-2.5 px-3">Medicine</th>
                            <th class="py-2.5 px-3">Purchased Qty</th>
                            <th class="py-2.5 px-3">Return Qty *</th>
                            <th class="py-2.5 px-3">Refund Unit Price *</th>
                            <th class="py-2.5 px-3">Resalable (Restock)?</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($sale->items as $idx => $item)
                            <tr>
                                <td class="py-3 px-3">
                                    <input type="hidden" name="items[{{ $idx }}][sale_item_id]" value="{{ $item->id }}">
                                    <input type="hidden" name="items[{{ $idx }}][medicine_id]" value="{{ $item->medicine_id }}">
                                    <input type="hidden" name="items[{{ $idx }}][batch_id]" value="{{ $item->batch_id }}">
                                    <p class="font-bold text-slate-800">{{ $item->medicine->name }}</p>
                                    <p class="text-[10px] text-slate-400">Batch: {{ $item->batch?->batch_number ?? 'N/A' }}</p>
                                </td>
                                <td class="py-3 px-3 font-bold text-slate-700">{{ $item->quantity }}</td>
                                <td class="py-3 px-3">
                                    <input type="number" min="0" max="{{ $item->quantity }}" name="items[{{ $idx }}][quantity]" value="{{ $item->quantity }}"
                                           class="w-20 px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-center font-bold">
                                </td>
                                <td class="py-3 px-3">
                                    <input type="number" step="0.01" name="items[{{ $idx }}][unit_refund_price]" value="{{ $item->unit_price }}"
                                           class="w-24 px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-right font-bold">
                                </td>
                                <td class="py-3 px-3">
                                    <select name="items[{{ $idx }}][is_resalable]" class="px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold">
                                        <option value="1">Yes (Restock into batch)</option>
                                        <option value="0">No (Damaged / Dispose)</option>
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
            <a href="{{ route('sales.show', $sale) }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-900/20">
                Confirm Return & Issue Refund
            </button>
        </div>
    </form>
</div>
@endsection
