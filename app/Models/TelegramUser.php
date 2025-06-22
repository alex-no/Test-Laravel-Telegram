<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
// use Illuminate\Support\Facades\Log;

class TelegramUser extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $fillable = [
        'telegram_id',
        'first_name',
        'last_name',
        'username',
        'language_code',
        'is_bot',
        'is_premium',
        'extra',
    ];

    /**
     * The attributes that should be cast to native types.
     * @var array<string, string>
     */
    protected $casts = [
        'telegram_id' => 'integer',
        'is_bot' => 'boolean',
        'is_premium' => 'boolean',
        'birthday' => 'date',
        'extra' => 'array', // Store arbitrary data as JSON
    ];

    /**
     * Create or update a Telegram user based on the message payload.
     */
    public static function getUser(array $message): self
    {
        // Validate that the message contains the necessary chat information
        if (!isset($message['chat']['id'])) {
            throw new \InvalidArgumentException('Missing chat.id in message payload');
        }

        // Check for chat.id presence
        $telegramId = $message['chat']['id'] ?? null;
        if (!$telegramId) {
            throw new InvalidArgumentException('Empty chat.id in message payload');
        }

        // Try to find an existing user
        $user = self::firstOrNew(['telegram_id' => $telegramId]);

        // Data source
        $from = $message['from'] ?? [];

        // Update only if not empty
        $user->first_name = $from['first_name'] ?? $user->first_name;
        $user->last_name = $from['last_name'] ?? $user->last_name;

        if (!empty($from['username'])) {
            $user->username = $from['username'];
        }

        if (!empty($from['language_code'])) {
            $user->language_code = $from['language_code'];
        }

        $user->is_bot = $from['is_bot'] ?? $user->is_bot ?? false;
        $user->is_premium = $from['is_premium'] ?? $user->is_premium ?? false;

        // Merge extra
        $user->extra = array_merge($user->extra ?? [], $from);

        // Save and return
        $user->save();

        return $user;
    }

    /**
     * Get the Telegram user's tasks.
     *
     * @return HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(TelegramTask::class, 'telegram_user_id');
    }

    /**
     * Get the Telegram user's state.
     *
     * @return HasOne
     */
    public function state(): HasOne
    {
        return $this->hasOne(TelegramUserState::class, 'telegram_user_id');
    }

    /**
     * Get the Telegram groups that the user belongs to.
     *
     * @return BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany(TelegramGroup::class, 'telegram_group_user');
    }
}
