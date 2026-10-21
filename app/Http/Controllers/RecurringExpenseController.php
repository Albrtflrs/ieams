<?php

namespace App\Http\Controllers;

use App\Models\ExpenseTransaction;
use App\Models\RecurringExpense;
use App\Services\RecurringExpenseGenerator;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecurringExpenseController extends Controller
{
    private function authorizeManagement(): void
    {
        abort_unless(auth()->user()?->hasRole(['super_admin', 'admin']), 403);
    }

    public function index()
    {
        $this->authorize('viewAny', ExpenseTransaction::class);

        $templates = RecurringExpense::with('supplier')
            ->orderByDesc('is_active')
            ->orderBy('next_due_date')
            ->paginate(20)
            ->through(fn ($template) => [
                'id' => $template->id,
                'supplier_name' => $template->supplier?->name,
                'subcategory' => $template->subcategory,
                'description' => $template->description,
                'amount' => (float) $template->amount,
                'payment_method' => $template->payment_method,
                'next_due_date' => $template->next_due_date->format('Y-m-d'),
                'is_active' => $template->is_active,
            ]);

        return Inertia::render('Expenses/Recurring/Index', ['templates' => $templates]);
    }

    public function create()
    {
        $this->authorize('create', ExpenseTransaction::class);

        return Inertia::render('Expenses/Recurring/Form', [
            'template' => null,
            'selectedSupplier' => null,
        ]);
    }

    public function store(Request $request, RecurringExpenseGenerator $generator)
    {
        $this->authorize('create', ExpenseTransaction::class);
        $validated = $this->validatedData($request);
        $validated['category'] = 'MONTHLY FIX BILLS';
        $validated['billing_day'] = (int) date('j', strtotime($validated['next_due_date']));
        $validated['created_by'] = auth()->id();

        $template = RecurringExpense::create($validated);
        $generator->generateDue();

        return redirect()->route('expenses.recurring.index')->with('success', 'Monthly bill schedule created.');
    }

    public function edit(RecurringExpense $recurringExpense)
    {
        $this->authorizeManagement();
        $recurringExpense->load('supplier');

        return Inertia::render('Expenses/Recurring/Form', [
            'template' => $recurringExpense,
            'selectedSupplier' => $recurringExpense->supplier ? [
                'id' => $recurringExpense->supplier->id,
                'name' => $recurringExpense->supplier->name,
            ] : null,
        ]);
    }

    public function update(Request $request, RecurringExpense $recurringExpense, RecurringExpenseGenerator $generator)
    {
        $this->authorizeManagement();
        $validated = $this->validatedData($request);
        $validated['category'] = 'MONTHLY FIX BILLS';
        $validated['billing_day'] = (int) date('j', strtotime($validated['next_due_date']));
        $validated['is_active'] = $request->boolean('is_active');

        $recurringExpense->update($validated);
        $generator->generateDue();

        return redirect()->route('expenses.recurring.index')->with('success', 'Monthly bill schedule updated.');
    }

    public function destroy(RecurringExpense $recurringExpense)
    {
        $this->authorizeManagement();
        $recurringExpense->delete();

        return redirect()->route('expenses.recurring.index')->with('success', 'Monthly bill schedule removed. Existing expenses were kept.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'subcategory' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string|max:50',
            'next_due_date' => 'required|date|after_or_equal:today',
            'is_active' => 'sometimes|boolean',
        ]);
    }
}
