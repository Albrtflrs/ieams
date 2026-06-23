<?php

namespace App\Http\Controllers;

use App\Models\IncomeTransaction;
use App\Models\Client;
use App\Models\Municipality;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class IncomeTransactionController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', IncomeTransaction::class);

        // ── Get filters ──
        $category = request('category', '');
        $status = request('status', '');
        $search = request('search', '');

        // ── Summary Query ──
        $summaryQuery = IncomeTransaction::query();
        if ($category !== '') {
            $summaryQuery->where('category', $category);
        }
        if ($status !== '') {
            $summaryQuery->where('status', $status);
        }
        if ($search !== '') {
            $summaryQuery->where(function ($q) use ($search) {
                $q->where('agency_department', 'like', "%{$search}%")
                  ->orWhere('particulars', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        $categoryTotals = $summaryQuery->select('category', DB::raw('SUM(gross_price) as total'))
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        $aggregates = $summaryQuery->select(
            DB::raw('SUM(gross_price) as gross_profit'),
            DB::raw('SUM(amount_paid) as amount_paid'),
            DB::raw('SUM(gross_price - amount_paid) as receivables'),
            DB::raw('SUM(gross_price - COALESCE(deductions, 0)) as net_sales'),
            DB::raw('SUM(COALESCE(royalty_percent, 0) * gross_price / 100) as royalty_gross'),
            DB::raw('SUM(COALESCE(royalty_percent, 0) * (gross_price - COALESCE(deductions, 0)) / 100) as royalty_net')
        )->first();

        $summary = [
            'categories' => $categoryTotals,
            'gross_profit' => $aggregates->gross_profit ?? 0,
            'amount_paid' => $aggregates->amount_paid ?? 0,
            'receivables' => $aggregates->receivables ?? 0,
            'net_sales' => $aggregates->net_sales ?? 0,
            'royalty_gross' => $aggregates->royalty_gross ?? 0,
            'royalty_net' => $aggregates->royalty_net ?? 0,
        ];

        // ── Transactions Query ──
        $perPage = Setting::get('rows_per_page', 20);

        $transactionsQuery = IncomeTransaction::with('client');
        if ($category !== '') {
            $transactionsQuery->where('category', $category);
        }
        if ($status !== '') {
            $transactionsQuery->where('status', $status);
        }
        if ($search !== '') {
            $transactionsQuery->where(function ($q) use ($search) {
                $q->where('agency_department', 'like', "%{$search}%")
                  ->orWhere('particulars', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        $transactions = $transactionsQuery->latest()
            ->paginate($perPage)
            ->through(fn($item) => [
                'id' => $item->id,
                'item_no' => $item->item_no,
                'client_name' => $item->client?->name,
                'agency_department' => $item->agency_department,
                'municipality' => $item->municipal_barangay ? explode(',', $item->municipal_barangay)[0] ?? '' : '',
                'barangay' => $item->municipal_barangay ? (explode(',', $item->municipal_barangay)[1] ?? '') : '',
                'particulars' => $item->particulars,
                'category' => $item->category,
                'date_delivered' => $item->date_delivered?->format('Y-m-d'),
                'date_paid' => $item->date_paid?->format('Y-m-d'),
                'receipt_number' => $item->receipt_number,
                'gross_price' => $item->gross_price,
                'amount_paid' => $item->amount_paid,
                'royalty_gross' => $item->royalty_gross ?? 0,
                'deductions' => $item->deductions,
                'net_sales' => $item->net_sales ?? 0,
                'receivables' => $item->receivables ?? 0,
                'status' => $item->status,
                'withdrawn' => $item->withdrawn,
                'created_at' => optional($item->created_at)->format('Y-m-d'),
            ]);

        return Inertia::render('Income/Index', [
            'transactions' => $transactions,
            'summary' => $summary,
            'filters' => [
                'category' => $category,
                'status' => $status,
                'search'  => $search,
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('create', IncomeTransaction::class);
        $clients = Client::orderBy('name')->get(['id', 'name']);
        $municipalities = $this->getMunicipalitiesWithBarangays();

        $categories = explode(',', Setting::get('default_income_categories', 'CCTV AND SUPPLIES,OFFICE SUPPLIES,IT EQUIPMENT,SOFTWARE,ELECTRONICS/AIRCON,FURNITURE,KITCHENWARE,SOLAR,OTHERS'));
        $defaultRoyalty = Setting::get('default_royalty_rate', 0);

        return Inertia::render('Income/Create', [
            'clients' => $clients,
            'municipalities' => $municipalities,
            'categories' => $categories,
            'default_royalty_rate' => $defaultRoyalty,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', IncomeTransaction::class);

        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'agency_department' => 'required|string|max:255',
            'municipal_barangay' => 'nullable|string|max:255',
            'particulars' => 'required|string',
            'date_delivered' => 'nullable|date',
            'amount_paid' => 'nullable|numeric|min:0',
            'date_paid' => 'nullable|date',
            'receipt_number' => 'nullable|string|max:100',
            'gross_price' => 'required|numeric|min:0',
            'royalty_percent' => 'nullable|numeric|min:0|max:100',
            'deductions' => 'nullable|numeric|min:0',
            'category' => ['required', 'in:' . implode(',', Setting::get('default_income_categories', 'CCTV AND SUPPLIES,OFFICE SUPPLIES,IT EQUIPMENT,SOFTWARE,ELECTRONICS/AIRCON,FURNITURE,KITCHENWARE,SOLAR,OTHERS'))],
            'withdrawn' => 'boolean',
            'status' => 'required|in:Paid,Unpaid,Cash On Hold,Paid Royalty',
            'remarks' => 'nullable|string',
            'is_miscellaneous' => 'boolean',
        ]);

        // Auto‑generate item_no
        $prefix = Setting::get('invoice_prefix', 'INV-');
        $next = Setting::get('invoice_next_number', 1);
        $validated['item_no'] = $prefix . $next;

        $validated['created_by'] = auth()->id();
        IncomeTransaction::create($validated);

        // Increment next number
        Setting::updateOrCreate(['key' => 'invoice_next_number'], ['value' => $next + 1]);

        return redirect()->route('income.index')->with('success', 'Income recorded.');
    }

    public function edit(IncomeTransaction $income)
    {
        $this->authorize('update', $income);
        $clients = Client::orderBy('name')->get(['id', 'name']);
        $municipalities = $this->getMunicipalitiesWithBarangays();

        $categories = explode(',', Setting::get('default_income_categories', 'CCTV AND SUPPLIES,OFFICE SUPPLIES,IT EQUIPMENT,SOFTWARE,ELECTRONICS/AIRCON,FURNITURE,KITCHENWARE,SOLAR,OTHERS'));

        return Inertia::render('Income/Edit', [
            'transaction' => $income,
            'clients' => $clients,
            'municipalities' => $municipalities,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, IncomeTransaction $income)
    {
        $this->authorize('update', $income);
        $validated = $request->validate([
            'item_no' => 'nullable|string|max:50',
            'client_id' => 'nullable|exists:clients,id',
            'agency_department' => 'required|string|max:255',
            'municipal_barangay' => 'nullable|string|max:255',
            'particulars' => 'required|string',
            'date_delivered' => 'nullable|date',
            'amount_paid' => 'required|numeric|min:0',
            'date_paid' => 'nullable|date',
            'receipt_number' => 'nullable|string|max:100',
            'gross_price' => 'required|numeric|min:0',
            'royalty_percent' => 'nullable|numeric|min:0|max:100',
            'deductions' => 'nullable|numeric|min:0',
            'category' => ['required', 'in:' . implode(',', Setting::get('default_income_categories', 'CCTV AND SUPPLIES,OFFICE SUPPLIES,IT EQUIPMENT,SOFTWARE,ELECTRONICS/AIRCON,FURNITURE,KITCHENWARE,SOLAR,OTHERS'))],
            'withdrawn' => 'boolean',
            'status' => 'required|in:Paid,Unpaid,Cash On Hold,Paid Royalty',
            'remarks' => 'nullable|string',
            'is_miscellaneous' => 'boolean',
        ]);

        $income->update($validated);
        return redirect()->route('income.index')->with('success', 'Income updated.');
    }

    public function destroy(IncomeTransaction $income)
    {
        $this->authorize('delete', $income);
        $income->delete();
        return redirect()->route('income.index')->with('success', 'Income deleted.');
    }

    private function getMunicipalitiesWithBarangays(): array
    {
        return Municipality::where('province', 'Aklan')
            ->with('barangays:id,name,municipality_id')
            ->orderBy('name')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'barangays' => $m->barangays->pluck('name')->sort()->values()->all(),
            ])
            ->toArray();
    }
}