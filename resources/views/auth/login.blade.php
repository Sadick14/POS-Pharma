@extends('layouts.guest')

@section('title', 'Sign In')

@section('content')
<div class="sm:mx-auto sm:w-full sm:max-w-md">
    <div class="flex justify-center items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-emerald-600 flex items-center justify-center text-white text-2xl shadow-lg shadow-emerald-200">
            <i class="fa-solid fa-prescription-bottle-medical"></i>
        </div>
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 leading-none">HealthCare Plus</h2>
            <p class="text-xs text-emerald-600 font-semibold uppercase tracking-wider mt-1">Pharmacy Inventory System</p>
        </div>
    </div>
</div>

<div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
    <div class="bg-white py-8 px-6 shadow-xl shadow-slate-200/60 rounded-2xl border border-slate-100 sm:px-10" x-data="{ email: '{{ old('email', 'admin@pharmacy.com') }}', password: 'password123' }">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-slate-900">Sign in to your account</h3>
            <p class="text-xs text-slate-500 mt-1">Enter your credentials to access the pharmacy workspace.</p>
        </div>

        @if (session('error'))
            <div class="mb-4 rounded-xl bg-rose-50 p-4 text-xs font-medium text-rose-700 border border-rose-100 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-sm"></i>
                {{ session('error') }}
            </div>
        @endif

        @if (session('info'))
            <div class="mb-4 rounded-xl bg-sky-50 p-4 text-xs font-medium text-sky-700 border border-sky-100 flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-sm"></i>
                {{ session('info') }}
            </div>
        @endif

        <form class="space-y-4" action="{{ route('login') }}" method="POST">
            @csrf

            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">Email address</label>
                <div class="mt-1 relative rounded-xl shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-regular fa-envelope"></i>
                    </div>
                    <input id="email" name="email" type="email" autocomplete="email" required x-model="email"
                           class="block w-full pl-10 pr-3 py-2.5 sm:text-sm rounded-xl border @error('email') border-rose-300 text-rose-900 focus:ring-rose-500 focus:border-rose-500 @else border-slate-200 focus:ring-emerald-500 focus:border-emerald-500 @enderror shadow-sm focus:outline-none focus:ring-2 placeholder-slate-400"
                           placeholder="you@pharmacy.com">
                </div>
                @error('email')
                    <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1"><i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">Password</label>
                <div class="mt-1 relative rounded-xl shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <input id="password" name="password" type="password" autocomplete="current-password" required x-model="password"
                           class="block w-full pl-10 pr-3 py-2.5 sm:text-sm rounded-xl border border-slate-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 placeholder-slate-400"
                           placeholder="••••••••">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                           class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-slate-300 rounded">
                    <label for="remember" class="ml-2 block text-xs text-slate-600">Remember me</label>
                </div>
            </div>

            <div>
                <button type="submit"
                        class="w-full flex justify-center items-center gap-2 py-3 px-4 border border-transparent rounded-xl shadow-md text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-150">
                    <i class="fa-solid fa-right-to-bracket"></i> Sign In to PIMS
                </button>
            </div>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-100">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 text-center mb-3">Quick Demo Login as:</p>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <button type="button" @click="email = 'admin@pharmacy.com'; password = 'password123'"
                        class="p-2 bg-slate-50 hover:bg-emerald-50 hover:text-emerald-700 border border-slate-200 rounded-lg text-left transition">
                    <span class="font-bold block">Administrator</span>
                    <span class="text-[10px] text-slate-500">Full System Access</span>
                </button>
                <button type="button" @click="email = 'pharmacist@pharmacy.com'; password = 'password123'"
                        class="p-2 bg-slate-50 hover:bg-emerald-50 hover:text-emerald-700 border border-slate-200 rounded-lg text-left transition">
                    <span class="font-bold block">Pharmacist</span>
                    <span class="text-[10px] text-slate-500">Sales & Inventory</span>
                </button>
                <button type="button" @click="email = 'cashier@pharmacy.com'; password = 'password123'"
                        class="p-2 bg-slate-50 hover:bg-emerald-50 hover:text-emerald-700 border border-slate-200 rounded-lg text-left transition">
                    <span class="font-bold block">Cashier</span>
                    <span class="text-[10px] text-slate-500">POS & Receipts</span>
                </button>
                <button type="button" @click="email = 'inventory@pharmacy.com'; password = 'password123'"
                        class="p-2 bg-slate-50 hover:bg-emerald-50 hover:text-emerald-700 border border-slate-200 rounded-lg text-left transition">
                    <span class="font-bold block">Inventory Officer</span>
                    <span class="text-[10px] text-slate-500">Stock & Batches</span>
                </button>
                <button type="button" @click="email = 'manager@pharmacy.com'; password = 'password123'"
                        class="p-2 bg-slate-50 hover:bg-emerald-50 hover:text-emerald-700 border border-slate-200 rounded-lg text-left transition">
                    <span class="font-bold block">Manager</span>
                    <span class="text-[10px] text-slate-500">Purchases & Sales</span>
                </button>
                <button type="button" @click="email = 'auditor@pharmacy.com'; password = 'password123'"
                        class="p-2 bg-slate-50 hover:bg-emerald-50 hover:text-emerald-700 border border-slate-200 rounded-lg text-left transition">
                    <span class="font-bold block">Auditor</span>
                    <span class="text-[10px] text-slate-500">Reports & Logs</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
