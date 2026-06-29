<?php

namespace Database\Seeders;

use App\Models\ExpenseTransaction;
use Illuminate\Database\Seeder;

class ExpenseTransactionSeeder extends Seeder
{
    public function run(): void
    {
        // Do NOT truncate – keep existing records and add more
        ExpenseTransaction::factory(160)->create(); // adds 160 more (total 200)
    }
}