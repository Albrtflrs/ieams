<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number')->unique()->index();
            
            // Client info
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('client_name')->nullable();
            $table->text('client_address')->nullable();
            
            // Dates
            $table->date('date_issued');
            $table->date('valid_until')->nullable();
            
            // Currency
            $table->string('currency', 10)->default('₱');
            
            // Financials
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            
            // Markup
            $table->decimal('markup_percentage', 8, 2)->default(0);
            $table->decimal('markup_amount', 15, 2)->default(0);
            
            // Status & conversion
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired', 'converted'])->default('draft');
            $table->foreignId('converted_to_income_id')->nullable()->constrained('income_transactions')->nullOnDelete();
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('date_issued');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};