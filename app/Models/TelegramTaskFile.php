<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramTaskFile extends Model
{
    protected $table = 'telegram_task_files';

    protected $fillable = [
        'task_id',
        'file_id',
        'file_unique_id',
        'file_name',
        'mime_type',
        'file_size',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(TelegramTask::class, 'task_id');
    }
}
