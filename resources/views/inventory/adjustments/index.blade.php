@extends('layouts.app')

@section('title', 'Stock Adjustments')
@section('page-title', 'Stock Adjustments Log')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Stock Adjustments History</h1>
            <p class="text-xs text-slate-500">Record of manual inventory adjustments, write-offs, and stock count corrections.</p>
        </div>
        @if(auth()->user()->canAdjustStock())
            <a href="{{ route('inventory.adjustments.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-900/20 transition">
                <i class="fa-solid fa-plus"></i> New Stock Adjustment
            </a>
        @endif
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/75 text-slate-500 border-b border-slate-200 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Medicine</th>
                        <th class="py-3 px-4">Batch Number</th>
                        <th class="py-3 px-4">Previous Qty</th>
                        <th class="py-3 px-4">Adjustment</th>
                        <th class="py-3 px-4">New Qty</th>
                        <th class="py-3 px-4">Reason</th>
                        <th class="py-3 px-4">Adjusted By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($adjustments as $adj)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono text-slate-600">{{ $adj->created_at->format('Y-m-d H:i') }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $adj->medicine->name }}</td>
                            <td class="py-3 px-4 font-mono text-slate-700">{{ $adj->batch?->batch_number ?? 'N/A' }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $adj->previous_quantity }}</td>
                            <td class="py-3 px-4 font-mono font-bold {{ $adj->adjustment_quantity > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $adj->adjustment_quantity > 0 ? '+' : '' }}{{ $adj->adjustment_quantity }}
                            </td>
                            <td class="py-3 px-4 font-black text-slate-900">{{ $adj->new_quantity }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800">
                                    {{ $adj->reason }}
                                </span>
                                @if($adj->notes)
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $adj->notes }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-700">{{ $adj->adjuster?->name ?? 'Staff' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">No stock adjustments recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($adjustments->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $adjustments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
