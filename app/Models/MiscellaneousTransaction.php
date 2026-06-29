<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MiscellaneousTransaction extends Model
{
    protected $fillable = [
        'type',
        'date',
        'amount',
        'category',
        'description',
        'reference_number',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'float',
    ];

    // ✅ Categories constant – used for dropdowns and validation
    const CATEGORIES = [
        'Donation',
        'Refund',
        'Misc Sales',
        'Other',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}