<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transaction_number_sequences', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->integer('last_number')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transaction_number_sequences');
    }
};
