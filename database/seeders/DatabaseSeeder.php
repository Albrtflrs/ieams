<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Activitylog\Facades\Activity;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Disable activity logging to avoid missing activity_log table
        Activity::disableLogging();

        $this->call([
            UserRoleSeeder::class,
            ClientSeeder::class,
            SupplierSeeder::class,
            IncomeTransactionSeeder::class,
            ExpenseTransactionSeeder::class,
            MiscellaneousTransactionSeeder::class,
            SettingsSeeder::class,
        ]);

        Activity::enableLogging();
    }
}