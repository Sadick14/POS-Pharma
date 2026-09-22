<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'PIMS') }} - @yield('title', 'Dashboard')</title>

    <!-- Tailwind CSS & FontAwesome CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        },
                        navy: {
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>
    @stack('styles')
</head>
<body class="h-full antialiased text-slate-800 bg-slate-50" x-data="{ sidebarOpen: false }">
    @php
        $user = auth()->user();
        $pharmacyName = \App\Models\Setting::get('pharmacy_name', 'HealthCare Plus Pharmacy');
        $currencySymbol = \App\Models\Setting::get('currency_symbol', 'GHS');
        $alertService = app(\App\Services\AlertService::class);
        $expiryCounts = $alertService->getExpiryCounts();
        $lowStockCount = $alertService->getLowStockMedicines()->count();
        $totalAlerts = $lowStockCount + $expiryCounts['expired'] + $expiryCounts['expiring_30'];
    @endphp

    <div class="min-h-full flex">
        <!-- Off-canvas sidebar for mobile -->
        <div x-cloak x-show="sidebarOpen" class="fixed inset-0 flex z-40 lg:hidden" role="dialog" aria-modal="true">
            <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/80" @click="sidebarOpen = false"></div>

            <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="relative flex-1 flex flex-col max-w-xs w-full bg-slate-900 pt-5 pb-4">
                <div class="absolute top-0 right-0 -mr-12 pt-2">
                    <button type="button" @click="sidebarOpen = false" class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none text-white">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                @include('layouts.navigation-links')
            </div>
        </div>

        <!-- Static sidebar for desktop -->
        <div class="hidden lg:flex lg:w-64 lg:flex-col lg:fixed lg:inset-y-0 bg-slate-900 border-r border-slate-800 z-30">
            <div class="flex flex-col flex-grow pt-5 pb-4 overflow-y-auto">
                <div class="flex items-center flex-shrink-0 px-6 gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center text-white text-xl shadow-md shadow-emerald-900">
                        <i class="fa-solid fa-prescription-bottle-medical"></i>
                    </div>
                    <div>
                        <h1 class="text-sm font-bold text-white tracking-wide leading-tight">HealthCare Plus</h1>
                        <span class="text-[10px] text-emerald-400 font-semibold tracking-wider uppercase">Pharmacy Management</span>
                    </div>
                </div>

                <div class="mt-4 px-4">
                    <div class="px-3 py-2 bg-slate-800/80 rounded-xl border border-slate-700/60 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
                            <span class="text-xs text-slate-300 font-medium">Currency: {{ $currencySymbol }}</span>
                        </div>
                        <span class="text-[10px] uppercase tracking-wider font-bold bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded-md">FEFO Active</span>
                    </div>
                </div>

                @include('layouts.navigation-links')
            </div>
        </div>

        <!-- Main content area -->
        <div class="lg:pl-64 flex flex-col flex-1 min-w-0">
            <!-- Top navbar -->
            <div class="sticky top-0 z-20 flex-shrink-0 flex h-16 bg-white border-b border-slate-200 shadow-sm px-4 sm:px-6 lg:px-8 items-center justify-between no-print">
                <button type="button" @click="sidebarOpen = true" class="lg:hidden text-slate-500 hover:text-slate-700 focus:outline-none">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>

                <div class="flex items-center gap-3">
                    <h2 class="text-lg font-bold text-slate-800 tracking-tight hidden sm:block">
                        @yield('page-title', 'Dashboard')
                    </h2>
                </div>

                <div class="flex items-center gap-3 sm:gap-4">
                    <!-- POS Quick Access Button -->
                    @if($user && $user->canProcessSales())
                        <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm shadow-emerald-200 transition">
                            <i class="fa-solid fa-cash-register"></i>
                            <span class="hidden sm:inline">Open POS Terminal</span>
                        </a>
                    @endif

                    <!-- Alerts Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="relative p-2 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition focus:outline-none">
                            <i class="fa-regular fa-bell text-lg"></i>
                            @if($totalAlerts > 0)
                                <span class="absolute top-1 right-1 flex h-4 w-4">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-4 w-4 bg-rose-600 text-[9px] font-bold text-white items-center justify-center">{{ $totalAlerts }}</span>
                                </span>
                            @endif
                        </button>

                        <div x-cloak x-show="open" @click.outside="open = false" x-transition class="origin-top-right absolute right-0 mt-2 w-80 rounded-2xl shadow-xl bg-white ring-1 ring-black ring-opacity-5 divide-y divide-slate-100 focus:outline-none z-50">
                            <div class="p-4 flex items-center justify-between">
                                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Inventory Alerts</h4>
                                <span class="text-[10px] font-semibold bg-rose-100 text-rose-700 px-2 py-0.5 rounded-full">{{ $totalAlerts }} Action(s) Required</span>
                            </div>
                            <div class="p-2 space-y-1 text-xs">
                                @if($lowStockCount > 0)
                                    <a href="{{ route('inventory.low-stock') }}" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-amber-50 text-amber-900 group transition">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-700">
                                                <i class="fa-solid fa-triangle-exclamation"></i>
                                            </div>
                                            <div>
                                                <p class="font-bold">Low Stock Medicines</p>
                                                <p class="text-[10px] text-slate-500">{{ $lowStockCount }} items below reorder level</p>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-chevron-right text-slate-400 group-hover:translate-x-0.5 transition"></i>
                                    </a>
                                @endif

                                @if($expiryCounts['expired'] > 0)
                                    <a href="{{ route('inventory.expiries', ['range' => 'expired']) }}" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-rose-50 text-rose-900 group transition">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center text-rose-700">
                                                <i class="fa-solid fa-ban"></i>
                                            </div>
                                            <div>
                                                <p class="font-bold">Expired Batches</p>
                                                <p class="text-[10px] text-slate-500">{{ $expiryCounts['expired'] }} batches past expiry</p>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-chevron-right text-slate-400 group-hover:translate-x-0.5 transition"></i>
                                    </a>
                                @endif

                                @if($expiryCounts['expiring_30'] > 0)
                                    <a href="{{ route('inventory.expiries', ['range' => '30']) }}" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-orange-50 text-orange-900 group transition">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center text-orange-700">
                                                <i class="fa-solid fa-clock-rotate-left"></i>
                                            </div>
                                            <div>
                                                <p class="font-bold">Expiring Soon (&lt; 30 Days)</p>
                                                <p class="text-[10px] text-slate-500">{{ $expiryCounts['expiring_30'] }} batches need FEFO monitoring</p>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-chevron-right text-slate-400 group-hover:translate-x-0.5 transition"></i>
                                    </a>
                                @endif

                                @if($totalAlerts === 0)
                                    <div class="py-6 text-center text-slate-400">
                                        <i class="fa-regular fa-circle-check text-2xl text-emerald-500 mb-2"></i>
                                        <p class="text-xs font-semibold text-slate-700">All Stocks Healthy</p>
                                        <p class="text-[10px]">No expired or critically low inventory items.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="relative" x-data="{ userMenu: false }">
                        <button @click="userMenu = !userMenu" class="flex items-center gap-2.5 p-1.5 rounded-xl hover:bg-slate-100 transition focus:outline-none">
                            <div class="w-8 h-8 rounded-xl bg-slate-800 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                                {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
                            </div>
                            <div class="text-left hidden md:block">
                                <p class="text-xs font-bold text-slate-800 leading-tight">{{ $user->name }}</p>
                                <span class="inline-block text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded">{{ $user->role_name }}</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-slate-400 text-xs hidden md:block"></i>
                        </button>

                        <div x-cloak x-show="userMenu" @click.outside="userMenu = false" x-transition class="origin-top-right absolute right-0 mt-2 w-52 rounded-2xl shadow-xl bg-white ring-1 ring-black ring-opacity-5 divide-y divide-slate-100 focus:outline-none z-50">
                            <div class="p-3">
                                <p class="text-xs font-bold text-slate-800">{{ $user->name }}</p>
                                <p class="text-[11px] text-slate-500 truncate">{{ $user->email }}</p>
                                <span class="mt-1.5 inline-block text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">{{ $user->role_name }}</span>
                            </div>
                            <div class="py-1 text-xs">
                                <a href="{{ route('profile') }}" class="flex items-center gap-2.5 px-4 py-2 text-slate-700 hover:bg-slate-50">
                                    <i class="fa-regular fa-id-badge text-slate-400"></i> My Profile
                                </a>
                                @if($user->isAdmin())
                                    <a href="{{ route('settings.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-slate-700 hover:bg-slate-50">
                                        <i class="fa-solid fa-sliders text-slate-400"></i> System Settings
                                    </a>
                                @endif
                            </div>
                            <div class="py-1 text-xs">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-rose-600 hover:bg-rose-50 text-left font-semibold">
                                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Sign Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Alerts & Flash messages -->
            <main class="flex-1 pb-12">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
                    @if(session('success'))
                        <div class="mb-5 rounded-2xl bg-emerald-50 p-4 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-sm">
                            <div class="flex items-center gap-2.5">
                                <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                                <span>{{ session('success') }}</span>
                            </div>
                            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-5 rounded-2xl bg-rose-50 p-4 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center justify-between shadow-sm">
                            <div class="flex items-center gap-2.5">
                                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base"></i>
                                <span>{{ session('error') }}</span>
                            </div>
                            <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-5 rounded-2xl bg-rose-50 p-4 border border-rose-200 text-rose-800 text-xs shadow-sm">
                            <div class="flex items-center gap-2 font-bold mb-1">
                                <i class="fa-solid fa-circle-xmark text-rose-600"></i> Please resolve the following errors:
                            </div>
                            <ul class="list-disc list-inside space-y-0.5 ml-5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
