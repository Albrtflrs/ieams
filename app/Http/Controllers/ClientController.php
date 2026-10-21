<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function index(Request $request) 
    {
        $this->authorize('viewAny', Client::class);

        $search = $request->input('search');
        $clientType = $request->input('client_type', '');

        $perPage = Setting::get('rows_per_page', 20);

        $query = Client::query();

        if (in_array($clientType, ['Government', 'Private Institution'], true)) {
            $query->where('institution_type', $clientType);
        }

        // Match every search word, allowing names to be entered in any order.
        foreach (preg_split('/\s+/', trim((string) $search), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $term) {
            $query->where(function ($q) use ($term) {
                $like = "%{$term}%";
                $q->where('name', 'like', $like)
                  ->orWhere('contact_person', 'like', $like)
                  ->orWhere('email', 'like', $like)
                  ->orWhere('phone', 'like', $like)
                  ->orWhere('address', 'like', $like);
            });
        }

        $clients = $query->latest()
            ->paginate($perPage)
            ->withQueryString() // 👈 keeps search in pagination links
            ->through(fn($client) => [
                'id' => $client->id,
                'name' => $client->name,
                'institution_type' => $client->institution_type,
                'contact_person' => $client->contact_person,
                'phone' => $client->phone,
                'email' => $client->email,
                'address' => $client->address,
                'created_at' => $client->created_at->format('Y-m-d'),
            ]);

        // ─── Summary calculations (unchanged) ───
        $totalClients = Client::count();
        $topClientRevenue = Client::withSum('incomeTransactions', 'amount_paid')
            ->orderBy('income_transactions_sum_amount_paid', 'desc')->first();
        $topClientCount = Client::withCount('incomeTransactions')
            ->orderBy('income_transactions_count', 'desc')->first();
        $totalRevenue = DB::table('income_transactions')
            ->join('clients', 'clients.id', '=', 'income_transactions.client_id')
            ->whereNull('clients.deleted_at')
            ->sum('income_transactions.amount_paid');

        $summary = [
            'total_clients' => $totalClients,
            'total_revenue' => $totalRevenue,
            'top_client_revenue' => $topClientRevenue ? ['name' => $topClientRevenue->name, 'amount' => $topClientRevenue->income_transactions_sum_amount_paid ?? 0] : null,
            'top_client_count' => $topClientCount ? ['name' => $topClientCount->name, 'count' => $topClientCount->income_transactions_count ?? 0] : null,
        ];

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'summary' => $summary,
            'filters' => ['search' => $search, 'client_type' => $clientType],
        ]);
    }

    public function search(Request $request)
    {
        $this->authorize('viewAny', Client::class);

        $search = trim((string) $request->query('q', ''));
        if (mb_strlen($search) < 2) {
            return response()->json([]);
        }

        $query = Client::query();
        foreach (preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $term) {
            $query->where('name', 'like', "%{$term}%");
        }

        return response()->json($query->orderBy('name')->limit(20)->get(['id', 'name', 'institution_type']));
    }

    // ─── Other methods (create, store, edit, update, destroy) remain unchanged ───
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
            'institution_type' => 'required|in:Government,Private Institution',
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
            'institution_type' => 'required|in:Government,Private Institution',
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
