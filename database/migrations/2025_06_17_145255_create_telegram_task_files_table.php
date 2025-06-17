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
        Schema::create('telegram_task_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('telegram_tasks')->onDelete('cascade');
            $table->string('file_id');
            $table->string('file_unique_id');
            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_task_files');
    }
};
