<?php

namespace App\Http\Controllers;

use App\Models\IncomeTransaction;
use App\Models\Client;
use App\Models\Municipality;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IncomeTransactionController extends Controller
{
    private const FALLBACK_CATEGORIES = 'CCTV AND SUPPLIES,OFFICE SUPPLIES,IT EQUIPMENT,SOFTWARE,ELECTRONICS/AIRCON,FURNITURE,KITCHENWARE,SOLAR,OTHERS';

    // ─── Helper: Generate Receipt Number ──────────────────────────────
    protected function generateReceiptNumber()
    {
        $prefix = Setting::get('receipt_prefix', 'RCPT-');
        $next = Setting::get('receipt_next_number', 1);
        $number = $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
        Setting::updateOrCreate(['key' => 'receipt_next_number'], ['value' => $next + 1]);
        return $number;
    }

    // ─── Index ──────────────────────────────────────────────────────────
    public function index()
    {
        $this->authorize('viewAny', IncomeTransaction::class);

        // ── Get filters ──
        $category = request('category', '');
        $status = request('status', '');
        $search = request('search', '');
        $dateFrom = request('date_from', '');
        $dateTo = request('date_to', '');

        // ── Build filter conditions ──
        $conditions = [];
        $bindings = [];

        if ($category !== '') {
            $conditions[] = 'category = ?';
            $bindings[] = $category;
        }
        if ($status !== '') {
            $conditions[] = 'status = ?';
            $bindings[] = $status;
        }
        if ($search !== '') {
            $conditions[] = '(agency_department LIKE ? OR particulars LIKE ? OR client_id IN (SELECT id FROM clients WHERE name LIKE ?))';
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
        }
        if ($dateFrom !== '') {
            $conditions[] = 'date_delivered >= ?';
            $bindings[] = $dateFrom;
        }
        if ($dateTo !== '') {
            $conditions[] = 'date_delivered <= ?';
            $bindings[] = $dateTo;
        }

        $whereClause = count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '';

        // ── Category totals ──
        $categorySql = "SELECT category, SUM(gross_price) as total FROM income_transactions {$whereClause} GROUP BY category";
        $categoryTotalsResult = DB::select($categorySql, $bindings);
        $categoryTotals = [];
        foreach ($categoryTotalsResult as $row) {
            $categoryTotals[$row->category] = (float) $row->total;
        }

        // ── Summary totals ──
        $summarySql = "
            SELECT
                SUM(gross_price) as gross_profit,
                SUM(amount_paid) as amount_paid,
                SUM(gross_price - COALESCE(amount_paid, 0)) as receivables,
                SUM(gross_price - COALESCE(deductions, 0)) as net_sales,
                SUM(COALESCE(royalty_percent, 0) * gross_price / 100) as royalty_gross,
                SUM(COALESCE(royalty_percent, 0) * (gross_price - COALESCE(deductions, 0)) / 100) as royalty_net
            FROM income_transactions
            {$whereClause}
        ";
        $aggregates = DB::select($summarySql, $bindings);
        $aggregates = $aggregates[0] ?? null;

        // ── Status counts (global, for all filtered records) ──
        $statusSql = "SELECT status, COUNT(*) as count FROM income_transactions {$whereClause} GROUP BY status";
        $statusCountsResult = DB::select($statusSql, $bindings);
        $statusCounts = [];
        foreach ($statusCountsResult as $row) {
            $statusCounts[$row->status] = (int) $row->count;
        }
        // Ensure all statuses are present (even if zero)
        $allStatuses = ['Paid', 'Unpaid', 'Cash On Hold', 'Paid Royalty'];
        foreach ($allStatuses as $s) {
            if (!isset($statusCounts[$s])) {
                $statusCounts[$s] = 0;
            }
        }

        $summary = [
            'categories'     => $categoryTotals,
            'gross_profit'   => (float) ($aggregates->gross_profit ?? 0),
            'amount_paid'    => (float) ($aggregates->amount_paid ?? 0),
            'receivables'    => (float) ($aggregates->receivables ?? 0),
            'net_sales'      => (float) ($aggregates->net_sales ?? 0),
            'royalty_gross'  => (float) ($aggregates->royalty_gross ?? 0),
            'royalty_net'    => (float) ($aggregates->royalty_net ?? 0),
            'status_counts'  => $statusCounts, // 👈 added
        ];

        Log::info('Income Summary (Raw)', $summary);

        // ── Transactions (paginated) ──
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
        if ($dateFrom !== '') {
            $transactionsQuery->whereDate('date_delivered', '>=', $dateFrom);
        }
        if ($dateTo !== '') {
            $transactionsQuery->whereDate('date_delivered', '<=', $dateTo);
        }

        $transactions = $transactionsQuery->latest()
            ->paginate($perPage)
            ->through(fn($item) => [
                'id'               => $item->id,
                'item_no'          => $item->item_no,
                'client_name'      => $item->client?->name,
                'agency_department'=> $item->agency_department,
                'municipality'     => $item->municipal_barangay ? explode(',', $item->municipal_barangay)[0] ?? '' : '',
                'barangay'         => $item->municipal_barangay ? (explode(',', $item->municipal_barangay)[1] ?? '') : '',
                'particulars'      => $item->particulars,
                'category'         => $item->category,
                'date_delivered'   => $item->date_delivered?->format('Y-m-d'),
                'date_paid'        => $item->date_paid?->format('Y-m-d'),
                'receipt_number'   => $item->receipt_number,
                'gross_price'      => (float) $item->gross_price,
                'amount_paid'      => (float) $item->amount_paid,
                'royalty_gross'    => (float) (($item->royalty_percent ?? 0) * $item->gross_price / 100),
                'deductions'       => (float) ($item->deductions ?? 0),
                'net_sales'        => (float) ($item->gross_price - ($item->deductions ?? 0)),
                'receivables'      => (float) ($item->gross_price - $item->amount_paid),
                'royalty_net'      => (float) (($item->royalty_percent ?? 0) * ($item->gross_price - ($item->deductions ?? 0)) / 100),
                'status'           => $item->status,
                'withdrawn'        => (bool) $item->withdrawn,
                'created_at'       => optional($item->created_at)->format('Y-m-d'),
            ]);

        // ── Category list from settings (for the frontend) ──
        $categories = explode(',', Setting::get('default_income_categories', self::FALLBACK_CATEGORIES));

        return Inertia::render('Income/Index', [
            'transactions' => $transactions,
            'summary'      => $summary,
            'filters'      => [
                'category'   => $category,
                'status'     => $status,
                'search'     => $search,
                'date_from'  => $dateFrom,
                'date_to'    => $dateTo,
            ],
            'categories'   => $categories, // 👈 added
        ]);
    }

    // ─── Create ─────────────────────────────────────────────────────────
    public function create()
    {
        $this->authorize('create', IncomeTransaction::class);
        $clients = Client::orderBy('name')->get(['id', 'name']);
        $municipalities = $this->getMunicipalitiesWithBarangays();

        $categories = explode(',', Setting::get('default_income_categories', self::FALLBACK_CATEGORIES));
        $defaultRoyalty = Setting::get('default_royalty_rate', 0);

        return Inertia::render('Income/Create', [
            'clients'              => $clients,
            'municipalities'       => $municipalities,
            'categories'           => $categories,
            'default_royalty_rate' => $defaultRoyalty,
        ]);
    }

    // ─── Store ──────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $this->authorize('create', IncomeTransaction::class);

        $categories = Setting::get('default_income_categories', self::FALLBACK_CATEGORIES);

        $validated = $request->validate([
            'client_id'          => 'nullable|exists:clients,id',
            'agency_department'  => 'required|string|max:255',
            'municipal_barangay' => 'nullable|string|max:255',
            'particulars'        => 'required|string',
            'date_delivered'     => 'nullable|date',
            'amount_paid'        => 'nullable|numeric|min:0',
            'date_paid'          => 'nullable|date',
            'receipt_number'     => 'nullable|string|max:100',
            'gross_price'        => 'required|numeric|min:0',
            'royalty_percent'    => 'nullable|numeric|min:0|max:100',
            'deductions'         => 'nullable|numeric|min:0',
            'category'           => ['required', 'in:' . $categories],
            'withdrawn'          => 'boolean',
            'status'             => 'required|in:Paid,Unpaid,Cash On Hold,Paid Royalty',
            'remarks'            => 'nullable|string',
            'is_miscellaneous'   => 'boolean',
        ]);

        // Generate item number
        $invoicePrefix = Setting::get('invoice_prefix', 'INV-');
        $invoiceNext = Setting::get('invoice_next_number', 1);
        $validated['item_no'] = $invoicePrefix . str_pad($invoiceNext, 4, '0', STR_PAD_LEFT);
        Setting::updateOrCreate(['key' => 'invoice_next_number'], ['value' => $invoiceNext + 1]);

        // Generate receipt number
        $validated['receipt_number'] = $this->generateReceiptNumber();

        $validated['created_by'] = auth()->id();
        IncomeTransaction::create($validated);

        return redirect()->route('income.index')->with('success', 'Income recorded.');
    }

    // ─── Edit ───────────────────────────────────────────────────────────
    public function edit(IncomeTransaction $income)
    {
        $this->authorize('update', $income);
        $clients = Client::orderBy('name')->get(['id', 'name']);
        $municipalities = $this->getMunicipalitiesWithBarangays();

        $categories = explode(',', Setting::get('default_income_categories', self::FALLBACK_CATEGORIES));

        $transaction = $income->toArray();
        $transaction['date_paid'] = $income->date_paid?->format('Y-m-d');
        $transaction['date_delivered'] = $income->date_delivered?->format('Y-m-d');

        return Inertia::render('Income/Edit', [
            'transaction'   => $transaction,
            'clients'       => $clients,
            'municipalities'=> $municipalities,
            'categories'    => $categories,
        ]);
    }

    // ─── Show ───────────────────────────────────────────────────────────
    public function show(IncomeTransaction $income)
    {
        $this->authorize('view', $income);
        return redirect()->route('income.edit', $income->id);
    }

    // ─── Update ─────────────────────────────────────────────────────────
    public function update(Request $request, IncomeTransaction $income)
    {
        $this->authorize('update', $income);

        // ── Pre‑fill date_paid if status is Paid and missing ──
        if ($request->input('status') === 'Paid' && empty($request->input('date_paid'))) {
            $request->merge(['date_paid' => now()->toDateString()]);
        }

        $categories = Setting::get('default_income_categories', self::FALLBACK_CATEGORIES);

        $rules = [
            'item_no'            => 'nullable|string|max:50',
            'client_id'          => 'nullable|exists:clients,id',
            'agency_department'  => 'required|string|max:255',
            'municipal_barangay' => 'nullable|string|max:255',
            'particulars'        => 'required|string',
            'date_delivered'     => 'nullable|date',
            'amount_paid'        => 'required|numeric|min:0',
            'date_paid'          => 'nullable|date',
            'receipt_number'     => 'nullable|string|max:100',
            'gross_price'        => 'required|numeric|min:0',
            'royalty_percent'    => 'nullable|numeric|min:0|max:100',
            'deductions'         => 'nullable|numeric|min:0',
            'category'           => ['required', 'in:' . $categories],
            'withdrawn'          => 'boolean',
            'status'             => 'required|in:Paid,Unpaid,Cash On Hold,Paid Royalty',
            'remarks'            => 'nullable|string',
            'is_miscellaneous'   => 'boolean',
        ];

        $validated = $request->validate($rules);

        // ─── Auto‑generate receipt if status is Paid and receipt is missing ──
        if ($request->input('status') === 'Paid' && empty($validated['receipt_number'])) {
            $validated['receipt_number'] = $this->generateReceiptNumber();
        }

        // ─── Auto‑fill amount_paid if Paid and amount_paid is 0 ──
        if ($request->input('status') === 'Paid' && ($validated['amount_paid'] ?? 0) == 0) {
            $validated['amount_paid'] = $validated['gross_price'] ?? 0;
        }

        $income->update($validated);

        return redirect()->route('income.index')->with('success', 'Income updated.');
    }

    // ─── Destroy ────────────────────────────────────────────────────────
    public function destroy(IncomeTransaction $income)
    {
        $this->authorize('delete', $income);
        $income->delete();
        return redirect()->route('income.index')->with('success', 'Income deleted.');
    }

    // ─── Mark as Paid ──────────────────────────────────────────────────
    public function markPaid(Request $request, IncomeTransaction $income)
    {
        $this->authorize('update', $income);

        $validated = $request->validate([
            'receipt_number' => 'nullable|string|max:100',
        ]);

        // Use provided receipt number, or generate one
        $receiptNumber = $validated['receipt_number'] ?? $income->receipt_number;
        if (empty($receiptNumber)) {
            $receiptNumber = $this->generateReceiptNumber();
        }

        $income->update([
            'status'         => 'Paid',
            'amount_paid'    => $income->gross_price,
            'date_paid'      => now()->toDateString(),
            'receipt_number' => $receiptNumber,
        ]);

        return redirect()->route('income.index')->with('success', 'Income marked as paid.');
    }

    // ─── Helper: Municipalities with Barangays ────────────────────────
    private function getMunicipalitiesWithBarangays(): array
    {
        return Municipality::where('province', 'Aklan')
            ->with('barangays:id,name,municipality_id')
            ->orderBy('name')
            ->get()
            ->map(fn ($m) => [
                'id'        => $m->id,
                'name'      => $m->name,
                'barangays' => $m->barangays->pluck('name')->sort()->values()->all(),
            ])
            ->toArray();
    }
}