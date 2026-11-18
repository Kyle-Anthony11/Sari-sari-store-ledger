<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transaction_reminders', function (Blueprint $table) {
            $table->id();
            $table->string('group_id'); // references transactions.group_id
            $table->string('type'); // 'pre_due' or 'overdue_week'
            $table->integer('week_number')->default(0); // 0 = pre‑due, 1,2,... for overdue weeks
            $table->timestamp('sent_at')->useCurrent();
            $table->unique(['group_id', 'type', 'week_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('transaction_reminders');
    }
};
