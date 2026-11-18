<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Get actual foreign key names for transactions_id and payments_id
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME, COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'transaction_history'
            AND REFERENCED_TABLE_NAME IS NOT NULL
            AND COLUMN_NAME IN ('transactions_id', 'payments_id')
        ");

        // Drop existing foreign keys
        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE transaction_history DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        // Re-add with ON DELETE SET NULL
        Schema::table('transaction_history', function ($table) {
            $table->foreign('transactions_id')
                ->references('id')
                ->on('transactions')
                ->onDelete('set null');

            $table->foreign('payments_id')
                ->references('id')
                ->on('payments')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        // Drop the new foreign keys
        Schema::table('transaction_history', function ($table) {
            $table->dropForeign(['transactions_id']);
            $table->dropForeign(['payments_id']);
        });

        // Restore original foreign keys (without ON DELETE SET NULL)
        // Note: This assumes the original constraints existed. If they didn't, you may skip this.
        Schema::table('transaction_history', function ($table) {
            $table->foreign('transactions_id')->references('id')->on('transactions');
            $table->foreign('payments_id')->references('id')->on('payments');
        });
    }
};
