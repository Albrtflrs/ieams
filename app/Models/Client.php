<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Client extends Model
{
    use SoftDeletes, HasFactory, LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}