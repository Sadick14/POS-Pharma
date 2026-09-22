@extends('layouts.app')

@section('title', 'Medicines Catalog')
@section('page-title', 'Medicines Management')

@section('content')
<div class="space-y-6">
    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Medicines Catalog</h1>
            <p class="text-xs text-slate-500">Manage pharmaceutical formulations, reorder limits, and active batches.</p>
        </div>
        <a href="{{ route('medicines.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-900/20 transition">
            <i class="fa-solid fa-plus"></i> Add New Medicine
        </a>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('medicines.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, generic, brand, barcode..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="sm:col-span-3">
                <select name="category_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">All Statuses</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition">
                    Filter
                </button>
                <a href="{{ route('medicines.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center justify-center">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Medicines Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/75 text-slate-500 border-b border-slate-200 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">Medicine Details</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Dosage / Strength</th>
                        <th class="py-3 px-4">Active Stock</th>
                        <th class="py-3 px-4">Selling Price</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($medicines as $med)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900 text-sm">
                                    <a href="{{ route('medicines.show', $med) }}" class="hover:text-emerald-600">{{ $med->name }}</a>
                                </div>
                                <div class="text-[11px] text-slate-500">
                                    Generic: {{ $med->generic_name ?? 'N/A' }}
                                    @if($med->barcode)
                                        • Barcode: <span class="font-mono text-slate-700">{{ $med->barcode }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 font-medium">
                                    {{ $med->category?->name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                {{ $med->dosage_form }} • {{ $med->strength }}
                                <div class="text-[10px] text-slate-400">Unit: {{ $med->unit }}</div>
                            </td>
                            <td class="py-3 px-4">
                                @php $stock = $med->current_stock; @endphp
                                <span class="inline-flex items-center gap-1 font-bold {{ $stock <= $med->reorder_level ? 'text-amber-600' : 'text-emerald-600' }}">
                                    @if($stock <= $med->reorder_level)
                                        <i class="fa-solid fa-triangle-exclamation text-[10px]"></i>
                                    @endif
                                    {{ $stock }} {{ $med->unit }}
                                </span>
                                <div class="text-[10px] text-slate-400">Min Reorder: {{ $med->reorder_level }}</div>
                            </td>
                            <td class="py-3 px-4 font-extrabold text-slate-900">
                                {{ \App\Models\Setting::get('currency_symbol', 'GHS') }} {{ number_format($med->selling_price, 2) }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $med->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $med->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('medicines.show', $med) }}" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100" title="View Details">
                                        <i class="fa-regular fa-eye"></i>
                                    </a>
                                    <a href="{{ route('medicines.edit', $med) }}" class="p-1.5 text-emerald-600 hover:text-emerald-800 rounded-lg hover:bg-emerald-50" title="Edit">
                                        <i class="fa-solid fa-pencil"></i>
                                    </a>
                                    <form action="{{ route('medicines.destroy', $med) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this medicine?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 rounded-lg hover:bg-rose-50" title="Delete">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                <i class="fa-solid fa-pills text-3xl mb-2 text-slate-300"></i>
                                <p class="text-sm font-semibold">No medicines found</p>
                            </td>
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
