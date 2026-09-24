<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\User;
use App\Services\InventoryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function __construct(protected InventoryService $inventoryService) {}

    public function index()
    {
        $customers = Customer::orderBy('name')->get();
        $categories = Category::withCount('medicines')->orderBy('name')->get();
        $currency = Setting::get('currency_symbol', 'GHS');
        $taxRate = (float) Setting::get('tax_percentage', 0);

        // Fetch top fast-moving medicines for quick-pick POS buttons
        $quickMedicines = Medicine::with(['category', 'activeBatches'])
            ->where('status', 'active')
            ->orderBy('name')
            ->take(12)
            ->get();

        return view('pos.index', compact('customers', 'categories', 'currency', 'taxRate', 'quickMedicines'));
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'exists:medicines,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,mobile_money,card,split'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $sale = DB::transaction(function () use ($validated) {
                $subtotal = 0;
                $totalDiscount = 0;

                foreach ($validated['items'] as $item) {
                    $itemDiscount = (float) ($item['discount'] ?? 0);
                    $lineTotal = ($item['quantity'] * $item['unit_price']) - $itemDiscount;
                    $subtotal += ($item['quantity'] * $item['unit_price']);
                    $totalDiscount += $itemDiscount;
                }

                $taxRate = (float) Setting::get('tax_percentage', 0);
                $taxableAmount = max(0, $subtotal - $totalDiscount);
                $tax = ($taxRate > 0) ? round(($taxableAmount * $taxRate) / 100, 2) : 0.00;
                $total = round($taxableAmount + $tax, 2);

                $amountPaid = (float) $validated['amount_paid'];
                if ($amountPaid < $total && $validated['payment_method'] === 'cash') {
                    throw new Exception("Amount paid ({$amountPaid}) cannot be less than total ({$total}).");
                }

                $changeDue = max(0, round($amountPaid - $total, 2));

                $prefix = Setting::get('invoice_prefix', 'INV-');
                $invoiceNumber = $prefix.date('Ymd').'-'.str_pad((string) (Sale::whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);

                $sale = Sale::create([
                    'customer_id' => $validated['customer_id'] ?? null,
                    'invoice_number' => $invoiceNumber,
                    'sale_date' => now(),
                    'subtotal' => $subtotal,
                    'discount' => $totalDiscount,
                    'tax' => $tax,
                    'total' => $total,
                    'amount_paid' => $amountPaid,
                    'change_due' => $changeDue,
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => 'paid',
                    'notes' => $validated['notes'] ?? null,
                    'sold_by' => Auth::id(),
                ]);

                // Deduct stock using FEFO logic for each item
                foreach ($validated['items'] as $item) {
                    $this->inventoryService->deductStockFEFO(
                        $sale,
                        $item['medicine_id'],
                        $item['quantity'],
                        $item['unit_price'],
                        (float) ($item['discount'] ?? 0),
                        Auth::id()
                    );
                }

                AuditLog::log(
                    'sale_completed',
                    'Sales',
                    (string) $sale->id,
                    "Completed POS sale #{$sale->invoice_number}. Total: ".number_format($total, 2).' via '.strtoupper($validated['payment_method'])
                );

                return $sale;
            });

            return response()->json([
                'success' => true,
                'message' => "Sale #{$sale->invoice_number} completed successfully.",
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'total' => $sale->total,
                'change_due' => $sale->change_due,
                'receipt_url' => route('pos.receipt', $sale->id),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function salesList(Request $request)
    {
        $search = $request->input('search');
        $staffId = $request->input('staff_id');
        $paymentMethod = $request->input('payment_method');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $staffMembers = User::orderBy('name')->get();

        $sales = Sale::with(['customer', 'seller', 'items.medicine'])
            ->when($search, function ($q, $search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
            })
            ->when($staffId, fn ($q) => $q->where('sold_by', $staffId))
            ->when($paymentMethod, fn ($q) => $q->where('payment_method', $paymentMethod))
            ->when($startDate, fn ($q) => $q->whereDate('sale_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('sale_date', '<=', $endDate))
            ->latest('sale_date')
            ->paginate(20)
            ->withQueryString();

        return view('sales.index', compact('sales', 'staffMembers', 'search', 'staffId', 'paymentMethod', 'startDate', 'endDate'));
    }

    public function showSale(Sale $sale)
    {
        $sale->load(['customer', 'seller', 'items.medicine', 'items.batch', 'returns.items.medicine', 'returns.processor']);

        return view('sales.show', compact('sale'));
    }

    public function receipt(Sale $sale)
    {
        $sale->load(['customer', 'seller', 'items.medicine', 'items.batch']);
        $pharmacyName = Setting::get('pharmacy_name', 'HealthCare Plus Pharmacy');
        $pharmacyAddress = Setting::get('pharmacy_address', 'Accra, Ghana');
        $pharmacyPhone = Setting::get('pharmacy_phone', '+233 24 123 4567');
        $currency = Setting::get('currency_symbol', 'GHS');

        return view('sales.receipt', compact('sale', 'pharmacyName', 'pharmacyAddress', 'pharmacyPhone', 'currency'));
    }
}
