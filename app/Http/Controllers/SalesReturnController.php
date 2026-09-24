<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SalesReturn;
use App\Services\InventoryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalesReturnController extends Controller
{
    public function __construct(protected InventoryService $inventoryService) {}

    public function index()
    {
        $returns = SalesReturn::with(['sale.customer', 'processor', 'items.medicine', 'items.batch'])
            ->latest()
            ->paginate(15);

        return view('returns.index', compact('returns'));
    }

    public function create(Sale $sale)
    {
        $sale->load(['customer', 'seller', 'items.medicine', 'items.batch', 'returns.items']);

        return view('returns.create', compact('sale'));
    }

    public function store(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'refund_method' => ['required', 'string', 'max:50'],
            'reason' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sale_item_id' => ['required', 'exists:sale_items,id'],
            'items.*.medicine_id' => ['required', 'exists:medicines,id'],
            'items.*.batch_id' => ['nullable', 'exists:medicine_batches,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_refund_price' => ['required', 'numeric', 'min:0'],
            'items.*.is_resalable' => ['required', 'boolean'],
            'items.*.condition_notes' => ['nullable', 'string'],
        ]);

        try {
            $salesReturn = $this->inventoryService->processSalesReturn(
                $sale,
                $validated['items'],
                $validated['refund_method'],
                $validated['reason'],
                Auth::id()
            );

            return redirect()->route('returns.show', $salesReturn)->with('success', "Return #{$salesReturn->return_number} processed successfully.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(SalesReturn $return)
    {
        $return->load(['sale.customer', 'processor', 'items.medicine', 'items.batch']);

        return view('returns.show', compact('return'));
    }
}
