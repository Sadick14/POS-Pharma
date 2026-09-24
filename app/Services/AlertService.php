<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;

class AlertService
{
    /**
     * Get all medicines that are at or below reorder level.
     */
    public function getLowStockMedicines(): \Illuminate\Support\Collection
    {
        return Medicine::with(['category', 'activeBatches'])
            ->where('status', 'active')
            ->get()
            ->filter(function (Medicine $medicine) {
                return $medicine->current_stock <= $medicine->reorder_level;
            })
            ->values();
    }

    /**
     * Get expired batches that still have quantity > 0.
     */
    public function getExpiredBatches(): Collection
    {
        return MedicineBatch::with(['medicine', 'supplier'])
            ->where('expiry_date', '<', now()->toDateString())
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->get();
    }

    /**
     * Get batches expiring within a given number of days.
     */
    public function getExpiringBatches(int $days = 30): Collection
    {
        $today = now()->toDateString();
        $targetDate = now()->addDays($days)->toDateString();

        return MedicineBatch::with(['medicine', 'supplier'])
            ->where('quantity', '>', 0)
            ->where('expiry_date', '>=', $today)
            ->where('expiry_date', '<=', $targetDate)
            ->orderBy('expiry_date', 'asc')
            ->get();
    }

    /**
     * Detailed expiry breakdown counts for dashboard and alerts.
     */
    public function getExpiryCounts(): array
    {
        $today = now()->toDateString();
        $in30 = now()->addDays(30)->toDateString();
        $in60 = now()->addDays(60)->toDateString();
        $in90 = now()->addDays(90)->toDateString();

        $expired = MedicineBatch::where('quantity', '>', 0)->where('expiry_date', '<', $today)->count();
        $expiring30 = MedicineBatch::where('quantity', '>', 0)->whereBetween('expiry_date', [$today, $in30])->count();
        $expiring60 = MedicineBatch::where('quantity', '>', 0)->whereBetween('expiry_date', [$today, $in60])->count();
        $expiring90 = MedicineBatch::where('quantity', '>', 0)->whereBetween('expiry_date', [$today, $in90])->count();

        return [
            'expired' => $expired,
            'expiring_30' => $expiring30,
            'expiring_60' => $expiring60,
            'expiring_90' => $expiring90,
        ];
    }

    /**
     * Comprehensive dashboard metrics.
     */
    public function getDashboardMetrics(): array
    {
        $today = now()->toDateString();

        $totalMedicines = Medicine::count();
        $totalSuppliers = Supplier::where('status', 'active')->count();
        $totalCustomers = Customer::count();

        // Calculate total stock inventory valuation
        $activeBatches = MedicineBatch::where('quantity', '>', 0)
            ->where('expiry_date', '>=', $today)
            ->get();

        $totalInventoryCost = $activeBatches->sum(fn ($b) => $b->quantity * $b->purchase_price);
        $totalInventoryRetail = $activeBatches->sum(fn ($b) => $b->quantity * $b->selling_price);

        $lowStockCount = $this->getLowStockMedicines()->count();
        $expiryCounts = $this->getExpiryCounts();

        $todaySales = Sale::whereDate('sale_date', $today)->sum('total');
        $todaySalesCount = Sale::whereDate('sale_date', $today)->count();
        $todayPurchases = Purchase::whereDate('purchase_date', $today)->sum('total');

        // Recent sales
        $recentSales = Sale::with(['customer', 'seller', 'items.medicine'])
            ->latest('sale_date')
            ->take(6)
            ->get();

        // Recent stock movements
        $recentMovements = StockMovement::with(['medicine', 'batch', 'creator'])
            ->latest()
            ->take(8)
            ->get();

        return [
            'total_medicines' => $totalMedicines,
            'total_suppliers' => $totalSuppliers,
            'total_customers' => $totalCustomers,
            'total_inventory_cost' => $totalInventoryCost,
            'total_inventory_retail' => $totalInventoryRetail,
            'low_stock_count' => $lowStockCount,
            'expiry_counts' => $expiryCounts,
            'today_sales' => $todaySales,
            'today_sales_count' => $todaySalesCount,
            'today_purchases' => $todayPurchases,
            'recent_sales' => $recentSales,
            'recent_movements' => $recentMovements,
        ];
    }
}
