<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TelegramTask extends Model
{
    protected $table = 'telegram_tasks';

    protected $fillable = [
        'telegram_user_id',
        'title',
        'description',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(TelegramUser::class, 'telegram_user_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(TelegramTaskFile::class, 'task_id');
    }
}
