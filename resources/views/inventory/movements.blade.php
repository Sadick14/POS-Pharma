@extends('layouts.app')

@section('title', 'Stock Movement Ledger')
@section('page-title', 'Stock Movements Audit Ledger')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Stock Movement Ledger</h1>
            <p class="text-xs text-slate-500">Immutable ledger tracking all stock additions, sales deductions, adjustments, and returns.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('inventory.movements') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-4">
                <select name="medicine_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">All Medicines</option>
                    @foreach($medicines as $m)
                        <option value="{{ $m->id }}" {{ $medicineId == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3">
                <select name="type" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">All Movement Types</option>
                    <option value="purchase_in" {{ $type === 'purchase_in' ? 'selected' : '' }}>Purchase Inward (+)</option>
                    <option value="sale_out" {{ $type === 'sale_out' ? 'selected' : '' }}>POS Sale Out (-)</option>
                    <option value="adjustment_in" {{ $type === 'adjustment_in' ? 'selected' : '' }}>Adjustment In (+)</option>
                    <option value="adjustment_out" {{ $type === 'adjustment_out' ? 'selected' : '' }}>Adjustment Out (-)</option>
                    <option value="return_in" {{ $type === 'return_in' ? 'selected' : '' }}>Sales Return Restock (+)</option>
                    <option value="disposal_out" {{ $type === 'disposal_out' ? 'selected' : '' }}>Disposal / Damage Write-off (-)</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white">
            </div>

            <div class="sm:col-span-2">
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white">
            </div>

            <div class="sm:col-span-1">
                <button type="submit" class="w-full px-3 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/75 text-slate-500 border-b border-slate-200 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">Date & Time</th>
                        <th class="py-3 px-4">Medicine</th>
                        <th class="py-3 px-4">Batch Number</th>
                        <th class="py-3 px-4">Movement Type</th>
                        <th class="py-3 px-4">Quantity Change</th>
                        <th class="py-3 px-4">Description</th>
                        <th class="py-3 px-4">Staff</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movements as $mov)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 text-slate-600 font-mono">{{ $mov->created_at->format('Y-m-d H:i') }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $mov->medicine->name }}</td>
                            <td class="py-3 px-4 font-mono text-slate-700">{{ $mov->batch?->batch_number ?? 'N/A' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $mov->quantity > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ str_replace('_', ' ', $mov->type) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-mono font-black text-sm {{ $mov->quantity > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $mov->quantity > 0 ? '+' : '' }}{{ $mov->quantity }}
                            </td>
                            <td class="py-3 px-4 text-slate-600 max-w-sm">{{ $mov->description }}</td>
                            <td class="py-3 px-4 text-slate-700">{{ $mov->creator?->name ?? 'System' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No stock movement logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
