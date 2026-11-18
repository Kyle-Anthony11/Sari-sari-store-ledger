<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('surcharge_at_creation', 15, 2)->default(0)->after('surcharge_total');
            $table->decimal('interest_rate_at_creation', 5, 2)->default(0)->after('interest_rate');
            $table->integer('duration_days_at_creation')->default(0)->after('due_date');
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['surcharge_at_creation', 'interest_rate_at_creation', 'duration_days_at_creation']);
        });
    }
};
