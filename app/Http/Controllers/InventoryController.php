<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Services\AlertService;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected AlertService $alertService
    ) {}

    public function index(Request $request)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $filter = $request->input('filter'); // all, low_stock, out_of_stock

        $categories = Category::orderBy('name')->get();

        $query = Medicine::with(['category', 'activeBatches'])
            ->where('status', 'active')
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('generic_name', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId));

        $medicines = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('inventory.index', compact('medicines', 'categories', 'search', 'categoryId', 'filter'));
    }

    public function batches(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status'); // active, depleted, expired

        $batches = MedicineBatch::with(['medicine.category', 'supplier'])
            ->when($search, function ($q, $search) {
                $q->where('batch_number', 'like', "%{$search}%")
                    ->orWhereHas('medicine', function ($m) use ($search) {
                        $m->where('name', 'like', "%{$search}%")
                          ->orWhere('generic_name', 'like', "%{$search}%");
                    });
            })
            ->when($status, function ($q, $status) {
                if ($status === 'expired') {
                    $q->where('expiry_date', '<', now()->toDateString())->where('quantity', '>', 0);
                } elseif ($status === 'depleted') {
                    $q->where('quantity', '<=', 0);
                } elseif ($status === 'active') {
                    $q->where('quantity', '>', 0)->where('expiry_date', '>=', now()->toDateString());
                }
            })
            ->orderBy('expiry_date', 'asc')
            ->paginate(20)
            ->withQueryString();

        return view('inventory.batches', compact('batches', 'search', 'status'));
    }

    public function lowStock()
    {
        $medicines = $this->alertService->getLowStockMedicines();
        return view('inventory.low_stock', compact('medicines'));
    }

    public function expiries(Request $request)
    {
        $range = $request->input('range', '30'); // expired, 30, 60, 90

        $expiryCounts = $this->alertService->getExpiryCounts();

        if ($range === 'expired') {
            $batches = $this->alertService->getExpiredBatches();
        } else {
            $batches = $this->alertService->getExpiringBatches((int) $range);
        }

        return view('inventory.expiries', compact('batches', 'range', 'expiryCounts'));
    }

    public function adjustments()
    {
        $adjustments = StockAdjustment::with(['medicine', 'batch', 'adjuster'])
            ->latest()
            ->paginate(20);

        return view('inventory.adjustments.index', compact('adjustments'));
    }

    public function createAdjustment()
    {
        $medicines = Medicine::with(['batches' => function ($q) {
            $q->where('quantity', '>', 0)->orderBy('expiry_date', 'asc');
        }])->where('status', 'active')->orderBy('name')->get();

        return view('inventory.adjustments.create', compact('medicines'));
    }

    public function storeAdjustment(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => ['required', 'exists:medicine_batches,id'],
            'adjustment_quantity' => ['required', 'integer', 'not_in:0'],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $this->inventoryService->adjustStock(
                $validated['batch_id'],
                $validated['adjustment_quantity'],
                $validated['reason'],
                $validated['notes'],
                Auth::id()
            );

            return redirect()->route('inventory.adjustments')->with('success', 'Stock adjustment recorded successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function movements(Request $request)
    {
        $medicineId = $request->input('medicine_id');
        $type = $request->input('type');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $medicines = Medicine::orderBy('name')->get();

        $movements = StockMovement::with(['medicine', 'batch', 'creator'])
            ->when($medicineId, fn ($q) => $q->where('medicine_id', $medicineId))
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($startDate, fn ($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('created_at', '<=', $endDate))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('inventory.movements', compact('movements', 'medicines', 'medicineId', 'type', 'startDate', 'endDate'));
    }
}
