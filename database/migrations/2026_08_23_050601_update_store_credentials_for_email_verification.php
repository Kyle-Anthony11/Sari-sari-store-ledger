<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('store_credentials', function (Blueprint $table) {
            $table->renameColumn('username', 'email');
            $table->timestamp('email_verified_at')->nullable()->after('pin');
            $table->string('verification_token', 64)->nullable()->after('email_verified_at');
        });
    }

    public function down()
    {
        Schema::table('store_credentials', function (Blueprint $table) {
            $table->renameColumn('email', 'username');
            $table->dropColumn(['email_verified_at', 'verification_token']);
        });
    }
};
