<?php

namespace Database\Seeders;

use App\Models\IncomeTransaction;
use Illuminate\Database\Seeder;

class IncomeTransactionSeeder extends Seeder
{
    public function run()
    {
        IncomeTransaction::factory(50)->create();
    }
}