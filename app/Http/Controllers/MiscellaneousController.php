<?php

namespace App\Http\Controllers;

use App\Models\MiscellaneousTransaction;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MiscellaneousController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', MiscellaneousTransaction::class);

        // ─── Filters ──────────────────────────────
        $type = $request->input('type');
        $category = $request->input('category');
        $search = $request->input('search');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $perPage = Setting::get('rows_per_page', 20);

        $query = MiscellaneousTransaction::query();

        if ($type) {
            $query->where('type', $type);
        }
        if ($category) {
            $query->where('category', $category);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }
        if ($dateFrom) {
            $query->where('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('date', '<=', $dateTo);
        }

        $transactions = $query->latest()
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn($item) => [
                'id' => $item->id,
                'type' => $item->type,
                'date' => $item->date->format('Y-m-d'),
                'amount' => $item->amount,
                'category' => $item->category,
                'description' => $item->description,
                'reference_number' => $item->reference_number,
                'created_at' => $item->created_at->format('Y-m-d H:i'),
            ]);

        // ─── Summary (filtered) ──────────────────────
        $totalIncome = (clone $query)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $query)->where('type', 'expense')->sum('amount');
        $net = $totalIncome - $totalExpense;
        $count = (clone $query)->count();

        $summary = [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net' => $net,
            'count' => $count,
        ];

        // ─── Categories for filter dropdown ──────────
        $categories = MiscellaneousTransaction::CATEGORIES;

        return Inertia::render('Miscellaneous/Index', [
            'transactions' => $transactions,
            'summary' => $summary,
            'filters' => [
                'type' => $type,
                'category' => $category,
                'search' => $search,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        $this->authorize('create', MiscellaneousTransaction::class);
        $categories = MiscellaneousTransaction::CATEGORIES;
        return Inertia::render('Miscellaneous/Create', ['categories' => $categories]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', MiscellaneousTransaction::class);
        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'category' => ['nullable', 'in:' . implode(',', MiscellaneousTransaction::CATEGORIES)],
            'description' => 'nullable|string',
            'reference_number' => 'nullable|string|max:100',
        ]);
        $validated['created_by'] = auth()->id();
        MiscellaneousTransaction::create($validated);
        return redirect()->route('misc.index')->with('success', 'Miscellaneous transaction saved.');
    }

    public function edit(MiscellaneousTransaction $misc)
    {
        $this->authorize('update', $misc);
        $categories = MiscellaneousTransaction::CATEGORIES;

        return Inertia::render('Miscellaneous/Edit', [
            'transaction' => [
                'id' => $misc->id,
                'type' => $misc->type,
                'date' => $misc->date?->format('Y-m-d') ?? '',
                'amount' => $misc->amount,
                'category' => $misc->category,
                'description' => $misc->description,
                'reference_number' => $misc->reference_number,
            ],
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, MiscellaneousTransaction $misc)
    {
        $this->authorize('update', $misc);
        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'category' => ['nullable', 'in:' . implode(',', MiscellaneousTransaction::CATEGORIES)],
            'description' => 'nullable|string',
            'reference_number' => 'nullable|string|max:100',
        ]);
        $misc->update($validated);
        return redirect()->route('misc.index')->with('success', 'Miscellaneous transaction updated.');
    }

    public function destroy(MiscellaneousTransaction $misc)
    {
        $this->authorize('delete', $misc);
        $misc->delete();
        return redirect()->route('misc.index')->with('success', 'Miscellaneous transaction deleted.');
    }

    // ─── Export CSV ──────────────────────────────
    public function exportCsv(Request $request)
    {
        $this->authorize('viewAny', MiscellaneousTransaction::class);

        // Apply same filters as index
        $query = MiscellaneousTransaction::query();
        if ($type = $request->input('type')) $query->where('type', $type);
        if ($category = $request->input('category')) $query->where('category', $category);
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }
        if ($dateFrom = $request->input('date_from')) $query->where('date', '>=', $dateFrom);
        if ($dateTo = $request->input('date_to')) $query->where('date', '<=', $dateTo);

        $items = $query->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="miscellaneous_transactions.csv"',
        ];

        $callback = function() use ($items) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Type', 'Date', 'Amount', 'Category', 'Description', 'Reference #']);
            foreach ($items as $item) {
                fputcsv($handle, [
                    $item->id,
                    $item->type,
                    $item->date->format('Y-m-d'),
                    $item->amount,
                    $item->category,
                    $item->description,
                    $item->reference_number,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}