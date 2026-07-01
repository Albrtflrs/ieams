<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Retainer extends Model
{
    use SoftDeletes, HasFactory, LogsActivity;

    protected $fillable = [
        'client_id',
        'reference_number',
        'total_amount',
        'used_amount',
        'start_date',
        'end_date',
        'status',
        'description',
        'created_by',
        'billing_frequency',
        'payment_terms',
        'auto_renew',
        'allocated_hours',
        'overage_hourly_rate',
        'rollover_allowed',
        'sla_tier',
        'contract_path',
        'services',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'auto_renew' => 'boolean',
        'rollover_allowed' => 'boolean',
        'services' => 'array',
        'total_amount' => 'decimal:2',
        'used_amount' => 'decimal:2',
        'overage_hourly_rate' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessor for remaining balance
    public function getRemainingBalanceAttribute()
    {
        return $this->total_amount - $this->used_amount;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}