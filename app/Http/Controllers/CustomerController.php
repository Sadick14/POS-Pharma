<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $customers = Customer::withCount('sales')
            ->when($search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers', 'search'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'date_of_birth' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $customer = Customer::create($validated);

        AuditLog::log('create', 'Customers', (string) $customer->id, "Registered customer: {$customer->name}");

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'customer' => $customer]);
        }

        return redirect()->route('customers.index')->with('success', "Customer '{$customer->name}' created successfully.");
    }

    public function show(Customer $customer)
    {
        $customer->load(['sales.items.medicine', 'sales.seller']);

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'date_of_birth' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $customer->update($validated);

        AuditLog::log('update', 'Customers', (string) $customer->id, "Updated customer: {$customer->name}");

        return redirect()->route('customers.index')->with('success', "Customer '{$customer->name}' updated successfully.");
    }

    public function destroy(Customer $customer)
    {
        $name = $customer->name;
        $customer->delete();

        AuditLog::log('delete', 'Customers', (string) $customer->id, "Deleted customer: {$name}");

        return redirect()->route('customers.index')->with('success', "Customer '{$name}' deleted successfully.");
    }

    /**
     * Quick registration AJAX endpoint from POS modal.
     */
    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $customer = Customer::create($validated);

        AuditLog::log('create', 'Customers', (string) $customer->id, "Quick-registered POS customer: {$customer->name}");

        return response()->json(['success' => true, 'customer' => $customer]);
    }
}
