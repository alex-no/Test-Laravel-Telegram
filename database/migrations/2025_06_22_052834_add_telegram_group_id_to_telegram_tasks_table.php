<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('telegram_tasks', function (Blueprint $table) {
            $table->foreignId('telegram_group_id')
                  ->nullable()
                  ->after('telegram_user_id')
                  ->constrained('telegram_groups')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('telegram_tasks', function (Blueprint $table) {
            $table->dropForeign(['telegram_group_id']);
            $table->dropColumn('telegram_group_id');
        });
    }
};
