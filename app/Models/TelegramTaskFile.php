<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TelegramTaskFile Model
 * This model represents a file associated with a Telegram task.
 * It stores file details such as file ID, unique ID, name, MIME type, and size.
 * It also establishes a relationship with the TelegramTask model.
 */
class TelegramTaskFile extends Model
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'telegram_task_files';

    /**
     * The attributes that should be cast to native types.
     * @var array<string, string>
     */
    protected $fillable = [
        'task_id',
        'file_id',
        'file_unique_id',
        'file_name',
        'mime_type',
        'file_size',
    ];

    /**
     * The attributes that should be cast to native types.
     * @return BelongsTo
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(TelegramTask::class, 'task_id');
    }
}
