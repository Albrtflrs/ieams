<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            
            // Item details
            $table->string('description');
            $table->integer('quantity')->default(1);
            
            // Pricing
            $table->decimal('unit_price', 15, 2)->default(0); // Your cost
            $table->decimal('selling_price', 15, 2)->default(0); // Cost + markup
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            
            // Markup per item
            $table->decimal('markup_percentage', 8, 2)->default(0);
            $table->decimal('markup_amount', 15, 2)->default(0);
            
            // Optional
            $table->string('category')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('quotation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};