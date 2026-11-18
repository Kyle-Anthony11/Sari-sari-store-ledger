<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->change();
        });

        // Payments table (if it has customer_id)
        if (Schema::hasColumn('payments', 'customer_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->unsignedBigInteger('customer_id')->nullable()->change();
            });
        }
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
        });

        if (Schema::hasColumn('payments', 'customer_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->unsignedBigInteger('customer_id')->nullable(false)->change();
            });
        }
    }
};
