<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('telegram_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('telegram_chat_id')->unique(); // ID группы из Telegram
            $table->string('title')->nullable();              // Название группы
            $table->string('type')->default('group');         // group / supergroup
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('telegram_groups');
    }
};
