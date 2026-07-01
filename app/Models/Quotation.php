<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Quotation extends Model
{
    use SoftDeletes, LogsActivity;
    
    protected $fillable = [
        'quotation_number',
        'client_id',
        'client_name',
        'client_address',
        'date_issued',
        'valid_until',
        'currency',
        'subtotal',
        'discount',
        'tax',
        'total_amount',
        'markup_percentage',
        'markup_amount',
        'status',
        'converted_to_income_id',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'date_issued' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'float',
        'discount' => 'float',
        'tax' => 'float',
        'total_amount' => 'float',
        'markup_percentage' => 'float',
        'markup_amount' => 'float',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function convertedIncome(): BelongsTo
    {
        return $this->belongsTo(IncomeTransaction::class, 'converted_to_income_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}