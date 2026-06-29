<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function store(Request $request)
    {
        // Only admins can add items
        if (!in_array(auth()->user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_cost_price' => 'nullable|numeric|min:0',
            'default_markup_percentage' => 'nullable|numeric|min:0',
            'default_selling_price' => 'nullable|numeric|min:0',
            'category' => 'nullable|string|max:255',
        ]);

        $validated['created_by'] = Auth::id();

        $item = Item::create($validated);

        return redirect()->back()->with('success', 'Item added successfully.');
    }

    public function destroy(Item $item)
    {
        // Only admins can delete items
        if (!in_array(auth()->user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Unauthorized');
        }

        $item->delete();
        return redirect()->back()->with('success', 'Item deleted.');
    }
}