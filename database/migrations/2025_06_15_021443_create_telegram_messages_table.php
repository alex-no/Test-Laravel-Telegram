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
        Schema::create('telegram_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('telegram_user_id')->index();
            $table->unsignedBigInteger('message_id')->nullable(); // Telegram message_id
            $table->string('message', 2048)->nullable();
            $table->dateTime('message_date')->nullable();
            $table->string('message_type', 32)->nullable(); // text, photo, sticker, etc.
            $table->boolean('is_bot_command')->default(false);
            $table->string('via_bot')->nullable();
            $table->json('raw')->nullable(); // Full JSON of the message object
            $table->timestamps();

            $table->foreign('telegram_user_id')
                  ->references('id')->on('telegram_users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_messages');
    }
};
