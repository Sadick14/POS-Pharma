@extends('layouts.app')

@section('title', 'Batch Ledger')
@section('page-title', 'Medicine Batches Ledger (FEFO)')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Batch Ledger (FEFO Priority)</h1>
            <p class="text-xs text-slate-500">Traceable batch entries with cost, selling price, and expiration countdowns.</p>
        </div>
        @if(auth()->user()->canProcessPurchases())
            <a href="{{ route('purchases.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-900/20 transition">
                <i class="fa-solid fa-cart-flatbed"></i> Receive Inward Batch
            </a>
        @endif
    </div>

    <!-- Filter -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('inventory.batches') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-8 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search batch number, medicine name..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="sm:col-span-4 flex gap-2">
                <select name="status" class="flex-1 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">All Batches</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active & Available</option>
                    <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>Expired Batches</option>
                    <option value="depleted" {{ $status === 'depleted' ? 'selected' : '' }}>Depleted (0 Stock)</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition">
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
                        <th class="py-3 px-4">Batch Number</th>
                        <th class="py-3 px-4">Medicine</th>
                        <th class="py-3 px-4">Supplier</th>
                        <th class="py-3 px-4">Remaining Qty</th>
                        <th class="py-3 px-4">Unit Cost</th>
                        <th class="py-3 px-4">Selling Price</th>
                        <th class="py-3 px-4">Expiry Date</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($batches as $batch)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $batch->batch_number }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">
                                <a href="{{ route('medicines.show', $batch->medicine) }}" class="hover:text-emerald-600">{{ $batch->medicine->name }}</a>
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $batch->supplier?->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4 font-black text-sm text-slate-900">{{ $batch->quantity }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($batch->purchase_price, 2) }}</td>
                            <td class="py-3 px-4 font-bold text-emerald-700">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($batch->selling_price, 2) }}</td>
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
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $batch->quantity > 0 && !$batch->is_expired ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $batch->quantity === 0 ? 'Depleted' : ($batch->is_expired ? 'Expired' : $batch->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">No batches match criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($batches->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $batches->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
