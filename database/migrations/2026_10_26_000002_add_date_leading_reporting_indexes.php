<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('income_transactions', function (Blueprint $table) {
            $table->index(['date_delivered', 'is_miscellaneous'], 'income_delivered_misc_idx');
        });

        Schema::table('expense_transactions', function (Blueprint $table) {
            $table->index(['date', 'is_miscellaneous'], 'expense_date_misc_idx');
        });
    }

    public function down(): void
    {
        Schema::table('income_transactions', function (Blueprint $table) {
            $table->dropIndex('income_delivered_misc_idx');
        });

        Schema::table('expense_transactions', function (Blueprint $table) {
            $table->dropIndex('expense_date_misc_idx');
        });
    }
};
