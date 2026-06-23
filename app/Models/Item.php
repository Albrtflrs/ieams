<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    protected $fillable = [
        'name',
        'description',
        'default_cost_price',
        'default_markup_percentage',
        'default_selling_price',
        'category',
        'created_by',
    ];

    protected $casts = [
        'default_cost_price' => 'float',
        'default_markup_percentage' => 'float',
        'default_selling_price' => 'float',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}