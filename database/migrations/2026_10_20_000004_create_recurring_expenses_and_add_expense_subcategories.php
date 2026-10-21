<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category')->default('MONTHLY FIX BILLS');
            $table->string('subcategory')->nullable();
            $table->text('description');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->nullable();
            $table->date('next_due_date');
            $table->unsignedTinyInteger('billing_day')->default(1);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('expense_transactions', function (Blueprint $table) {
            $table->string('subcategory')->nullable()->after('category');
            $table->foreignId('recurring_expense_id')->nullable()->after('supplier_id')->constrained()->nullOnDelete();
            $table->date('recurring_period')->nullable()->after('date');
            $table->unique(['recurring_expense_id', 'recurring_period'], 'expense_recurring_period_unique');
        });
    }

    public function down(): void
    {
        Schema::table('expense_transactions', function (Blueprint $table) {
            $table->dropUnique('expense_recurring_period_unique');
            $table->dropConstrainedForeignId('recurring_expense_id');
            $table->dropColumn(['subcategory', 'recurring_period']);
        });

        Schema::dropIfExists('recurring_expenses');
    }
};
