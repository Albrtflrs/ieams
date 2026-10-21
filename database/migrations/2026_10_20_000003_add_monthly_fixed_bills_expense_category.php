<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $setting = DB::table('settings')->where('key', 'default_expense_categories')->first();

        if (!$setting) {
            return;
        }

        $categories = array_values(array_filter(array_map('trim', explode(',', (string) $setting->value))));
        if (!in_array('MONTHLY FIX BILLS', $categories, true)) {
            $categories[] = 'MONTHLY FIX BILLS';
        }

        DB::table('settings')
            ->where('key', 'default_expense_categories')
            ->update(['value' => implode(',', $categories)]);
    }

    public function down(): void
    {
        $setting = DB::table('settings')->where('key', 'default_expense_categories')->first();

        if (!$setting) {
            return;
        }

        $categories = array_values(array_filter(
            array_map('trim', explode(',', (string) $setting->value)),
            fn ($category) => $category !== 'MONTHLY FIX BILLS'
        ));

        DB::table('settings')
            ->where('key', 'default_expense_categories')
            ->update(['value' => implode(',', $categories)]);
    }
};
