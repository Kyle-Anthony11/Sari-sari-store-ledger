<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Removes all records from the store setup tables.
     */
    public function up(): void
    {
        DB::transaction(function () {
            // Delete in correct order to respect foreign key constraints:
            // settings → credentials → profile
            DB::table('store_settings')->delete();
            DB::table('store_credentials')->delete();
            DB::table('store_profiles')->delete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @throws \Exception
     */
    public function down(): void
    {
        // Data cannot be restored automatically.
        throw new \Exception('This migration cannot be rolled back.');
    }
};
