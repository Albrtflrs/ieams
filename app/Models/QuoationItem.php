<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id',
        'description',
        'quantity',
        'unit_price',
        'selling_price',
        'discount',
        'total',
        'category',
        'markup_percentage',
        'markup_amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'float',
        'selling_price' => 'float',
        'discount' => 'float',
        'total' => 'float',
        'markup_percentage' => 'float',
        'markup_amount' => 'float',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }
}