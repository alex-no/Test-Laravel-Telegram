<?php

namespace App\Telegram\Middleware;

use App\Models\TelegramGroup;
use App\Models\TelegramUser;

class AutoGroupRegistrar
{
    public static function handle(array $message): void
    {
        if (!isset($message['chat']) || !in_array($message['chat']['type'], ['group', 'supergroup'])) {
            return;
        }

        $chat = $message['chat'];

        $group = TelegramGroup::firstOrCreate(
            ['telegram_chat_id' => $chat['id']],
            [
                'title' => $chat['title'] ?? null,
                'type' => $chat['type'],
            ]
        );

        $user = TelegramUser::getUser($message);
        $group->users()->syncWithoutDetaching([$user->id]);
    }
}
