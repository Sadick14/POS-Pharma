<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Sales Report by date range, staff, payment method.
     */
    public function getSalesReport(?string $startDate = null, ?string $endDate = null, ?int $staffId = null, ?string $paymentMethod = null)
    {
        $query = Sale::with(['customer', 'seller', 'items.medicine'])
            ->when($startDate, fn ($q) => $q->whereDate('sale_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('sale_date', '<=', $endDate))
            ->when($staffId, fn ($q) => $q->where('sold_by', $staffId))
            ->when($paymentMethod, fn ($q) => $q->where('payment_method', $paymentMethod))
            ->orderBy('sale_date', 'desc');

        return $query->get();
    }

    /**
     * Sales grouped by medicine for product performance.
     */
    public function getSalesByMedicine(?string $startDate = null, ?string $endDate = null)
    {
        return SaleItem::join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('medicines', 'medicines.id', '=', 'sale_items.medicine_id')
            ->when($startDate, fn ($q) => $q->whereDate('sales.sale_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('sales.sale_date', '<=', $endDate))
            ->select(
                'medicines.id',
                'medicines.name',
                'medicines.generic_name',
                'medicines.dosage_form',
                'medicines.strength',
                DB::raw('SUM(sale_items.quantity) as total_qty_sold'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
                DB::raw('SUM(sale_items.quantity * sale_items.purchase_price) as total_cost'),
                DB::raw('SUM(sale_items.subtotal - (sale_items.quantity * sale_items.purchase_price)) as estimated_profit')
            )
            ->groupBy('medicines.id', 'medicines.name', 'medicines.generic_name', 'medicines.dosage_form', 'medicines.strength')
            ->orderByDesc('total_revenue')
            ->get();
    }

    /**
     * Current inventory valuation report (by batch).
     */
    public function getInventoryValuationReport()
    {
        return MedicineBatch::with(['medicine.category', 'supplier'])
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->map(function ($batch) {
                $costValuation = $batch->quantity * $batch->purchase_price;
                $retailValuation = $batch->quantity * $batch->selling_price;
                $potentialProfit = $retailValuation - $costValuation;

                return [
                    'batch_id' => $batch->id,
                    'medicine_name' => $batch->medicine->name,
                    'generic_name' => $batch->medicine->generic_name,
                    'category' => $batch->medicine->category?->name ?? 'Uncategorized',
                    'batch_number' => $batch->batch_number,
                    'supplier' => $batch->supplier?->name ?? 'N/A',
                    'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                    'is_expired' => $batch->is_expired,
                    'days_left' => $batch->days_until_expiry,
                    'quantity' => $batch->quantity,
                    'unit_cost' => $batch->purchase_price,
                    'unit_price' => $batch->selling_price,
                    'cost_valuation' => $costValuation,
                    'retail_valuation' => $retailValuation,
                    'potential_profit' => $potentialProfit,
                ];
            });
    }

    /**
     * Financial profit and loss overview.
     */
    public function getFinancialOverview(?string $startDate = null, ?string $endDate = null): array
    {
        $salesQuery = Sale::query()
            ->when($startDate, fn ($q) => $q->whereDate('sale_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('sale_date', '<=', $endDate));

        $totalGrossSales = (float) $salesQuery->sum('subtotal');
        $totalDiscounts = (float) $salesQuery->sum('discount');
        $totalNetSales = (float) $salesQuery->sum('total');

        $saleItemsQuery = SaleItem::join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->when($startDate, fn ($q) => $q->whereDate('sales.sale_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('sales.sale_date', '<=', $endDate));

        $costOfGoodsSold = (float) $saleItemsQuery->sum(DB::raw('sale_items.quantity * sale_items.purchase_price'));
        $grossProfit = $totalNetSales - $costOfGoodsSold;
        $profitMargin = $totalNetSales > 0 ? ($grossProfit / $totalNetSales) * 100 : 0;

        $purchasesQuery = Purchase::query()
            ->when($startDate, fn ($q) => $q->whereDate('purchase_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('purchase_date', '<=', $endDate));

        $totalPurchases = (float) $purchasesQuery->sum('total');

        return [
            'gross_sales' => $totalGrossSales,
            'discounts' => $totalDiscounts,
            'net_sales' => $totalNetSales,
            'cost_of_goods_sold' => $costOfGoodsSold,
            'gross_profit' => $grossProfit,
            'profit_margin' => $profitMargin,
            'total_purchases' => $totalPurchases,
        ];
    }
}
