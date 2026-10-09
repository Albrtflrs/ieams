<?php

namespace App\Http\Controllers;

use App\Models\ExpenseTransaction;
use App\Models\Supplier;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExpenseTransactionController extends Controller
{
    // Fallback categories (used if settings are empty)
    private const DEFAULT_CATEGORIES = 'DELIVERY (parcel receiving),DAILY EXPENSES,GAS/MAINTENANCE,SALARY,CASH RECEIVED,LOAN PAYMENT,MONTHLY FIX BILLS';

    // ─── Helper: Apply shared filters to a query builder ──────────────
    private function applyFilters($query, ?Carbon $startDate, ?Carbon $endDate, string $category, string $status, string $search)
    {
        if (auth()->user()->hasRole(['staff', 'viewer'])) {
            $query->where('created_by', auth()->id());
        }

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }
        if ($category !== '') {
            $query->where('category', $category);
        }
        if ($status !== '') {
            $query->where('status', $status);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('receipt_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        return $query;
    }

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

        // ─── Total expenses ──────────────────────────────────────
        $totalExpenses = $this->applyFilters(
            ExpenseTransaction::query(), $startDate, $endDate, $categoryFilter, $statusFilter, $search
        )->sum('amount');

        // ─── Category totals ────────────────────────────────────
        $categoryTotals = $this->applyFilters(
            ExpenseTransaction::query(), $startDate, $endDate, $categoryFilter, $statusFilter, $search
        )
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        // ─── Status counts ──────────────────────────────────────
        $statusCounts = $this->applyFilters(
            ExpenseTransaction::query(), $startDate, $endDate, $categoryFilter, $statusFilter, $search
        )
            ->selectRaw('status, COUNT(*) as count')
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
            'status_counts'  => $statusCounts,
        ];

        // ─── Transactions (paginated) ──────────────────────────
        $perPage = Setting::get('rows_per_page', 20);

        $transactionsQuery = $this->applyFilters(
            ExpenseTransaction::with('supplier'), $startDate, $endDate, $categoryFilter, $statusFilter, $search
        );

        $transactions = $transactionsQuery->latest()
            ->paginate($perPage)
            ->through(fn($item) => [
                'id'             => $item->id,
                'date'           => $item->date->format('Y-m-d'),
                'supplier_name'  => $item->supplier?->name,
                'category'       => $item->category,
                'amount'         => $item->amount,
                'receipt_number' => $item->receipt_number,
                'payment_method' => $item->payment_method,
                'status'         => $item->status,
                'created_at'     => $item->created_at->format('Y-m-d'),
            ]);

        // ─── Category list from settings ──────────────────────
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
            'categories'   => $categories,
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
     * Remove the specified expense from storage (soft delete).
     */
    public function destroy(ExpenseTransaction $expense)
    {
        $this->authorize('delete', $expense);
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }

    // ─── TRASH PAGE ─────────────────────────────────────────────────────
    public function trash()
    {
        $this->authorize('viewTrash', ExpenseTransaction::class);

        $transactions = ExpenseTransaction::onlyTrashed()
            ->with('supplier')
            ->latest('deleted_at')
            ->paginate(20)
            ->through(fn($item) => [
                'id'             => $item->id,
                'date'           => $item->date->format('Y-m-d'),
                'supplier_name'  => $item->supplier?->name,
                'category'       => $item->category,
                'amount'         => $item->amount,
                'deleted_at'     => $item->deleted_at->format('Y-m-d H:i:s'),
            ]);

        return Inertia::render('Expenses/Trash', [
            'transactions' => $transactions,
        ]);
    }

    // ─── RESTORE ────────────────────────────────────────────────────────
    public function restore($id)
    {
        $expense = ExpenseTransaction::withTrashed()->findOrFail($id);
        $this->authorize('restore', $expense);
        $expense->restore();
        return redirect()->route('expenses.trash')->with('success', 'Expense restored.');
    }

    // ─── FORCE DELETE ──────────────────────────────────────────────────
    public function forceDelete($id)
    {
        $expense = ExpenseTransaction::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $expense);
        $expense->forceDelete();
        return redirect()->route('expenses.trash')->with('success', 'Expense permanently deleted.');
    }
}