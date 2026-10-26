<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('income_transactions', function (Blueprint $table) {
            $table->index(['is_miscellaneous', 'created_at'], 'income_misc_created_idx');
            $table->index(['is_miscellaneous', 'date_delivered'], 'income_misc_delivered_idx');
            $table->index(['client_id', 'date_delivered'], 'income_client_delivered_idx');
            $table->index(['status', 'created_at'], 'income_status_created_idx');
        });

        Schema::table('expense_transactions', function (Blueprint $table) {
            $table->index(['is_miscellaneous', 'created_at'], 'expense_misc_created_idx');
            $table->index(['is_miscellaneous', 'date'], 'expense_misc_date_idx');
            $table->index(['supplier_id', 'date'], 'expense_supplier_date_idx');
        });

        Schema::table('miscellaneous_transactions', function (Blueprint $table) {
            $table->index(['type', 'created_at'], 'misc_type_created_idx');
            $table->index(['type', 'date'], 'misc_type_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('income_transactions', function (Blueprint $table) {
            $table->dropIndex('income_misc_created_idx');
            $table->dropIndex('income_misc_delivered_idx');
            $table->dropIndex('income_client_delivered_idx');
            $table->dropIndex('income_status_created_idx');
        });

        Schema::table('expense_transactions', function (Blueprint $table) {
            $table->dropIndex('expense_misc_created_idx');
            $table->dropIndex('expense_misc_date_idx');
            $table->dropIndex('expense_supplier_date_idx');
        });

        Schema::table('miscellaneous_transactions', function (Blueprint $table) {
            $table->dropIndex('misc_type_created_idx');
            $table->dropIndex('misc_type_date_idx');
        });
    }
};
