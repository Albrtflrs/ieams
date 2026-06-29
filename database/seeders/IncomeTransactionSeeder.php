<?php

namespace Database\Seeders;

use App\Models\IncomeTransaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IncomeTransactionSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks to avoid constraint issues
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate the table
        IncomeTransaction::truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create 500 records (or whatever number you want)
        IncomeTransaction::factory(500)->create();
    }
}