@extends('layouts.app')

@section('title', $medicine->name)
@section('page-title', 'Medicine Profile & Batches')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $medicine->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                    {{ $medicine->status }}
                </span>
                <span class="text-xs text-slate-500">{{ $medicine->category?->name ?? 'Uncategorized' }}</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900">{{ $medicine->name }}</h1>
            <p class="text-xs text-slate-500">Generic: {{ $medicine->generic_name ?? 'N/A' }} • Brand: {{ $medicine->brand_name ?? 'N/A' }}</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('medicines.edit', $medicine) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                <i class="fa-solid fa-pencil mr-1"></i> Edit Record
            </a>
            <a href="{{ route('medicines.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                Catalog
            </a>
        </div>
    </div>

    <!-- Medicine Info Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Active Stock</span>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ $medicine->current_stock }} {{ $medicine->unit }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Reorder Level: {{ $medicine->reorder_level }}</p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Selling Price</span>
            <p class="text-2xl font-black text-emerald-700 mt-1">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($medicine->selling_price, 2) }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Per {{ $medicine->unit }}</p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Formulation</span>
            <p class="text-base font-bold text-slate-800 mt-1">{{ $medicine->dosage_form }} ({{ $medicine->strength }})</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Manufacturer: {{ $medicine->manufacturer ?? 'N/A' }}</p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Barcode</span>
            <p class="text-base font-mono font-bold text-slate-800 mt-1">{{ $medicine->barcode ?? 'None' }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Registered {{ $medicine->created_at->format('d M Y') }}</p>
        </div>
    </div>

    <!-- Active Batches Ledger (FEFO Priority) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Batches & Expiry (FEFO Ordered)</h3>
                <p class="text-[11px] text-slate-500">Batches sorted by earliest expiration date</p>
            </div>
            @if(auth()->user()->canAdjustStock())
                <a href="{{ route('inventory.adjustments.create') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">
                    <i class="fa-solid fa-sliders mr-1"></i> Stock Adjustment
                </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/75 text-slate-500 border-b border-slate-200 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">Batch Number</th>
                        <th class="py-3 px-4">Supplier</th>
                        <th class="py-3 px-4">Available Qty</th>
                        <th class="py-3 px-4">Cost Price</th>
                        <th class="py-3 px-4">Selling Price</th>
                        <th class="py-3 px-4">Expiry Date</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($medicine->batches as $batch)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $batch->batch_number }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $batch->supplier?->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $batch->quantity }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($batch->purchase_price, 2) }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($batch->selling_price, 2) }}</td>
                            <td class="py-3 px-4">
                                @if($batch->is_expired)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        Expired ({{ $batch->expiry_date->format('Y-m-d') }})
                                    </span>
                                @elseif($batch->days_until_expiry <= 30)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-800">
                                        {{ $batch->expiry_date->format('Y-m-d') }} ({{ $batch->days_until_expiry }}d left)
                                    </span>
                                @else
                                    <span class="text-slate-700 font-medium">{{ $batch->expiry_date->format('Y-m-d') }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $batch->status === 'active' && $batch->quantity > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $batch->quantity === 0 ? 'Depleted' : ($batch->is_expired ? 'Expired' : $batch->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">No batches received for this medicine yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
