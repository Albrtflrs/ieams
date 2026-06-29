<?php

namespace App\Http\Controllers;

use App\Models\ExpenseTransaction;
use App\Models\Supplier;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpenseTransactionController extends Controller
{
    // Fallback categories (used if settings are empty)
    private const DEFAULT_CATEGORIES = 'DELIVERY (parcel receiving),DAILY EXPENSES,GAS/MAINTENANCE,SALARY,CASH RECEIVED,LOAN PAYMENT,MONTHLY FIX BILLS';

    /**
     * Display a listing of expense transactions with filters and search.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', ExpenseTransaction::class);

        // ─── Read filters ───────────────────────────────────────
        $period = $request->input('period', 'this_month');
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $categoryFilter = $request->input('category', '');
        $statusFilter = $request->input('status', '');
        $search = $request->input('search', '');

        // ─── Date range based on period ────────────────────────
        $startDate = null;
        $endDate = null;
        switch ($period) {
            case 'this_month':
                $startDate = Carbon::parse($selectedMonth)->startOfMonth();
                $endDate = Carbon::parse($selectedMonth)->endOfMonth();
                break;
            case 'this_year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'last_12_months':
                $startDate = Carbon::now()->subMonths(11)->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'all_time':
                $startDate = null;
                $endDate = null;
                break;
            default:
                $startDate = Carbon::parse($selectedMonth)->startOfMonth();
                $endDate = Carbon::parse($selectedMonth)->endOfMonth();
        }

        // ─── Build summary query (for total and category breakdown) ──
        $summaryQuery = ExpenseTransaction::query();
        if ($startDate && $endDate) $summaryQuery->whereBetween('date', [$startDate, $endDate]);
        if ($categoryFilter !== '') $summaryQuery->where('category', $categoryFilter);
        if ($statusFilter !== '') $summaryQuery->where('status', $statusFilter);
        if ($search !== '') {
            $summaryQuery->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('receipt_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }
        $totalExpenses = $summaryQuery->sum('amount');

        // ─── Category totals (respecting filters) ──────────────
        $categoryTotals = ExpenseTransaction::query()
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('date', [$startDate, $endDate]))
            ->when($categoryFilter !== '', fn($q) => $q->where('category', $categoryFilter))
            ->when($statusFilter !== '', fn($q) => $q->where('status', $statusFilter))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('description', 'like', "%{$search}%")
                       ->orWhere('receipt_number', 'like', "%{$search}%")
                       ->orWhereHas('supplier', function ($q3) use ($search) {
                           $q3->where('name', 'like', "%{$search}%");
                       });
                });
            })
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        // ─── Status counts (global, for all filtered records) ── 👈 NEW
        $statusQuery = ExpenseTransaction::query();
        if ($startDate && $endDate) $statusQuery->whereBetween('date', [$startDate, $endDate]);
        if ($categoryFilter !== '') $statusQuery->where('category', $categoryFilter);
        if ($statusFilter !== '') $statusQuery->where('status', $statusFilter);
        if ($search !== '') {
            $statusQuery->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('receipt_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }
        $statusCounts = $statusQuery->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        // Ensure all statuses are present (even if zero)
        $allStatuses = ['Paid', 'Unpaid', 'Pending'];
        foreach ($allStatuses as $s) {
            if (!isset($statusCounts[$s])) {
                $statusCounts[$s] = 0;
            }
        }

        $summary = [
            'categories'     => $categoryTotals,
            'total_expenses' => $totalExpenses,
            'status_counts'  => $statusCounts, // 👈 NEW
        ];

        // ─── Transactions (paginated) ──────────────────────────
        $perPage = Setting::get('rows_per_page', 20);

        $transactionsQuery = ExpenseTransaction::with('supplier');
        if ($startDate && $endDate) $transactionsQuery->whereBetween('date', [$startDate, $endDate]);
        if ($categoryFilter !== '') $transactionsQuery->where('category', $categoryFilter);
        if ($statusFilter !== '') $transactionsQuery->where('status', $statusFilter);
        if ($search !== '') {
            $transactionsQuery->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('receipt_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $transactions = $transactionsQuery->latest()
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn($item) => [
                'id' => $item->id,
                'date' => $item->date->format('Y-m-d'),
                'supplier_name' => $item->supplier?->name,
                'category' => $item->category,
                'amount' => $item->amount,
                'receipt_number' => $item->receipt_number,
                'payment_method' => $item->payment_method,
                'status' => $item->status,
                'created_at' => $item->created_at->format('Y-m-d'),
            ]);

        // ─── Category list from settings (for the frontend) ── 👈 NEW
        $categories = explode(',', Setting::get('default_expense_categories', self::DEFAULT_CATEGORIES));

        return Inertia::render('Expenses/Index', [
            'transactions' => $transactions,
            'summary'      => $summary,
            'filters'      => [
                'period'   => $period,
                'month'    => $selectedMonth,
                'category' => $categoryFilter,
                'status'   => $statusFilter,
                'search'   => $search,
            ],
            'categories'   => $categories, // 👈 NEW
        ]);
    }

    /**
     * Show the form for creating a new expense.
     */
    public function create()
    {
        $this->authorize('create', ExpenseTransaction::class);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);
        $categories = explode(',', Setting::get('default_expense_categories', self::DEFAULT_CATEGORIES));

        return Inertia::render('Expenses/Create', [
            'suppliers' => $suppliers,
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created expense in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', ExpenseTransaction::class);
        $categories = explode(',', Setting::get('default_expense_categories', self::DEFAULT_CATEGORIES));
        $validated = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'category' => ['required', 'in:' . implode(',', $categories)],
            'description' => 'nullable|string',
            'receipt_number' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:50',
            'is_miscellaneous' => 'boolean',
            'status' => 'required|in:Paid,Unpaid,Pending',
        ]);
        $validated['created_by'] = auth()->id();
        ExpenseTransaction::create($validated);
        return redirect()->route('expenses.index')->with('success', 'Expense recorded.');
    }

    /**
     * Show the form for editing the specified expense.
     */
    public function edit(ExpenseTransaction $expense)
    {
        $this->authorize('update', $expense);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);
        $categories = explode(',', Setting::get('default_expense_categories', self::DEFAULT_CATEGORIES));

        return Inertia::render('Expenses/Edit', [
            'expense' => $expense,
            'suppliers' => $suppliers,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified expense in storage.
     */
    public function update(Request $request, ExpenseTransaction $expense)
    {
        $this->authorize('update', $expense);
        $categories = explode(',', Setting::get('default_expense_categories', self::DEFAULT_CATEGORIES));
        $validated = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'category' => ['required', 'in:' . implode(',', $categories)],
            'description' => 'nullable|string',
            'receipt_number' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:50',
            'is_miscellaneous' => 'boolean',
            'status' => 'required|in:Paid,Unpaid,Pending',
        ]);
        $expense->update($validated);
        return redirect()->route('expenses.index')->with('success', 'Expense updated.');
    }

    /**
     * Remove the specified expense from storage.
     */
    public function destroy(ExpenseTransaction $expense)
    {
        $this->authorize('delete', $expense);
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }
}