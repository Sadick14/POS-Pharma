@extends('layouts.app')

@section('title', 'Expiry Analysis')
@section('page-title', 'Expiry Reports & Analysis')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Expiry Forecast & Analysis</h1>
            <p class="text-xs text-slate-500">Monitoring upcoming expiring medicines to enforce FEFO queue and minimize write-offs.</p>
        </div>
        <a href="{{ route('reports.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
            Reports Directory
        </a>
    </div>

    <!-- Expired Stock Breakdown -->
    @if($expiredBatches->count() > 0)
        <div class="bg-rose-50/60 rounded-3xl border border-rose-200 p-6 space-y-3">
            <div class="flex items-center gap-2 text-rose-800 font-bold text-sm">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>Expired Batches Requiring Disposal / Write-off ({{ $expiredBatches->count() }} batches)</span>
            </div>
            <div class="overflow-x-auto bg-white rounded-2xl border border-rose-200 shadow-xs">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-rose-100/50 text-rose-900 font-bold uppercase text-[10px]">
                            <th class="py-2.5 px-3">Medicine</th>
                            <th class="py-2.5 px-3">Batch Number</th>
                            <th class="py-2.5 px-3">Expired Date</th>
                            <th class="py-2.5 px-3 text-center">Remaining Quantity</th>
                            <th class="py-2.5 px-3 text-right">Cost Loss</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-rose-100">
                        @foreach($expiredBatches as $eb)
                            <tr>
                                <td class="py-2.5 px-3 font-bold text-slate-900">{{ $eb->medicine->name }}</td>
                                <td class="py-2.5 px-3 font-mono font-bold text-rose-700">{{ $eb->batch_number }}</td>
                                <td class="py-2.5 px-3 font-mono text-rose-600">{{ $eb->expiry_date->format('Y-m-d') }}</td>
                                <td class="py-2.5 px-3 font-black text-center text-slate-900">{{ $eb->quantity }}</td>
                                <td class="py-2.5 px-3 text-right font-bold text-rose-700">{{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($eb->quantity * $eb->purchase_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Batches Expiring Within Selected Window -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Batches Expiring Soon (FEFO Queue)</h3>
                <p class="text-[11px] text-slate-500">Sorted by earliest expiration date</p>
            </div>
            <div class="flex gap-2 text-xs">
                <a href="{{ route('reports.expiries', ['range' => 30]) }}" class="px-3 py-1.5 rounded-lg font-bold {{ $range == 30 ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700' }}">30 Days</a>
                <a href="{{ route('reports.expiries', ['range' => 60]) }}" class="px-3 py-1.5 rounded-lg font-bold {{ $range == 60 ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700' }}">60 Days</a>
                <a href="{{ route('reports.expiries', ['range' => 90]) }}" class="px-3 py-1.5 rounded-lg font-bold {{ $range == 90 ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700' }}">90 Days</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/75 text-slate-500 border-b border-slate-200 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">Medicine</th>
                        <th class="py-3 px-4">Batch Number</th>
                        <th class="py-3 px-4">Supplier</th>
                        <th class="py-3 px-4 text-center">Remaining Quantity</th>
                        <th class="py-3 px-4">Expiry Date</th>
                        <th class="py-3 px-4">Days Left</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($batches as $b)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $b->medicine->name }}</td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-700">{{ $b->batch_number }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $b->supplier?->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4 font-black text-center text-slate-900">{{ $b->quantity }}</td>
                            <td class="py-3 px-4 font-mono font-bold text-orange-600">{{ $b->expiry_date->format('Y-m-d') }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-800">
                                    {{ $b->days_until_expiry }} days left
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">No batches expiring within {{ $range }} days.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
