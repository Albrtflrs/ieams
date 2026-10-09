<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Helper function to add created_by safely
        $addCreatedBy = function ($tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'created_by')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->foreignId('created_by')
                          ->nullable()
                          ->after('id') // Optional: Puts it near the top for readability
                          ->constrained('users')
                          ->nullOnDelete();
                });
            }
        };

        // Apply to all tables that exist
        $addCreatedBy('clients');
        $addCreatedBy('suppliers');
        $addCreatedBy('quotations');
        $addCreatedBy('retainers'); // This will now safely skip if the table doesn't exist
    }

    public function down()
    {
        // Helper function to drop created_by safely
        $dropCreatedBy = function ($tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'created_by')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    // Check if the foreign key constraint exists before trying to drop it
                    // Laravel's dropForeign works safely even if it doesn't exist, but we'll wrap it anyway.
                    $table->dropForeign(['created_by']);
                    $table->dropColumn('created_by');
                });
            }
        };

        $dropCreatedBy('clients');
        $dropCreatedBy('suppliers');
        $dropCreatedBy('quotations');
        $dropCreatedBy('retainers');
    }
};