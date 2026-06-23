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
    public function index()
    {
        $this->authorize('viewAny', ExpenseTransaction::class);

        $period = request('period', 'this_month');
        $selectedMonth = request('month', now()->format('Y-m'));
        $categoryFilter = request('category', '');
        $statusFilter = request('status', ''); // 👈 NEW

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

        $summaryQuery = ExpenseTransaction::query();
        if ($startDate && $endDate) $summaryQuery->whereBetween('date', [$startDate, $endDate]);
        if ($categoryFilter !== '') $summaryQuery->where('category', $categoryFilter);
        if ($statusFilter !== '') $summaryQuery->where('status', $statusFilter); // 👈 NEW

        $categoryTotals = ExpenseTransaction::query()
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('date', [$startDate, $endDate]))
            ->when($categoryFilter !== '', fn($q) => $q->where('category', $categoryFilter))
            ->when($statusFilter !== '', fn($q) => $q->where('status', $statusFilter)) // 👈 NEW
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        $totalExpenses = $summaryQuery->sum('amount');

        $summary = ['categories' => $categoryTotals, 'total_expenses' => $totalExpenses];

        $perPage = Setting::get('rows_per_page', 20);

        $transactionsQuery = ExpenseTransaction::with('supplier');
        if ($startDate && $endDate) $transactionsQuery->whereBetween('date', [$startDate, $endDate]);
        if ($categoryFilter !== '') $transactionsQuery->where('category', $categoryFilter);
        if ($statusFilter !== '') $transactionsQuery->where('status', $statusFilter); // 👈 NEW

        $transactions = $transactionsQuery->latest()
            ->paginate($perPage)
            ->through(fn($item) => [
                'id' => $item->id,
                'date' => $item->date->format('Y-m-d'),
                'supplier_name' => $item->supplier?->name,
                'category' => $item->category,
                'amount' => $item->amount,
                'receipt_number' => $item->receipt_number,
                'payment_method' => $item->payment_method,
                'status' => $item->status, // 👈 NEW
                'created_at' => $item->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Expenses/Index', [
            'transactions' => $transactions,
            'summary' => $summary,
            'filters' => [
                'period' => $period,
                'month' => $selectedMonth,
                'category' => $categoryFilter,
                'status' => $statusFilter, // 👈 NEW
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('create', ExpenseTransaction::class);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);
        $categories = explode(',', Setting::get('default_expense_categories', 'Office Supplies,Utilities,Rent,Transportation,Maintenance,Others'));

        return Inertia::render('Expenses/Create', [
            'suppliers' => $suppliers,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', ExpenseTransaction::class);
        $validated = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'category' => ['required', 'in:' . implode(',', Setting::get('default_expense_categories', 'Office Supplies,Utilities,Rent,Transportation,Maintenance,Others'))],
            'description' => 'nullable|string',
            'receipt_number' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:50',
            'is_miscellaneous' => 'boolean',
            'status' => 'required|in:Paid,Unpaid,Pending', // 👈 NEW
        ]);
        $validated['created_by'] = auth()->id();
        ExpenseTransaction::create($validated);
        return redirect()->route('expenses.index')->with('success', 'Expense recorded.');
    }

    public function edit(ExpenseTransaction $expense)
    {
        $this->authorize('update', $expense);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);
        $categories = explode(',', Setting::get('default_expense_categories', 'Office Supplies,Utilities,Rent,Transportation,Maintenance,Others'));

        return Inertia::render('Expenses/Edit', [
            'expense' => $expense,
            'suppliers' => $suppliers,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, ExpenseTransaction $expense)
    {
        $this->authorize('update', $expense);
        $validated = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'category' => ['required', 'in:' . implode(',', Setting::get('default_expense_categories', 'Office Supplies,Utilities,Rent,Transportation,Maintenance,Others'))],
            'description' => 'nullable|string',
            'receipt_number' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:50',
            'is_miscellaneous' => 'boolean',
            'status' => 'required|in:Paid,Unpaid,Pending', // 👈 NEW
        ]);
        $expense->update($validated);
        return redirect()->route('expenses.index')->with('success', 'Expense updated.');
    }

    public function destroy(ExpenseTransaction $expense)
    {
        $this->authorize('delete', $expense);
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }
}