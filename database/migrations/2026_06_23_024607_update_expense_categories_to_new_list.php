<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Old → New category mapping
    private const CATEGORY_MAP = [
        'Office Supplies' => 'DAILY EXPENSES',
        'Utilities' => 'MONTHLY FIX BILLS',
        'Rent' => 'MONTHLY FIX BILLS',
        'Transportation' => 'GAS/MAINTENANCE',
        'Maintenance' => 'GAS/MAINTENANCE',
        'Others' => 'DAILY EXPENSES',
        'Salaries' => 'SALARY',
        'Salary' => 'SALARY',
        'Gas' => 'GAS/MAINTENANCE',
        'Fuel' => 'GAS/MAINTENANCE',
        'Rental' => 'MONTHLY FIX BILLS',
        'Insurance' => 'MONTHLY FIX BILLS',
        'Communication' => 'MONTHLY FIX BILLS',
        'Internet' => 'MONTHLY FIX BILLS',
        'Electricity' => 'MONTHLY FIX BILLS',
        'Water' => 'MONTHLY FIX BILLS',
        'Loan' => 'LOAN PAYMENT',
        'Loan Payment' => 'LOAN PAYMENT',
        'Delivery' => 'DELIVERY (parcel receiving)',
        'Cash Received' => 'CASH RECEIVED',
    ];

    public function up(): void
    {
        $oldCategories = array_keys(self::CATEGORY_MAP);
        $newCategories = array_values(self::CATEGORY_MAP);

        // Update each old category to its new mapping
        foreach (self::CATEGORY_MAP as $old => $new) {
            DB::table('expense_transactions')
                ->where('category', $old)
                ->update(['category' => $new]);
        }

        // Any remaining categories that aren't in the map → set to 'DAILY EXPENSES'
        $validNewCategories = [
            'DELIVERY (parcel receiving)',
            'DAILY EXPENSES',
            'GAS/MAINTENANCE',
            'SALARY',
            'CASH RECEIVED',
            'LOAN PAYMENT',
            'MONTHLY FIX BILLS'
        ];

        DB::table('expense_transactions')
            ->whereNotIn('category', $validNewCategories)
            ->update(['category' => 'DAILY EXPENSES']);
    }

    public function down(): void
    {
        // Cannot reliably revert category changes, so we leave it empty
    }
};