<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('income_transactions', function (Blueprint $table) {
            $table->string('agency_department')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('income_transactions')->whereNull('agency_department')->update(['agency_department' => '']);

        Schema::table('income_transactions', function (Blueprint $table) {
            $table->string('agency_department')->nullable(false)->change();
        });
    }
};
