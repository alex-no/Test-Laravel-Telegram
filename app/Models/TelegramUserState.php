<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TelegramUserState Model
 * This model represents the state of a Telegram user in the application.
 * It stores the current step and any associated data for the user.
 */
class TelegramUserState extends Model
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'telegram_user_states';

    /**
     * The attributes that should be cast to native types.
     * @var array<string, string>
     */
    protected $fillable = [
        'telegram_user_id',
        'step',
        'data',
    ];

    /**
     * The attributes that should be cast to native types.
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get the user associated with the Telegram user state.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(TelegramUser::class, 'telegram_user_id');
    }
}
