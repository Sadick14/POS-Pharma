<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesReturnController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // AJAX APIs
    Route::get('/api/medicines/search', [MedicineController::class, 'search'])->name('api.medicines.search');
    Route::post('/api/customers/quick-store', [CustomerController::class, 'quickStore'])->name('api.customers.quick-store');

    // POS & Sales
    Route::middleware('role:admin,pharmacist,manager,cashier')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
        Route::get('/pos/receipt/{sale}', [PosController::class, 'receipt'])->name('pos.receipt');
        Route::get('/sales', [PosController::class, 'salesList'])->name('sales.index');
        Route::get('/sales/{sale}', [PosController::class, 'showSale'])->name('sales.show');
    });

    // Sales Returns
    Route::middleware('role:admin,manager,pharmacist')->group(function () {
        Route::get('/returns', [SalesReturnController::class, 'index'])->name('returns.index');
        Route::get('/sales/{sale}/return', [SalesReturnController::class, 'create'])->name('returns.create');
        Route::post('/sales/{sale}/return', [SalesReturnController::class, 'store'])->name('returns.store');
        Route::get('/returns/{return}', [SalesReturnController::class, 'show'])->name('returns.show');
    });

    // Medicines & Categories
    Route::middleware('role:admin,manager,pharmacist,inventory_officer')->group(function () {
        Route::resource('medicines', MedicineController::class);
        Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
    });

    // Inventory & Batch Management
    Route::middleware('role:admin,manager,pharmacist,inventory_officer')->group(function () {
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/batches', [InventoryController::class, 'batches'])->name('inventory.batches');
        Route::get('/inventory/low-stock', [InventoryController::class, 'lowStock'])->name('inventory.low-stock');
        Route::get('/inventory/expiries', [InventoryController::class, 'expiries'])->name('inventory.expiries');
        Route::get('/inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
    });

    // Stock Adjustments
    Route::middleware('role:admin,manager,inventory_officer')->group(function () {
        Route::get('/inventory/adjustments', [InventoryController::class, 'adjustments'])->name('inventory.adjustments');
        Route::get('/inventory/adjustments/create', [InventoryController::class, 'createAdjustment'])->name('inventory.adjustments.create');
        Route::post('/inventory/adjustments', [InventoryController::class, 'storeAdjustment'])->name('inventory.adjustments.store');
    });

    // Procurement & Suppliers
    Route::middleware('role:admin,manager,inventory_officer')->group(function () {
        Route::resource('suppliers', SupplierController::class);
        Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);
    });

    // Customers
    Route::middleware('role:admin,manager,pharmacist,cashier')->group(function () {
        Route::resource('customers', CustomerController::class);
    });

    // Reports & Analytics
    Route::middleware('role:admin,manager,auditor')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('/reports/expiries', [ReportController::class, 'expiries'])->name('reports.expiries');
        Route::get('/reports/profit', [ReportController::class, 'profit'])->name('reports.profit');
        Route::get('/reports/purchases', [ReportController::class, 'purchases'])->name('reports.purchases');
    });

    // Audit Logs
    Route::middleware('role:admin,auditor')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

    // Administration (Users & Settings)
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
