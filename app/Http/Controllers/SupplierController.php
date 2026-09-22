<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $suppliers = Supplier::withCount(['purchases', 'batches'])
            ->when($search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers', 'search', 'status'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ]);

        $supplier = Supplier::create($validated);

        AuditLog::log('create', 'Suppliers', (string) $supplier->id, "Created supplier: {$supplier->name}");

        return redirect()->route('suppliers.index')->with('success', "Supplier '{$supplier->name}' registered successfully.");
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['purchases.creator', 'batches.medicine']);
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ]);

        $supplier->update($validated);

        AuditLog::log('update', 'Suppliers', (string) $supplier->id, "Updated supplier: {$supplier->name}");

        return redirect()->route('suppliers.index')->with('success', "Supplier '{$supplier->name}' updated successfully.");
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchases()->count() > 0) {
            return back()->with('error', "Cannot delete supplier '{$supplier->name}' with associated purchase orders. You may mark it as inactive instead.");
        }

        $name = $supplier->name;
        $supplier->delete();

        AuditLog::log('delete', 'Suppliers', (string) $supplier->id, "Deleted supplier: {$name}");

        return redirect()->route('suppliers.index')->with('success', "Supplier '{$name}' deleted successfully.");
    }
}
