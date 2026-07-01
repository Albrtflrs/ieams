<?php

namespace App\Http\Controllers;

use App\Models\Retainer;
use App\Models\Client;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class RetainerController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Retainer::class);

        $perPage = Setting::get('rows_per_page', 20);

        $retainers = Retainer::with('client')
            ->latest()
            ->paginate($perPage)
            ->through(fn($item) => [
                'id' => $item->id,
                'reference_number' => $item->reference_number,
                'client_name' => $item->client?->name,
                'total_amount' => $item->total_amount,
                'used_amount' => $item->used_amount,
                'remaining_balance' => $item->remaining_balance,
                'start_date' => $item->start_date->format('Y-m-d'),
                'end_date' => $item->end_date?->format('Y-m-d'),
                'status' => $item->status,
                'description' => $item->description,
                'billing_frequency' => $item->billing_frequency,
                'payment_terms' => $item->payment_terms,
                'auto_renew' => $item->auto_renew,
                'allocated_hours' => $item->allocated_hours,
                'overage_hourly_rate' => $item->overage_hourly_rate,
                'rollover_allowed' => $item->rollover_allowed,
                'sla_tier' => $item->sla_tier,
                'contract_path' => $item->contract_path,
                'services' => $item->services,
                'created_at' => $item->created_at->format('Y-m-d'),
            ]);

        // Summary
        $totalActive = Retainer::where('status', 'active')->sum('total_amount');
        $totalUsed = Retainer::sum('used_amount');
        $totalRemaining = Retainer::sum(DB::raw('total_amount - used_amount'));
        $count = Retainer::count();

        $summary = [
            'total_active' => $totalActive,
            'total_used' => $totalUsed,
            'total_remaining' => $totalRemaining,
            'count' => $count,
        ];

        return Inertia::render('Retainers/Index', [
            'retainers' => $retainers,
            'summary' => $summary,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Retainer::class);
        $clients = Client::orderBy('name')->get(['id', 'name']);

        $prefix = Setting::get('retainer_prefix', 'RET-');
        $next = Setting::get('retainer_next_number', 1);
        $reference = $prefix . str_pad($next, 6, '0', STR_PAD_LEFT);

        return Inertia::render('Retainers/Create', [
            'clients' => $clients,
            'reference_number' => $reference,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Retainer::class);

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'total_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|in:active,used_up,expired',
            'description' => 'nullable|string',
            'billing_frequency' => 'nullable|in:monthly,quarterly,annually',
            'payment_terms' => 'nullable|in:due_on_receipt,net_10,net_30',
            'auto_renew' => 'boolean',
            'allocated_hours' => 'nullable|integer|min:0',
            'overage_hourly_rate' => 'nullable|numeric|min:0',
            'rollover_allowed' => 'boolean',
            'sla_tier' => 'nullable|in:bronze,silver,gold',
            'contract_path' => 'nullable|string|max:255',
            'services' => 'nullable|array',
            'services.*.name' => 'required_with:services|string|max:255',
            'services.*.description' => 'nullable|string',
        ]);

        $prefix = Setting::get('retainer_prefix', 'RET-');
        $next = Setting::get('retainer_next_number', 1);
        $validated['reference_number'] = $prefix . str_pad($next, 6, '0', STR_PAD_LEFT);
        $validated['used_amount'] = 0;
        $validated['created_by'] = auth()->id();

        Retainer::create($validated);

        Setting::updateOrCreate(['key' => 'retainer_next_number'], ['value' => $next + 1]);

        return redirect()->route('retainers.index')->with('success', 'Retainer created.');
    }

    public function edit(Retainer $retainer)
    {
        $this->authorize('update', $retainer);
        $clients = Client::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Retainers/Edit', [
            'retainer' => $retainer,
            'clients' => $clients,
        ]);
    }

    public function update(Request $request, Retainer $retainer)
    {
        $this->authorize('update', $retainer);

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'total_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|in:active,used_up,expired',
            'description' => 'nullable|string',
            'billing_frequency' => 'nullable|in:monthly,quarterly,annually',
            'payment_terms' => 'nullable|in:due_on_receipt,net_10,net_30',
            'auto_renew' => 'boolean',
            'allocated_hours' => 'nullable|integer|min:0',
            'overage_hourly_rate' => 'nullable|numeric|min:0',
            'rollover_allowed' => 'boolean',
            'sla_tier' => 'nullable|in:bronze,silver,gold',
            'contract_path' => 'nullable|string|max:255',
            'services' => 'nullable|array',
            'services.*.name' => 'required_with:services|string|max:255',
            'services.*.description' => 'nullable|string',
        ]);

        $retainer->update($validated);

        return redirect()->route('retainers.index')->with('success', 'Retainer updated.');
    }

    public function destroy(Retainer $retainer)
    {
        $this->authorize('delete', $retainer);
        $retainer->delete();
        return redirect()->route('retainers.index')->with('success', 'Retainer deleted.');
    }

    // ─── TRASH ─────────────────────────────────────────────────────────
    public function trash()
    {
        $this->authorize('viewTrash', Retainer::class);

        $retainers = Retainer::onlyTrashed()
            ->with('client')
            ->latest('deleted_at')
            ->paginate(20)
            ->through(fn($item) => [
                'id'                => $item->id,
                'reference_number'  => $item->reference_number,
                'client_name'       => $item->client?->name,
                'total_amount'      => $item->total_amount,
                'used_amount'       => $item->used_amount,
                'remaining_balance' => $item->remaining_balance,
                'start_date'        => $item->start_date->format('Y-m-d'),
                'end_date'          => $item->end_date?->format('Y-m-d'),
                'status'            => $item->status,
                'deleted_at'        => $item->deleted_at->format('Y-m-d H:i:s'),
            ]);

        return Inertia::render('Retainers/Trash', [
            'retainers' => $retainers,
        ]);
    }

    // ─── RESTORE ────────────────────────────────────────────────────────
    public function restore($id)
    {
        $retainer = Retainer::withTrashed()->findOrFail($id);
        $this->authorize('restore', $retainer);
        $retainer->restore();
        return redirect()->route('retainers.trash')->with('success', 'Retainer restored.');
    }

    // ─── FORCE DELETE ──────────────────────────────────────────────────
    public function forceDelete($id)
    {
        $retainer = Retainer::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $retainer);
        $retainer->forceDelete();
        return redirect()->route('retainers.trash')->with('success', 'Retainer permanently deleted.');
    }
}