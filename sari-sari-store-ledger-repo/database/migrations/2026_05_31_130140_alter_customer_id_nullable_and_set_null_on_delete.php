<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Make customer_id nullable and set foreign key to ON DELETE SET NULL
        Schema::table('transactions', function (Blueprint $table) {
            // Drop existing foreign key if any (name may differ – check your schema)
            $table->dropForeign(['customer_id']);
            $table->foreignId('customer_id')->nullable()->change();
            $table->foreign('customer_id')
                ->references('id')->on('customers')
                ->onDelete('set null');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->foreignId('customer_id')->nullable()->change();
            $table->foreign('customer_id')
                ->references('id')->on('customers')
                ->onDelete('set null');
        });

        Schema::table('transaction_history', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->foreignId('customer_id')->nullable()->change();
            $table->foreign('customer_id')
                ->references('id')->on('customers')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        // Revert to NOT NULL (optional)
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->foreignId('customer_id')->nullable(false)->change();
            $table->foreign('customer_id')->references('id')->on('customers');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->foreignId('customer_id')->nullable(false)->change();
            $table->foreign('customer_id')->references('id')->on('customers');
        });


        Schema::table('transaction_history', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->foreignId('customer_id')->nullable(false)->change();
            $table->foreign('customer_id')->references('id')->on('customers');
        });
    }
};
