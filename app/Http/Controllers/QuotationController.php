<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Client;
use App\Models\Item; // 👈 ADDED
use App\Models\IncomeTransaction;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Quotation::class);

        $perPage = Setting::get('rows_per_page', 20);
        $quotations = Quotation::with(['client', 'creator'])
            ->when(auth()->user()->role === 'staff' || auth()->user()->role === 'viewer', function ($q) {
                $q->where('created_by', auth()->id());
            })
            ->latest()
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

        return Inertia::render('Quotations/Index', [
            'quotations' => $quotations,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Quotation::class);

        $clients = Client::orderBy('name')->get(['id', 'name', 'address']);
        // 👇 FETCH ITEMS FROM CATALOG
        $items = Item::orderBy('name')->get(['id', 'name', 'description', 'default_cost_price', 'default_selling_price', 'default_markup_percentage']);

        $next = Quotation::count() + 1;
        $prefix = Setting::get('quotation_prefix', 'QT-');
        $number = $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);

        return Inertia::render('Quotations/Create', [
            'clients' => $clients,
            'items' => $items, // 👈 PASS TO VUE
            'quotation_number' => $number,
            'default_currency' => Setting::get('currency_symbol', '₱'),
            'user_role' => auth()->user()->role, // 👈 PASS ROLE
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
            'discount' => 'numeric|min:0',
            'tax' => 'numeric|min:0',
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
            // Auto-generate number if not provided
            if (empty($validated['quotation_number'])) {
                $next = Quotation::count() + 1;
                $prefix = Setting::get('quotation_prefix', 'QT-');
                $validated['quotation_number'] = $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
            }

            // Set client_name from client if provided
            if ($validated['client_id']) {
                $client = Client::find($validated['client_id']);
                if ($client) {
                    $validated['client_name'] = $client->name;
                    $validated['client_address'] = $validated['client_address'] ?? $client->address;
                }
            }

            $validated['created_by'] = auth()->id();

            $quotation = Quotation::create($validated);

            // Create items
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
        // 👇 FETCH ITEMS FROM CATALOG
        $items = Item::orderBy('name')->get(['id', 'name', 'description', 'default_cost_price', 'default_selling_price', 'default_markup_percentage']);
        $quotation->load('items');

        return Inertia::render('Quotations/Edit', [
            'quotation' => $quotation,
            'clients' => $clients,
            'items' => $items, // 👈 PASS TO VUE
            'user_role' => auth()->user()->role, // 👈 PASS ROLE
        ]);
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
            'discount' => 'numeric|min:0',
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

            // Sync items
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

            DB::commit();

            return redirect()->route('quotations.index')->with('success', 'Quotation updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update quotation: ' . $e->getMessage()]);
        }
    }

    public function destroy(Quotation $quotation)
    {
        $this->authorize('delete', $quotation);
        $quotation->delete();
        return redirect()->route('quotations.index')->with('success', 'Quotation deleted.');
    }

    // ─── Convert to Income ──────────────────────────
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
            $income = IncomeTransaction::create([
                'client_id' => $quotation->client_id,
                'agency_department' => $quotation->client_name,
                'particulars' => 'Quotation: ' . $quotation->quotation_number,
                'gross_price' => $quotation->total_amount,
                'amount_paid' => 0,
                'status' => 'Unpaid',
                'category' => 'QUOTATION',
                'date_delivered' => now(),
                'remarks' => 'Converted from quotation #' . $quotation->quotation_number,
                'created_by' => auth()->id(),
            ]);

            $quotation->update([
                'status' => 'converted',
                'converted_to_income_id' => $income->id,
            ]);

            DB::commit();

            return redirect()->route('income.edit', $income->id)
                ->with('success', 'Quotation converted to Income successfully. You can now manage payment.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to convert: ' . $e->getMessage()]);
        }
    }
}