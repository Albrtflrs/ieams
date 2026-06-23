<?php

namespace Database\Seeders;

use App\Models\ExpenseTransaction;
use Illuminate\Database\Seeder;

class ExpenseTransactionSeeder extends Seeder
{
    public function run()
    {
        ExpenseTransaction::factory(40)->create();
    }
}