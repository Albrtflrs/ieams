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

        // Generate reference number
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
        ]);

        // Auto‑generate reference number
        $prefix = Setting::get('retainer_prefix', 'RET-');
        $next = Setting::get('retainer_next_number', 1);
        $validated['reference_number'] = $prefix . str_pad($next, 6, '0', STR_PAD_LEFT);
        $validated['used_amount'] = 0;
        $validated['created_by'] = auth()->id();

        // 🔥 Debug: dump the array to confirm it has all keys
        // dd($validated); // uncomment if needed

        Retainer::create($validated);

        // Increment next number
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
}