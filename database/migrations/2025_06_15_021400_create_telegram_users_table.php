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
        Schema::create('telegram_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('telegram_id')->unique();
            $table->string('username', 64)->nullable();
            $table->string('password', 128)->nullable();
            $table->string('first_name', 128)->nullable();
            $table->string('last_name', 128)->nullable();
            $table->string('phone', 64)->nullable();
            $table->date('birthday')->nullable();
            $table->string('language_code', 8)->nullable();
            $table->boolean('is_bot')->default(false);
            $table->boolean('is_premium')->default(false);
            $table->json('extra')->nullable(); // for arbitrary data
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_users');
    }
};
