<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Retainer extends Model
{
    use HasFactory;

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
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_amount' => 'decimal:2',
        'used_amount' => 'decimal:2',
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
}