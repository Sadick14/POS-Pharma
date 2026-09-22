<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MedicineController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $status = $request->input('status');

        $categories = Category::orderBy('name')->get();

        $medicines = Medicine::with(['category', 'batches' => function ($q) {
                $q->where('quantity', '>', 0)->orderBy('expiry_date', 'asc');
            }])
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('generic_name', 'like', "%{$search}%")
                        ->orWhere('brand_name', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('manufacturer', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('medicines.index', compact('medicines', 'categories', 'search', 'categoryId', 'status'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('medicines.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'generic_name' => ['nullable', 'string', 'max:255'],
            'brand_name' => ['nullable', 'string', 'max:255'],
            'dosage_form' => ['nullable', 'string', 'max:100'],
            'strength' => ['nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:50'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:medicines,barcode'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $medicine = Medicine::create($validated);

        AuditLog::log(
            'create',
            'Medicines',
            (string) $medicine->id,
            "Created medicine: {$medicine->name}",
            null,
            $medicine->toArray()
        );

        return redirect()->route('medicines.index')->with('success', "Medicine '{$medicine->name}' registered successfully.");
    }

    public function show(Medicine $medicine)
    {
        $medicine->load(['category', 'batches.supplier', 'stockMovements.creator', 'stockAdjustments.adjuster']);
        return view('medicines.show', compact('medicine'));
    }

    public function edit(Medicine $medicine)
    {
        $categories = Category::orderBy('name')->get();
        return view('medicines.edit', compact('medicine', 'categories'));
    }

    public function update(Request $request, Medicine $medicine)
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'generic_name' => ['nullable', 'string', 'max:255'],
            'brand_name' => ['nullable', 'string', 'max:255'],
            'dosage_form' => ['nullable', 'string', 'max:100'],
            'strength' => ['nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:50'],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique('medicines')->ignore($medicine->id)],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $oldValues = $medicine->toArray();
        $medicine->update($validated);

        AuditLog::log(
            'update',
            'Medicines',
            (string) $medicine->id,
            "Updated medicine: {$medicine->name}",
            $oldValues,
            $medicine->toArray()
        );

        return redirect()->route('medicines.index')->with('success', "Medicine '{$medicine->name}' updated successfully.");
    }

    public function destroy(Medicine $medicine)
    {
        // Check if there are active batches with stock
        if ($medicine->current_stock > 0) {
            return back()->with('error', "Cannot delete '{$medicine->name}' because it currently has {$medicine->current_stock} units in active stock. Please adjust or deplete batches first.");
        }

        $medicineName = $medicine->name;
        $medicine->delete();

        AuditLog::log('delete', 'Medicines', (string) $medicine->id, "Soft-deleted medicine: {$medicineName}");

        return redirect()->route('medicines.index')->with('success', "Medicine '{$medicineName}' deleted successfully.");
    }

    /**
     * AJAX live search for POS and autocomplete.
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        $barcode = $request->input('barcode');

        if ($barcode) {
            $medicine = Medicine::with(['category', 'activeBatches'])
                ->where('status', 'active')
                ->where('barcode', $barcode)
                ->first();

            if ($medicine) {
                return response()->json([
                    'success' => true,
                    'medicine' => [
                        'id' => $medicine->id,
                        'name' => $medicine->name,
                        'generic_name' => $medicine->generic_name,
                        'brand_name' => $medicine->brand_name,
                        'dosage_form' => $medicine->dosage_form,
                        'strength' => $medicine->strength,
                        'unit' => $medicine->unit,
                        'barcode' => $medicine->barcode,
                        'selling_price' => (float) $medicine->selling_price,
                        'current_stock' => $medicine->current_stock,
                        'batches_count' => $medicine->activeBatches->count(),
                        'earliest_expiry' => $medicine->earliest_expiry_batch?->expiry_date?->format('Y-m-d') ?? 'N/A',
                        'batches' => $medicine->activeBatches->map(fn ($b) => [
                            'id' => $b->id,
                            'batch_number' => $b->batch_number,
                            'quantity' => $b->quantity,
                            'selling_price' => (float) $b->selling_price,
                            'expiry_date' => $b->expiry_date->format('Y-m-d'),
                        ]),
                    ]
                ]);
            }

            return response()->json(['success' => false, 'message' => 'No medicine found with this barcode.'], 404);
        }

        $medicines = Medicine::with(['category', 'activeBatches'])
            ->where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('generic_name', 'like', "%{$query}%")
                    ->orWhere('brand_name', 'like', "%{$query}%")
                    ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->take(15)
            ->get()
            ->map(function (Medicine $m) {
                return [
                    'id' => $m->id,
                    'name' => $m->name,
                    'generic_name' => $m->generic_name,
                    'brand_name' => $m->brand_name,
                    'dosage_form' => $m->dosage_form,
                    'strength' => $m->strength,
                    'unit' => $m->unit,
                    'barcode' => $m->barcode,
                    'selling_price' => (float) $m->selling_price,
                    'current_stock' => $m->current_stock,
                    'batches_count' => $m->activeBatches->count(),
                    'earliest_expiry' => $m->earliest_expiry_batch?->expiry_date?->format('Y-m-d') ?? 'N/A',
                ];
            });

        return response()->json(['success' => true, 'medicines' => $medicines]);
    }
}
