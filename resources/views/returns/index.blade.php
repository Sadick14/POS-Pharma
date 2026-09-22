@extends('layouts.app')

@section('title', 'Sales Returns')
@section('page-title', 'Sales Returns & Restocking')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Sales Returns Management</h1>
            <p class="text-xs text-slate-500">Processed returns, customer refunds, and restocked or damaged write-offs.</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/75 text-slate-500 border-b border-slate-200 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">Return #</th>
                        <th class="py-3 px-4">Original Invoice</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Total Refund</th>
                        <th class="py-3 px-4">Reason</th>
                        <th class="py-3 px-4">Processed By</th>
                        <th class="py-3 px-4 text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($returns as $ret)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $ret->return_number }}</td>
                            <td class="py-3 px-4 font-mono text-slate-700">
                                <a href="{{ route('sales.show', $ret->sale) }}" class="hover:text-emerald-600">{{ $ret->sale->invoice_number }}</a>
                            </td>
                            <td class="py-3 px-4 text-slate-800">{{ $ret->sale->customer?->name ?? 'Walk-in' }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $ret->created_at->format('d M Y, H:i') }}</td>
                            <td class="py-3 px-4 font-black text-rose-600">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($ret->total_refund, 2) }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $ret->reason }}</td>
                            <td class="py-3 px-4 text-slate-700">{{ $ret->processor?->name ?? 'Staff' }}</td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('returns.show', $ret) }}" class="text-emerald-600 font-bold hover:underline">View &rarr;</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">No returns processed yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($returns->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $returns->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
