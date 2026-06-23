<?php

namespace App\Http\Controllers;

use App\Models\Municipality;

class LocationController extends Controller
{
    /**
     * Return all Aklan municipalities with their barangays eager-loaded.
     * Used to populate the cascading Municipality -> Barangay select
     * on the income transaction form.
     */
    public function aklan()
    {
        return Municipality::where('province', 'Aklan')
            ->with('barangays:id,name,municipality_id')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'barangays' => $m->barangays->pluck('name')->sort()->values(),
            ]);
    }
}