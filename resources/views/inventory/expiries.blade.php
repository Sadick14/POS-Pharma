@extends('layouts.app')

@section('title', 'Expiry Monitoring')
@section('page-title', 'Medicine Expiry Management')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Expiry Monitoring & Prevention</h1>
            <p class="text-xs text-slate-500">Track batch expiration timelines to prevent waste and block sales of expired medicines.</p>
        </div>
        @if(auth()->user()->canAdjustStock())
            <a href="{{ route('inventory.adjustments.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-900/20 transition">
                <i class="fa-solid fa-trash-can"></i> Dispose Expired Stock
            </a>
        @endif
    </div>

    <!-- Alert Filter Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('inventory.expiries', ['range' => 'expired']) }}"
           class="p-4 rounded-2xl border transition {{ $range === 'expired' ? 'bg-rose-600 text-white border-rose-600 shadow-md' : 'bg-white text-slate-800 border-slate-200 hover:border-rose-300' }}">
            <div class="flex justify-between items-center text-xs font-bold uppercase">
                <span>Expired Stock</span>
                <i class="fa-solid fa-ban"></i>
            </div>
            <p class="text-2xl font-black mt-2">{{ $expiryCounts['expired'] }}</p>
            <p class="text-[10px] {{ $range === 'expired' ? 'text-rose-100' : 'text-slate-400' }}">Cannot be sold (Disposal)</p>
        </a>

        <a href="{{ route('inventory.expiries', ['range' => '30']) }}"
           class="p-4 rounded-2xl border transition {{ $range == '30' ? 'bg-orange-500 text-white border-orange-500 shadow-md' : 'bg-white text-slate-800 border-slate-200 hover:border-orange-300' }}">
            <div class="flex justify-between items-center text-xs font-bold uppercase">
                <span>&lt; 30 Days</span>
                <i class="fa-solid fa-stopwatch"></i>
            </div>
            <p class="text-2xl font-black mt-2">{{ $expiryCounts['expiring_30'] }}</p>
            <p class="text-[10px] {{ $range == '30' ? 'text-orange-100' : 'text-slate-400' }}">Urgent FEFO dispensing</p>
        </a>

        <a href="{{ route('inventory.expiries', ['range' => '60']) }}"
           class="p-4 rounded-2xl border transition {{ $range == '60' ? 'bg-amber-500 text-white border-amber-500 shadow-md' : 'bg-white text-slate-800 border-slate-200 hover:border-amber-300' }}">
            <div class="flex justify-between items-center text-xs font-bold uppercase">
                <span>&lt; 60 Days</span>
                <i class="fa-solid fa-clock"></i>
            </div>
            <p class="text-2xl font-black mt-2">{{ $expiryCounts['expiring_60'] }}</p>
            <p class="text-[10px] {{ $range == '60' ? 'text-amber-100' : 'text-slate-400' }}">High priority queue</p>
        </a>

        <a href="{{ route('inventory.expiries', ['range' => '90']) }}"
           class="p-4 rounded-2xl border transition {{ $range == '90' ? 'bg-sky-600 text-white border-sky-600 shadow-md' : 'bg-white text-slate-800 border-slate-200 hover:border-sky-300' }}">
            <div class="flex justify-between items-center text-xs font-bold uppercase">
                <span>&lt; 90 Days</span>
                <i class="fa-solid fa-calendar-day"></i>
            </div>
            <p class="text-2xl font-black mt-2">{{ $expiryCounts['expiring_90'] }}</p>
            <p class="text-[10px] {{ $range == '90' ? 'text-sky-100' : 'text-slate-400' }}">Upcoming quarterly horizon</p>
        </a>
    </div>

    <!-- Batches Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/75 text-slate-500 border-b border-slate-200 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">Medicine</th>
                        <th class="py-3 px-4">Batch Number</th>
                        <th class="py-3 px-4">Supplier</th>
                        <th class="py-3 px-4">Remaining Qty</th>
                        <th class="py-3 px-4">Expiry Date</th>
                        <th class="py-3 px-4">Days Remaining</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($batches as $b)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                <a href="{{ route('medicines.show', $b->medicine) }}" class="hover:text-emerald-600">{{ $b->medicine->name }}</a>
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-700">{{ $b->batch_number }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $b->supplier?->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4 font-black text-slate-900">{{ $b->quantity }}</td>
                            <td class="py-3 px-4 font-bold font-mono {{ $b->is_expired ? 'text-rose-600' : 'text-orange-600' }}">
                                {{ $b->expiry_date->format('Y-m-d') }}
                            </td>
                            <td class="py-3 px-4">
                                @if($b->is_expired)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        Expired ({{ abs($b->days_until_expiry) }} days ago)
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-800">
                                        {{ $b->days_until_expiry }} days left
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if(auth()->user()->canAdjustStock())
                                    <a href="{{ route('inventory.adjustments.create') }}" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">
                                        Adjust / Write-off
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No batches match this expiry window.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
