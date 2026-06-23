<?php

namespace App\Http\Controllers;

use App\Models\MiscellaneousTransaction;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MiscellaneousController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', MiscellaneousTransaction::class);

        $perPage = Setting::get('rows_per_page', 20);

        $transactions = MiscellaneousTransaction::latest()
            ->paginate($perPage)
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

        $totalIncome = MiscellaneousTransaction::where('type', 'income')->sum('amount');
        $totalExpense = MiscellaneousTransaction::where('type', 'expense')->sum('amount');
        $net = $totalIncome - $totalExpense;
        $count = MiscellaneousTransaction::count();

        $summary = ['total_income' => $totalIncome, 'total_expense' => $totalExpense, 'net' => $net, 'count' => $count];

        return Inertia::render('Miscellaneous/Index', ['transactions' => $transactions, 'summary' => $summary]);
    }

    public function create()
    {
        $this->authorize('create', MiscellaneousTransaction::class);
        $categories = explode(',', Setting::get('default_misc_categories', 'Donation,Refund,Misc Sales,Other'));
        return Inertia::render('Miscellaneous/Create', ['categories' => $categories]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', MiscellaneousTransaction::class);
        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'category' => ['nullable', 'in:' . implode(',', Setting::get('default_misc_categories', 'Donation,Refund,Misc Sales,Other'))],
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
        $categories = explode(',', Setting::get('default_misc_categories', 'Donation,Refund,Misc Sales,Other'));

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
            'category' => ['nullable', 'in:' . implode(',', Setting::get('default_misc_categories', 'Donation,Refund,Misc Sales,Other'))],
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
}