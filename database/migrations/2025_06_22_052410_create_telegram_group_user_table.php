<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('telegram_group_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('telegram_group_id');
            $table->unsignedBigInteger('telegram_user_id');
            $table->timestamps();

            $table->unique(['telegram_group_id', 'telegram_user_id']);

            $table->foreign('telegram_group_id')
                ->references('id')->on('telegram_groups')
                ->onDelete('cascade');

            $table->foreign('telegram_user_id')
                ->references('id')->on('telegram_users')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('telegram_group_user');
    }
};
