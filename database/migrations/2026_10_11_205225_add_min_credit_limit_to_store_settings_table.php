<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('store_settings', 'min_credit_limit')) {
                $table->decimal('min_credit_limit', 15, 2)
                    ->default(0)
                    ->after('max_credit_limit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            if (Schema::hasColumn('store_settings', 'min_credit_limit')) {
                $table->dropColumn('min_credit_limit');
            }
        });
    }
};
