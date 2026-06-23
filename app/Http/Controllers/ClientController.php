<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Client::class);

        $perPage = Setting::get('rows_per_page', 20);
        $clients = Client::latest()->paginate($perPage)
            ->through(fn($client) => [
                'id' => $client->id,
                'name' => $client->name,
                'contact_person' => $client->contact_person,
                'phone' => $client->phone,
                'email' => $client->email,
                'address' => $client->address,
                'created_at' => $client->created_at->format('Y-m-d'),
            ]);

        $totalClients = Client::count();
        $topClientRevenue = Client::withSum('incomeTransactions', 'amount_paid')
            ->orderBy('income_transactions_sum_amount_paid', 'desc')->first();
        $topClientCount = Client::withCount('incomeTransactions')
            ->orderBy('income_transactions_count', 'desc')->first();
        $totalRevenue = Client::with('incomeTransactions')->get()->sum(fn($c) => $c->incomeTransactions->sum('amount_paid'));

        $summary = [
            'total_clients' => $totalClients,
            'total_revenue' => $totalRevenue,
            'top_client_revenue' => $topClientRevenue ? ['name' => $topClientRevenue->name, 'amount' => $topClientRevenue->income_transactions_sum_amount_paid ?? 0] : null,
            'top_client_count' => $topClientCount ? ['name' => $topClientCount->name, 'count' => $topClientCount->income_transactions_count ?? 0] : null,
        ];

        return Inertia::render('Clients/Index', ['clients' => $clients, 'summary' => $summary]);
    }

    public function create()
    {
        $this->authorize('create', Client::class);
        return Inertia::render('Clients/Create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Client::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);
        $validated['created_by'] = auth()->id();
        Client::create($validated);
        return redirect()->route('clients.index')->with('success', 'Client created.');
    }

    public function edit(Client $client)
    {
        $this->authorize('update', $client);
        return Inertia::render('Clients/Edit', ['client' => $client]);
    }

    public function update(Request $request, Client $client)
    {
        $this->authorize('update', $client);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);
        $client->update($validated);
        return redirect()->route('clients.index')->with('success', 'Client updated.');
    }

    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client deleted.');
    }
}