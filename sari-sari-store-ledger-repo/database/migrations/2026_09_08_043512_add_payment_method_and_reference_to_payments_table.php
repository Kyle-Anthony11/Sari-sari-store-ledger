<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Add payment_method if missing
            if (!Schema::hasColumn('payments', 'payment_method')) {
                $table->string('payment_method')->default('cash')->after('change');
            }

            // Add reference_number if missing
            if (!Schema::hasColumn('payments', 'reference_number')) {
                // Place after payment_method if that exists, else after change
                $afterColumn = Schema::hasColumn('payments', 'payment_method') ? 'payment_method' : 'change';
                $table->string('reference_number')->nullable()->after($afterColumn);
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'reference_number']);
        });
    }
};
