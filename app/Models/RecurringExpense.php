<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecurringExpense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_id', 'category', 'subcategory', 'description', 'amount',
        'payment_method', 'next_due_date', 'billing_day', 'is_active', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'next_due_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function transactions()
    {
        return $this->hasMany(ExpenseTransaction::class);
    }
}
