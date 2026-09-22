<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function __construct(protected InventoryService $inventoryService) {}

    public function index(Request $request)
    {
        $search = $request->input('search');
        $supplierId = $request->input('supplier_id');
        $paymentStatus = $request->input('payment_status');

        $suppliers = Supplier::orderBy('name')->get();

        $purchases = Purchase::with(['supplier', 'creator', 'items.medicine'])
            ->when($search, function ($q, $search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$search}%"));
            })
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->when($paymentStatus, fn ($q) => $q->where('payment_status', $paymentStatus))
            ->latest('purchase_date')
            ->paginate(15)
            ->withQueryString();

        return view('purchases.index', compact('purchases', 'suppliers', 'search', 'supplierId', 'paymentStatus'));
    }

    public function create()
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $medicines = Medicine::where('status', 'active')->orderBy('name')->get();

        return view('purchases.create', compact('suppliers', 'medicines'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'invoice_number' => ['required', 'string', 'max:100', 'unique:purchases,invoice_number'],
            'purchase_date' => ['required', 'date'],
            'payment_status' => ['required', 'in:paid,partial,pending'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'exists:medicines,id'],
            'items.*.batch_number' => ['required', 'string', 'max:100'],
            'items.*.expiry_date' => ['required', 'date', 'after:today'],
            'items.*.manufactured_date' => ['nullable', 'date'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.selling_price' => ['required', 'numeric', 'min:0'],
        ]);

        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += ($item['quantity'] * $item['unit_cost']);
        }

        $purchase = Purchase::create([
            'supplier_id' => $validated['supplier_id'],
            'invoice_number' => $validated['invoice_number'],
            'purchase_date' => $validated['purchase_date'],
            'subtotal' => $subtotal,
            'discount' => 0.00,
            'tax' => 0.00,
            'total' => $subtotal,
            'payment_status' => $validated['payment_status'],
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        $this->inventoryService->receivePurchaseStock($purchase, $validated['items'], Auth::id());

        return redirect()->route('purchases.show', $purchase)->with('success', "Purchase invoice #{$purchase->invoice_number} recorded and inventory updated.");
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'creator', 'items.medicine', 'items.batch']);
        return view('purchases.show', compact('purchase'));
    }
}
