<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', fn(Blueprint $t) => $t->string('contact_number', 20)->nullable()->change());
        Schema::table('store_profiles', fn(Blueprint $t) => $t->string('contact_number', 20)->nullable()->change());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
