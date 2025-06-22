<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * TelegramTask Model
 * This model represents a task associated with a Telegram user.
 * It stores task details such as title, description, status, and the user it belongs to.
 * It also establishes relationships with the TelegramUser and TelegramTaskFile models.
 */
class TelegramTask extends Model
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'telegram_tasks';

    /**
     * The attributes that should be cast to native types.
     * @var array<string, string>
     */
    protected $fillable = [
        'telegram_user_id',
        'telegram_group_id',
        'title',
        'description',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     * @var array<string, string>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(TelegramUser::class, 'telegram_user_id');
    }

    /**
     * Get the files associated with the Telegram task.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files(): HasMany
    {
        return $this->hasMany(TelegramTaskFile::class, 'task_id');
    }

    /**
     * Get the Telegram group associated with the task.
     * @return BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(TelegramGroup::class, 'telegram_group_id');
    }
}
