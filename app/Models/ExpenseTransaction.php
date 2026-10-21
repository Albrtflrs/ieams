<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ExpenseTransaction extends Model
{
    use SoftDeletes, HasFactory, LogsActivity;

    protected $fillable = [
        'supplier_id',
        'date',
        'amount',
        'category',
        'subcategory',
        'description',
        'receipt_number',
        'payment_method',
        'is_miscellaneous',
        'created_by',
        'status',
        'recurring_expense_id',
        'recurring_period',
    ];

    protected $casts = [
        'date' => 'date',
        'recurring_period' => 'date',
        'is_miscellaneous' => 'boolean',
        'status' => 'string',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function recurringExpense()
    {
        return $this->belongsTo(RecurringExpense::class);
    }

    public function creator()
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
