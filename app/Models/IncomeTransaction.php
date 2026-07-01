<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class IncomeTransaction extends Model
{
    use SoftDeletes;
    use HasFactory;
    use LogsActivity; // 👈 added for audit trail

    const CATEGORIES = [
        'CCTV AND SUPPLIES',
        'OFFICE SUPPLIES',
        'IT EQUIPMENT',
        'SOFTWARE',
        'ELECTRONICS/AIRCON',
        'FURNITURE',
        'KITCHENWARE',
        'SOLAR',
        'OTHERS'
    ];

    protected $fillable = [
        'item_no', 'client_id', 'agency_department', 'municipal_barangay',
        'particulars', 'date_delivered', 'amount_paid', 'date_paid',
        'receipt_number', 'gross_price', 'royalty_percent', 'deductions',
        'category', 'withdrawn', 'status', 'remarks', 'is_miscellaneous',
        'created_by'
    ];

    protected $casts = [
        'date_delivered' => 'date',
        'date_paid' => 'date',
        'withdrawn' => 'boolean',
        'is_miscellaneous' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getReceivablesAttribute()
    {
        return max(0, $this->gross_price - $this->amount_paid);
    }

    public function getRoyaltyGrossAttribute()
    {
        return $this->gross_price * ($this->royalty_percent / 100);
    }

    public function getNetSalesAttribute()
    {
        return $this->gross_price - $this->deductions;
    }

    public function getNetCashConversionAttribute()
    {
        if ($this->amount_paid >= $this->gross_price) {
            return $this->amount_paid - $this->royalty_gross - $this->deductions;
        }
        return null;
    }

    // ─── Audit trail configuration ──────────────────────
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}