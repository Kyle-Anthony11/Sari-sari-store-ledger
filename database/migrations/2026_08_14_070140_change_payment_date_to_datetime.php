<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // For payments table
        Schema::table('payments', function (Blueprint $table) {
            $table->datetime('payment_date')->change();
        });

        // For transaction_history table
        Schema::table('transaction_history', function (Blueprint $table) {
            $table->datetime('payment_date')->change();
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->date('payment_date')->change();
        });

        Schema::table('transaction_history', function (Blueprint $table) {
            $table->date('payment_date')->change();
        });
    }
};
