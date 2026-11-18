<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_reset_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_credential_id')
                ->constrained('store_credentials')
                ->cascadeOnDelete();
            $table->string('otp'); // hashed
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['store_credential_id', 'used_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_otps');
    }
};