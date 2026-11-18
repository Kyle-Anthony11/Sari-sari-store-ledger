<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_history', function (Blueprint $table) {
            if (!Schema::hasColumn('transaction_history', 'payment_method')) {
                $table->string('payment_method')->default('cash')->after('change');
            }

            if (!Schema::hasColumn('transaction_history', 'reference_number')) {
                $afterColumn = Schema::hasColumn('transaction_history', 'payment_method') ? 'payment_method' : 'change';
                $table->string('reference_number')->nullable()->after($afterColumn);
            }
        });
    }

    public function down(): void
    {
        Schema::table('transaction_history', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'reference_number']);
        });
    }
};
