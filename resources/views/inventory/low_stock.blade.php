@extends('layouts.app')

@section('title', 'Low Stock Alert')
@section('page-title', 'Low Stock & Reorder Alerts')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Low Stock Management</h1>
            <p class="text-xs text-slate-500">Medicines where total active stock is at or below the minimum reorder threshold.</p>
        </div>
        @if(auth()->user()->canProcessPurchases())
            <a href="{{ route('purchases.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-900/20 transition">
                <i class="fa-solid fa-cart-flatbed"></i> Create Purchase Order
            </a>
        @endif
    </div>

    <!-- Low Stock Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-amber-50/60 text-amber-900 border-b border-amber-200 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">Medicine Name</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Current Active Stock</th>
                        <th class="py-3 px-4">Reorder Threshold</th>
                        <th class="py-3 px-4">Deficit Units</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($medicines as $med)
                        @php
                            $deficit = max(0, $med->reorder_level - $med->current_stock);
                        @endphp
                        <tr class="hover:bg-amber-50/20 transition">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                <a href="{{ route('medicines.show', $med) }}" class="hover:text-emerald-600">{{ $med->name }}</a>
                                <div class="text-[10px] text-slate-400">{{ $med->dosage_form }} • {{ $med->strength }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $med->category?->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4 font-black text-sm text-amber-600">
                                {{ $med->current_stock }} {{ $med->unit }}
                            </td>
                            <td class="py-3 px-4 text-slate-600 font-bold">{{ $med->reorder_level }} {{ $med->unit }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                    -{{ $deficit }} {{ $med->unit }} needed
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if(auth()->user()->canProcessPurchases())
                                    <a href="{{ route('purchases.create') }}" class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 hover:text-emerald-700">
                                        <i class="fa-solid fa-cart-shopping"></i> Reorder
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400">
                                <i class="fa-regular fa-circle-check text-3xl text-emerald-500 mb-2"></i>
                                <p class="text-sm font-semibold text-slate-700">All Stock Levels Healthy</p>
                                <p class="text-xs text-slate-400">No medicines are currently at or below their reorder threshold.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
