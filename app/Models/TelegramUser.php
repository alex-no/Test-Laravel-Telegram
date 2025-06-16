<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class TelegramUser extends Model
{
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

    protected $casts = [
        'telegram_id' => 'integer',
        'is_bot' => 'boolean',
        'is_premium' => 'boolean',
        'birthday' => 'date',
        'extra' => 'array', // Store arbitrary data as JSON
    ];

    public static function getUser(array $message): self
    {
        // Check for chat.id presence
        $telegramId = $message['chat']['id'] ?? null;
        if (!$telegramId) {
            throw new InvalidArgumentException('Missing chat.id in message payload');
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
}
