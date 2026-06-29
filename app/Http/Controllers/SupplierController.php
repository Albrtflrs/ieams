<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index(Request $request) // 👈 added Request
    {
        $this->authorize('viewAny', Supplier::class);

        $search = $request->input('search');

        $perPage = Setting::get('rows_per_page', 20);

        $query = Supplier::query();

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->latest()
            ->paginate($perPage)
            ->withQueryString() // 👈 keeps search in pagination links
            ->through(fn($supplier) => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'contact_person' => $supplier->contact_person,
                'phone' => $supplier->phone,
                'email' => $supplier->email,
                'address' => $supplier->address,
                'created_at' => $supplier->created_at->format('Y-m-d'),
            ]);

        // ─── Summary calculations (unchanged) ───
        $totalSuppliers = Supplier::count();
        $topSupplierAmount = Supplier::withSum('expenseTransactions', 'amount')
            ->orderBy('expense_transactions_sum_amount', 'desc')->first();
        $topSupplierCount = Supplier::withCount('expenseTransactions')
            ->orderBy('expense_transactions_count', 'desc')->first();
        $totalExpenses = Supplier::with('expenseTransactions')->get()->sum(fn($s) => $s->expenseTransactions->sum('amount'));

        $summary = [
            'total_suppliers' => $totalSuppliers,
            'total_expenses' => $totalExpenses,
            'top_supplier_amount' => $topSupplierAmount ? ['name' => $topSupplierAmount->name, 'amount' => $topSupplierAmount->expense_transactions_sum_amount ?? 0] : null,
            'top_supplier_count' => $topSupplierCount ? ['name' => $topSupplierCount->name, 'count' => $topSupplierCount->expense_transactions_count ?? 0] : null,
        ];

        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers,
            'summary' => $summary,
            'filters' => ['search' => $search], // 👈 pass current search to Vue
        ]);
    }

    // ─── Other methods (create, store, edit, update, destroy) remain unchanged ───
    public function create()
    {
        $this->authorize('create', Supplier::class);
        return Inertia::render('Suppliers/Create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Supplier::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);
        Supplier::create($validated);
        return redirect()->route('suppliers.index')->with('success', 'Supplier created.');
    }

    public function edit(Supplier $supplier)
    {
        $this->authorize('update', $supplier);
        return Inertia::render('Suppliers/Edit', ['supplier' => $supplier]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $this->authorize('update', $supplier);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);
        $supplier->update($validated);
        return redirect()->route('suppliers.index')->with('success', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier)
    {
        $this->authorize('delete', $supplier);
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted.');
    }

    // Optional: add show method if needed (the View link exists)
    public function show(Supplier $supplier)
    {
        $this->authorize('view', $supplier);
        return Inertia::render('Suppliers/Show', ['supplier' => $supplier]);
    }
}