<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            // Your existing seeders (e.g., UserRoleSeeder, AklanLocationSeeder)
            ClientSeeder::class,
            SupplierSeeder::class,
            IncomeTransactionSeeder::class,
            ExpenseTransactionSeeder::class,
            MiscellaneousTransactionSeeder::class,
        ]);
    }
}