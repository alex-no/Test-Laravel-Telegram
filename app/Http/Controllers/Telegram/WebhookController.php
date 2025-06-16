<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use Telegram\Bot\Api;
use Illuminate\Http\Request;
use App\Models\TelegramUser;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
Log::info('raw request body: ' . $request->getContent());
        $telegram = app(Api::class);
        $update = $telegram->getWebhookUpdate();
Log::info('class of $update: ' . get_class($update));
Log::info('raw $update: ' . print_r($update, true));
        $message = $update->get('message');
Log::info('ok-4');

        if (!$message) {
            return response()->noContent();
        }

        $chatId    = $message['chat']['id'] ?? null;
        $firstName = $message['from']['first_name'] ?? '';
        $text      = trim($message['text']);

Log::info('ok-5 [' . $text . ']');
        if ($text === '/start') {
            TelegramUser::updateOrCreate(
                ['telegram_id' => $chatId],
                [
                    'first_name'     => $message['from']['first_name'] ?? null,
                    'last_name'      => $message['from']['last_name'] ?? null,
                    'username'       => $message['from']['username'] ?? null,
                    'language_code'  => $message['from']['language_code'] ?? null,
                    'is_bot'         => $message['from']['is_bot'] ?? false,
                    'is_premium'     => $message['from']['is_premium'] ?? false,
                    'extra'          => [
                        'supports_inline_queries'  => $message['from']['supports_inline_queries'] ?? null,
                        'added_to_attachment_menu' => $message['from']['added_to_attachment_menu'] ?? null,
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
