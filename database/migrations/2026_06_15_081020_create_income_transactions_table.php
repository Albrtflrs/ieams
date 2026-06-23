<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('income_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('item_no')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('agency_department');
            $table->string('municipal_barangay')->nullable();
            $table->text('particulars');
            $table->date('date_delivered')->nullable();
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->date('date_paid')->nullable();
            $table->string('receipt_number')->nullable();
            $table->decimal('gross_price', 12, 2);
            $table->decimal('royalty_percent', 5, 2)->default(0);
            $table->decimal('deductions', 12, 2)->default(0);
            $table->string('category');
            $table->boolean('withdrawn')->default(false);
            $table->string('status')->default('Unpaid');
            $table->text('remarks')->nullable();
            $table->boolean('is_miscellaneous')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('income_transactions');
    }
};