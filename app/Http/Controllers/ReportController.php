<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Services\AlertService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
        protected AlertService $alertService
    ) {}

    public function index()
    {
        $overview = $this->reportService->getFinancialOverview(now()->startOfMonth()->toDateString(), now()->toDateString());
        return view('reports.index', compact('overview'));
    }

    public function sales(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $staffId = $request->input('staff_id');
        $paymentMethod = $request->input('payment_method');

        $staffMembers = User::orderBy('name')->get();
        $sales = $this->reportService->getSalesReport($startDate, $endDate, $staffId ? (int) $staffId : null, $paymentMethod);
        $medicineSales = $this->reportService->getSalesByMedicine($startDate, $endDate);

        $totalRevenue = $sales->sum('total');
        $totalDiscounts = $sales->sum('discount');
        $totalTransactions = $sales->count();

        if ($request->input('export') === 'csv') {
            return $this->exportSalesCsv($sales);
        }

        return view('reports.sales', compact(
            'sales',
            'medicineSales',
            'staffMembers',
            'startDate',
            'endDate',
            'staffId',
            'paymentMethod',
            'totalRevenue',
            'totalDiscounts',
            'totalTransactions'
        ));
    }

    public function inventory(Request $request)
    {
        $valuationData = $this->reportService->getInventoryValuationReport();

        $totalCost = $valuationData->sum('cost_valuation');
        $totalRetail = $valuationData->sum('retail_valuation');
        $potentialProfit = $totalRetail - $totalCost;

        if ($request->input('export') === 'csv') {
            return $this->exportInventoryCsv($valuationData);
        }

        return view('reports.inventory', compact('valuationData', 'totalCost', 'totalRetail', 'potentialProfit'));
    }

    public function expiries(Request $request)
    {
        $range = (int) $request->input('range', 30);
        $batches = $this->alertService->getExpiringBatches($range);
        $expiredBatches = $this->alertService->getExpiredBatches();
        $counts = $this->alertService->getExpiryCounts();

        return view('reports.expiries', compact('batches', 'expiredBatches', 'range', 'counts'));
    }

    public function profit(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $financials = $this->reportService->getFinancialOverview($startDate, $endDate);
        $topProfitable = $this->reportService->getSalesByMedicine($startDate, $endDate)->take(10);

        return view('reports.profit', compact('financials', 'topProfitable', 'startDate', 'endDate'));
    }

    public function purchases(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $supplierId = $request->input('supplier_id');

        $suppliers = Supplier::orderBy('name')->get();

        $purchases = Purchase::with(['supplier', 'creator', 'items.medicine'])
            ->when($startDate, fn ($q) => $q->whereDate('purchase_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('purchase_date', '<=', $endDate))
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->latest('purchase_date')
            ->get();

        $totalExpenditure = $purchases->sum('total');

        return view('reports.purchases', compact('purchases', 'suppliers', 'startDate', 'endDate', 'supplierId', 'totalExpenditure'));
    }

    protected function exportSalesCsv($sales): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sales_report_' . date('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () use ($sales) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Invoice #', 'Date', 'Customer', 'Cashier', 'Payment Method', 'Subtotal', 'Discount', 'Tax', 'Total']);

            foreach ($sales as $sale) {
                fputcsv($handle, [
                    $sale->invoice_number,
                    $sale->sale_date->format('Y-m-d H:i'),
                    $sale->customer?->name ?? 'Walk-in',
                    $sale->seller?->name ?? 'N/A',
                    strtoupper($sale->payment_method),
                    number_format($sale->subtotal, 2, '.', ''),
                    number_format($sale->discount, 2, '.', ''),
                    number_format($sale->tax, 2, '.', ''),
                    number_format($sale->total, 2, '.', ''),
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    protected function exportInventoryCsv($valuationData): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="inventory_valuation_' . date('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () use ($valuationData) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Medicine Name', 'Generic Name', 'Category', 'Batch #', 'Supplier', 'Expiry Date', 'Status', 'Quantity', 'Cost Price', 'Selling Price', 'Total Cost', 'Total Retail', 'Potential Profit']);

            foreach ($valuationData as $row) {
                fputcsv($handle, [
                    $row['medicine_name'],
                    $row['generic_name'],
                    $row['category'],
                    $row['batch_number'],
                    $row['supplier'],
                    $row['expiry_date'],
                    $row['is_expired'] ? 'EXPIRED' : ($row['days_left'] <= 30 ? 'EXPIRING SOON' : 'ACTIVE'),
                    $row['quantity'],
                    number_format($row['unit_cost'], 2, '.', ''),
                    number_format($row['unit_price'], 2, '.', ''),
                    number_format($row['cost_valuation'], 2, '.', ''),
                    number_format($row['retail_valuation'], 2, '.', ''),
                    number_format($row['potential_profit'], 2, '.', ''),
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }
}
