<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'contact_person', 'phone', 'email', 'address'
    ];

    // Relationships
    public function incomeTransactions()
    {
        return $this->hasMany(IncomeTransaction::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function retainers()
    {
        return $this->hasMany(Retainer::class);
    }
}