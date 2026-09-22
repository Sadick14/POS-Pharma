@extends('layouts.app')

@section('title', 'Customers')
@section('page-title', 'Customer Profiles')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Customers Directory</h1>
            <p class="text-xs text-slate-500">Registered patients and retail pharmacy clients.</p>
        </div>
        <a href="{{ route('customers.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-900/20 transition">
            <i class="fa-solid fa-user-plus"></i> Add Customer
        </a>
    </div>

    <!-- Filter -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('customers.index') }}" class="flex gap-3">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by customer name, phone, email..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition">
                Search
            </button>
            <a href="{{ route('customers.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center justify-center">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/75 text-slate-500 border-b border-slate-200 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">Customer Name</th>
                        <th class="py-3 px-4">Phone Number</th>
                        <th class="py-3 px-4">Email Address</th>
                        <th class="py-3 px-4">Address</th>
                        <th class="py-3 px-4">Total Purchases</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $c)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                <a href="{{ route('customers.show', $c) }}" class="hover:text-emerald-600">{{ $c->name }}</a>
                            </td>
                            <td class="py-3 px-4 text-slate-700">{{ $c->phone ?? 'N/A' }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $c->email ?? 'N/A' }}</td>
                            <td class="py-3 px-4 text-slate-500 max-w-xs truncate">{{ $c->address ?? 'N/A' }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $c->sales_count }} order(s)</td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('customers.show', $c) }}" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100" title="View">
                                        <i class="fa-regular fa-eye"></i>
                                    </a>
                                    <a href="{{ route('customers.edit', $c) }}" class="p-1.5 text-emerald-600 hover:text-emerald-800 rounded-lg hover:bg-emerald-50" title="Edit">
                                        <i class="fa-solid fa-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">No customers registered yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
