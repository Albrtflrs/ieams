<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Client;
use App\Models\Item;
use App\Models\IncomeTransaction;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Quotation::class);

        // ─── Filters ──────────────────────────
        $clientId = $request->input('client_id');
        $dateFilter = $request->input('date_filter');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('status');

        $perPage = Setting::get('rows_per_page', 20);

        $query = Quotation::with(['client', 'creator'])
            ->when(auth()->user()->role === 'staff' || auth()->user()->role === 'viewer', function ($q) {
                $q->where('created_by', auth()->id());
            });

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($dateFilter) {
            switch ($dateFilter) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'this_week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                    break;
                case 'this_year':
                    $query->whereYear('created_at', now()->year);
                    break;
                case 'custom':
                    if ($startDate && $endDate) {
                        $query->whereBetween('created_at', [$startDate, $endDate]);
                    }
                    break;
            }
        }

        $quotations = $query->latest()
            ->paginate($perPage)
            ->through(fn($q) => [
                'id' => $q->id,
                'quotation_number' => $q->quotation_number,
                'client_name' => $q->client_name ?? $q->client?->name,
                'date_issued' => $q->date_issued?->format('Y-m-d'),
                'valid_until' => $q->valid_until?->format('Y-m-d'),
                'total_amount' => $q->total_amount,
                'status' => $q->status,
                'converted_to_income_id' => $q->converted_to_income_id,
                'created_by' => $q->creator?->name,
            ]);

        $items = Item::orderBy('name')->get(['id', 'name', 'description', 'default_cost_price', 'default_markup_percentage', 'default_selling_price']);
        $clients = Client::orderBy('name')->get(['id', 'name']);
        $statuses = ['draft', 'sent', 'accepted', 'rejected', 'expired', 'converted'];

        return Inertia::render('Quotations/Index', [
            'quotations' => $quotations,
            'items' => $items,
            'user_role' => auth()->user()->role,
            'categories' => IncomeTransaction::CATEGORIES,
            'clients' => $clients,
            'statuses' => $statuses,
            'filters' => [
                'client_id' => $clientId,
                'date_filter' => $dateFilter,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $status,
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('create', Quotation::class);

        $clients = Client::orderBy('name')->get(['id', 'name', 'address']);
        $items = Item::orderBy('name')->get(['id', 'name', 'description', 'default_cost_price', 'default_selling_price', 'default_markup_percentage', 'category']);

        $next = Quotation::count() + 1;
        $prefix = Setting::get('quotation_prefix', 'QT-');
        $number = $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);

        return Inertia::render('Quotations/Create', [
            'clients' => $clients,
            'items' => $items,
            'quotation_number' => $number,
            'default_currency' => Setting::get('currency_symbol', '₱'),
            'user_role' => auth()->user()->role,
            'categories' => IncomeTransaction::CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Quotation::class);

        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'client_name' => 'nullable|string|max:255',
            'client_address' => 'nullable|string',
            'date_issued' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:date_issued',
            'currency' => 'string|max:10',
            'subtotal' => 'numeric|min:0',
            'discount' => 'numeric|min:0|max:100',
            'tax' => 'numeric|min:0|max:100',
            'total_amount' => 'numeric|min:0',
            'markup_percentage' => 'numeric|min:0',
            'markup_amount' => 'numeric|min:0',
            'status' => 'required|in:draft,sent,accepted,rejected,expired',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.selling_price' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.total' => 'numeric|min:0',
            'items.*.category' => 'nullable|string',
            'items.*.markup_percentage' => 'nullable|numeric|min:0',
            'items.*.markup_amount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            if (empty($validated['quotation_number'])) {
                $next = Quotation::count() + 1;
                $prefix = Setting::get('quotation_prefix', 'QT-');
                $validated['quotation_number'] = $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
            }

            if ($validated['client_id']) {
                $client = Client::find($validated['client_id']);
                if ($client) {
                    $validated['client_name'] = $client->name;
                    $validated['client_address'] = $validated['client_address'] ?? $client->address;
                }
            }

            $validated['created_by'] = auth()->id();

            $quotation = Quotation::create($validated);

            $items = $request->input('items', []);
            foreach ($items as $item) {
                $item['quotation_id'] = $quotation->id;
                QuotationItem::create($item);
            }

            DB::commit();

            return redirect()->route('quotations.index')->with('success', 'Quotation created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create quotation: ' . $e->getMessage()]);
        }
    }

    public function show(Quotation $quotation)
    {
        $this->authorize('view', $quotation);
        $quotation->load(['items', 'client', 'creator']);

        return Inertia::render('Quotations/Show', [
            'quotation' => $quotation,
        ]);
    }

    public function edit(Quotation $quotation)
    {
        $this->authorize('update', $quotation);

        $clients = Client::orderBy('name')->get(['id', 'name', 'address']);
        $items = Item::orderBy('name')->get(['id', 'name', 'description', 'default_cost_price', 'default_selling_price', 'default_markup_percentage', 'category']);
        $quotation->load('items');

        return Inertia::render('Quotations/Edit', [
            'quotation' => $quotation,
            'clients' => $clients,
            'items' => $items,
            'quotation_number' => $quotation->quotation_number,
            'default_currency' => Setting::get('currency_symbol', '₱'),
            'user_role' => auth()->user()->role,
            'categories' => IncomeTransaction::CATEGORIES,
        ]);
    }

    protected function performConversionToIncome(Quotation $quotation)
    {
        if ($quotation->converted_to_income_id) {
            return null;
        }

        $prefix = Setting::get('invoice_prefix', 'INV-');
        $next = Setting::get('invoice_next_number', 1);
        $itemNo = $prefix . $next;

        $incomeData = [
            'item_no' => $itemNo,
            'client_id' => $quotation->client_id,
            'agency_department' => $quotation->client_name,
            'municipal_barangay' => $quotation->client_address ?? '',
            'particulars' => 'Quotation: ' . $quotation->quotation_number,
            'date_delivered' => $quotation->date_issued ?? now(),
            'amount_paid' => 0,
            'date_paid' => null,
            'receipt_number' => null,
            'gross_price' => $quotation->total_amount,
            'royalty_percent' => 0,
            'deductions' => 0,
            'category' => 'QUOTATION',
            'status' => 'Unpaid',
            'remarks' => 'Converted from quotation #' . $quotation->quotation_number,
            'created_by' => auth()->id(),
            'is_miscellaneous' => false,
        ];

        $income = IncomeTransaction::create($incomeData);

        Setting::updateOrCreate(['key' => 'invoice_next_number'], ['value' => $next + 1]);

        $quotation->update([
            'converted_to_income_id' => $income->id,
            'status' => 'converted',
        ]);

        return $income;
    }

    public function update(Request $request, Quotation $quotation)
    {
        $this->authorize('update', $quotation);

        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'client_name' => 'nullable|string|max:255',
            'client_address' => 'nullable|string',
            'date_issued' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:date_issued',
            'currency' => 'string|max:10',
            'subtotal' => 'numeric|min:0',
            'discount' => 'numeric|min:0|max:100',
            'tax' => 'numeric|min:0',
            'total_amount' => 'numeric|min:0',
            'markup_percentage' => 'numeric|min:0',
            'markup_amount' => 'numeric|min:0',
            'status' => 'required|in:draft,sent,accepted,rejected,expired,converted',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:quotation_items,id',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.selling_price' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.total' => 'numeric|min:0',
            'items.*.category' => 'nullable|string',
            'items.*.markup_percentage' => 'nullable|numeric|min:0',
            'items.*.markup_amount' => 'nullable|numeric|min:0',
        ]);

        $oldStatus = $quotation->status;

        DB::beginTransaction();

        try {
            if ($validated['client_id']) {
                $client = Client::find($validated['client_id']);
                if ($client) {
                    $validated['client_name'] = $client->name;
                    $validated['client_address'] = $validated['client_address'] ?? $client->address;
                }
            }

            $quotation->update($validated);

            $itemIds = [];
            foreach ($request->input('items', []) as $itemData) {
                if (isset($itemData['id']) && $itemData['id']) {
                    $item = QuotationItem::find($itemData['id']);
                    if ($item) {
                        $item->update($itemData);
                        $itemIds[] = $item->id;
                    }
                } else {
                    $itemData['quotation_id'] = $quotation->id;
                    $item = QuotationItem::create($itemData);
                    $itemIds[] = $item->id;
                }
            }

            $quotation->items()->whereNotIn('id', $itemIds)->delete();

            // ─── Auto-convert if enabled ──────────────────────────────
            $newStatus = $validated['status'];
            $autoConvert = Setting::get('auto_convert_accepted_quotations', false);

            if ($autoConvert && $newStatus === 'accepted' && $oldStatus !== 'accepted' && !$quotation->converted_to_income_id) {
                $this->performConversionToIncome($quotation);
            }

            DB::commit();

            if ($autoConvert && $newStatus === 'accepted' && $oldStatus !== 'accepted' && $quotation->converted_to_income_id) {
                return redirect()->route('quotations.index')
                    ->with('success', 'Quotation accepted and automatically converted to income.');
            }

            return redirect()->route('quotations.index')->with('success', 'Quotation updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quotation update failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update quotation: ' . $e->getMessage()]);
        }
    }

    public function destroy(Quotation $quotation)
    {
        $this->authorize('delete', $quotation);
        $quotation->delete();
        return redirect()->route('quotations.index')->with('success', 'Quotation deleted.');
    }

    public function convertToIncome(Quotation $quotation)
    {
        $this->authorize('convertToIncome', $quotation);

        if ($quotation->status !== 'accepted') {
            return back()->withErrors(['error' => 'Only accepted quotations can be converted.']);
        }

        if ($quotation->converted_to_income_id) {
            return back()->withErrors(['error' => 'This quotation has already been converted.']);
        }

        DB::beginTransaction();

        try {
            $income = $this->performConversionToIncome($quotation);
            DB::commit();
            return redirect()->route('income.edit', $income->id)
                ->with('success', 'Quotation converted to Income successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to convert: ' . $e->getMessage()]);
        }
    }

    // ─── Updated exportPdf ────────────────
    public function exportPdf(Quotation $quotation)
    {
        $this->authorize('view', $quotation);
        $quotation->load('items');
        $logoPath = Setting::get('logo_path');
        $pdf = Pdf::loadView('pdf.quotation', [
            'quotation' => $quotation,
            'logoPath'  => $logoPath,
        ]);
        return $pdf->download('quotation-' . $quotation->quotation_number . '.pdf');
    }

    public function exportCsv(Quotation $quotation)
    {
        $this->authorize('view', $quotation);
        $quotation->load('items');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="quotation-' . $quotation->quotation_number . '.csv"',
        ];

        $callback = function() use ($quotation) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Description', 'Quantity', 'Unit Price', 'Selling Price', 'Total']);

            foreach ($quotation->items as $item) {
                fputcsv($handle, [
                    $item->description,
                    $item->quantity,
                    $item->unit_price,
                    $item->selling_price,
                    $item->total,
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Subtotal', '', '', '', $quotation->subtotal]);

            if ($quotation->discount > 0) {
                fputcsv($handle, ['Discount (' . $quotation->discount . '%)', '', '', '', $quotation->subtotal * $quotation->discount / 100]);
            }

            if ($quotation->tax > 0) {
                $taxable = $quotation->subtotal - ($quotation->subtotal * $quotation->discount / 100);
                fputcsv($handle, ['Tax (' . $quotation->tax . '%)', '', '', '', $taxable * $quotation->tax / 100]);
            }

            fputcsv($handle, ['Total Amount', '', '', '', $quotation->total_amount]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ─── TRASH ─────────────────────────────────────────────────────────
    public function trash()
    {
        $this->authorize('viewTrash', Quotation::class);

        $quotations = Quotation::onlyTrashed()
            ->with('client')
            ->latest('deleted_at')
            ->paginate(20)
            ->through(fn($item) => [
                'id'                => $item->id,
                'quotation_number'  => $item->quotation_number,
                'client_name'       => $item->client?->name,
                'total_amount'      => $item->total_amount,
                'status'            => $item->status,
                'deleted_at'        => $item->deleted_at->format('Y-m-d H:i:s'),
            ]);

        return Inertia::render('Quotations/Trash', [
            'quotations' => $quotations,
        ]);
    }

    public function restore($id)
    {
        $quotation = Quotation::withTrashed()->findOrFail($id);
        $this->authorize('restore', $quotation);
        $quotation->restore();
        return redirect()->route('quotations.trash')->with('success', 'Quotation restored.');
    }

    public function forceDelete($id)
    {
        $quotation = Quotation::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $quotation);
        $quotation->forceDelete();
        return redirect()->route('quotations.trash')->with('success', 'Quotation permanently deleted.');
    }
}