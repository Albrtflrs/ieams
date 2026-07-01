<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('retainers', function (Blueprint $table) {
            // Billing & payments
            $table->enum('billing_frequency', ['monthly', 'quarterly', 'annually'])->nullable();
            $table->enum('payment_terms', ['due_on_receipt', 'net_10', 'net_30'])->nullable();
            $table->boolean('auto_renew')->default(false);

            // Scope & tracking
            $table->unsignedInteger('allocated_hours')->nullable();
            $table->decimal('overage_hourly_rate', 15, 2)->nullable();
            $table->boolean('rollover_allowed')->default(false);

            // Service level
            $table->enum('sla_tier', ['bronze', 'silver', 'gold'])->nullable();
            $table->string('contract_path')->nullable();

            // Line items (JSON array: [{name, description, amount?}])
            $table->json('services')->nullable();
        });
    }

    public function down()
    {
        Schema::table('retainers', function (Blueprint $table) {
            $table->dropColumn([
                'billing_frequency', 'payment_terms', 'auto_renew',
                'allocated_hours', 'overage_hourly_rate', 'rollover_allowed',
                'sla_tier', 'contract_path', 'services'
            ]);
        });
    }
};