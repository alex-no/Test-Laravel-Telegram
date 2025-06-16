<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use Telegram\Bot\Api;
use Illuminate\Http\Request;
use App\Models\TelegramUser;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $telegram = app(Api::class);
        $update = $telegram->getWebhookUpdate();
        $message = $update->getMessage();

        if (!$message) {
            return response()->noContent();
        }

        $chatId = $message->getChat()->getId();
        $firstName = $message->getFrom()->getFirstName();
        $userData = $message->getFrom();
        $text = trim($message->getText());

        if ($text === '/start') {
            TelegramUser::updateOrCreate(
                ['telegram_id' => $chatId],
                [
                    'first_name'     => $userData->getFirstName(),
                    'last_name'      => $userData->getLastName(),
                    'username'       => $userData->getUsername(),
                    'language_code'  => $userData->getLanguageCode(),
                    'is_bot'         => $userData->getIsBot(),
                    'is_premium'     => $userData->get('is_premium') ?? false,
                    'extra'          => [
                        'supports_inline_queries'  => $userData->get('supports_inline_queries'),
                        'added_to_attachment_menu' => $userData->get('added_to_attachment_menu'),
                    ],
                ]
            );

            $telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "Привіт, {$firstName}! 👋\nТи успішно зареєстрований.",
            ]);
        } elseif ($text === '/help') {
            $telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "Доступні команди:\n/start — зареєструватися\n/help — допомога",
            ]);
        }

        return response()->noContent();
    }
}
