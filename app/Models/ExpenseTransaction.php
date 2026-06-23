<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'date',
        'amount',
        'category',
        'description',
        'receipt_number',
        'payment_method',
        'is_miscellaneous',
        'created_by',
        'status', // <-- added
    ];

    protected $casts = [
        'date' => 'date',
        'is_miscellaneous' => 'boolean',
        'status' => 'string',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}