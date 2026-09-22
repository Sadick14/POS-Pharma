@extends('layouts.app')

@section('title', 'Stock Overview')
@section('page-title', 'Inventory Stock Balances')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Inventory Stock Overview</h1>
            <p class="text-xs text-slate-500">Live active stock aggregated across non-expired FEFO batches.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('inventory.batches') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold transition">
                <i class="fa-solid fa-barcode mr-1"></i> Batch Ledger
            </a>
            @if(auth()->user()->canAdjustStock())
                <a href="{{ route('inventory.adjustments.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                    <i class="fa-solid fa-sliders mr-1"></i> Stock Adjustment
                </a>
            @endif
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('inventory.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-8 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search medicine, generic, barcode..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="sm:col-span-4 flex gap-2">
                <select name="category_id" class="flex-1 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/75 text-slate-500 border-b border-slate-200 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">Medicine Name</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Active Batches</th>
                        <th class="py-3 px-4">Available Qty</th>
                        <th class="py-3 px-4">Min Reorder</th>
                        <th class="py-3 px-4">Stock Status</th>
                        <th class="py-3 px-4 text-right">Earliest Expiry</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($medicines as $med)
                        @php
                            $activeQty = $med->current_stock;
                            $isLow = $activeQty <= $med->reorder_level;
                            $earliest = $med->earliest_expiry_batch;
                        @endphp
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                <a href="{{ route('medicines.show', $med) }}" class="hover:text-emerald-600">{{ $med->name }}</a>
                                <div class="text-[10px] text-slate-400">{{ $med->dosage_form }} • {{ $med->strength }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $med->category?->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[10px]">
                                    {{ $med->activeBatches->count() }} batch(es)
                                </span>
                            </td>
                            <td class="py-3 px-4 font-black text-sm {{ $isLow ? 'text-amber-600' : 'text-slate-900' }}">
                                {{ $activeQty }} {{ $med->unit }}
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ $med->reorder_level }} {{ $med->unit }}</td>
                            <td class="py-3 px-4">
                                @if($activeQty === 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-rose-100 text-rose-800">Out of Stock</span>
                                @elseif($isLow)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-100 text-amber-800">Low Stock</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">Adequate</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if($earliest)
                                    <span class="font-mono {{ $earliest->days_until_expiry <= 30 ? 'text-rose-600 font-bold' : 'text-slate-700' }}">
                                        {{ $earliest->expiry_date->format('Y-m-d') }}
                                        ({{ $earliest->days_until_expiry }}d)
                                    </span>
                                @else
                                    <span class="text-slate-400">No active batch</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No inventory items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($medicines->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $medicines->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
