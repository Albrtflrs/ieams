<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\ExpenseTransaction;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index(Request $request) // 👈 added Request
    {
        $this->authorize('viewAny', Supplier::class);
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'has_expenses' => ['nullable', 'in:yes,no'],
        ]);

        $search = $request->input('search');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $hasExpenses = $request->input('has_expenses', '');

        $perPage = Setting::get('rows_per_page', 20);

        $query = Supplier::query();

        foreach (preg_split('/\s+/', trim((string) $search), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $term) {
            $query->where(function ($q) use ($term) {
                $like = "%{$term}%";
                $q->where('name', 'like', $like)
                  ->orWhere('contact_person', 'like', $like)
                  ->orWhere('email', 'like', $like)
                  ->orWhere('phone', 'like', $like)
                  ->orWhere('address', 'like', $like);
            });
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        if ($hasExpenses === 'yes') {
            $query->whereHas('expenseTransactions');
        } elseif ($hasExpenses === 'no') {
            $query->whereDoesntHave('expenseTransactions');
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
        $totalExpenses = ExpenseTransaction::whereHas('supplier')->sum('amount');

        $summary = [
            'total_suppliers' => $totalSuppliers,
            'total_expenses' => $totalExpenses,
            'top_supplier_amount' => $topSupplierAmount ? ['name' => $topSupplierAmount->name, 'amount' => $topSupplierAmount->expense_transactions_sum_amount ?? 0] : null,
            'top_supplier_count' => $topSupplierCount ? ['name' => $topSupplierCount->name, 'count' => $topSupplierCount->expense_transactions_count ?? 0] : null,
        ];

        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers,
            'summary' => $summary,
            'filters' => [
                'search' => $search,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'has_expenses' => $hasExpenses,
            ],
        ]);
    }

    public function search(Request $request)
    {
        $this->authorize('viewAny', Supplier::class);

        $search = trim((string) $request->query('q', ''));
        if (mb_strlen($search) < 2) {
            return response()->json([]);
        }

        $query = Supplier::query();
        foreach (preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $term) {
            $query->where('name', 'like', "%{$term}%");
        }

        return response()->json($query->orderBy('name')->limit(20)->get(['id', 'name']));
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
