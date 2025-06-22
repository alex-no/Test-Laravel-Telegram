<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * TelegramGroup Model
 * This model represents a Telegram group in the application.
 * It stores the group information such as chat ID, title, and type.
 * It also establishes a relationship with the TelegramUser model.
 */
class TelegramGroup extends Model
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $fillable = [
        'telegram_chat_id',
        'title',
        'type',
    ];

    /**
     * The attributes that should be cast to native types.
     * Get the Telegram users belonging to this group.
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(TelegramUser::class, 'telegram_group_user');
    }

    /**
     * Get the tasks associated with the Telegram group.
     * @return HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(TelegramTask::class);
    }

}
