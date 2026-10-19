<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique('transactions_transaction_number_unique');
            $table->index('transaction_number', 'transactions_transaction_number_index');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_transaction_number_index');
            $table->unique('transaction_number', 'transactions_transaction_number_unique');
        });
    }
};
