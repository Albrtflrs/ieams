<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // The 'role' column doesn't exist in the base users table,
        // so we ADD it instead of trying to MODIFY it.
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'admin', 'manager', 'staff', 'viewer'])
                  ->default('staff')
                  ->after('password'); // Places it right after the password field for readability
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};