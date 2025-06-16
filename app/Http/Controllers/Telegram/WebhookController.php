<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use Telegram\Bot\Api;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\TelegramUser;
// use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Supported languages for the bot.
     * @var array
    */
    const LANGUAGES = ['en', 'uk', 'ru'];

    /**
     * Handle incoming Telegram webhook updates.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request): Response
    {
        $telegram = app(Api::class);
        $update = $telegram->getWebhookUpdate();
        $message = $update->get('message');

        if (!$message) {
            return response()->noContent();
        }

        $telegramUser = TelegramUser::getUser($message);
        $text      = trim($message['text']);

        $lang = $this->setUserLanguage($telegramUser);
// Log::info("Telegram user {$telegramUser->telegram_id} set language to {$lang}");

        if ($text === '/start') {
            $telegram->sendMessage([
                'chat_id' => $telegramUser->telegram_id,
                'text' => __('messages.welcome') . ", {$telegramUser->first_name}! 👋\n" . __('messages.successful_command') . '.',
            ]);
        } elseif ($text === '/help') {
            $telegram->sendMessage([
                'chat_id' => $telegramUser->telegram_id,
                'text' => __('messages.commands') . ":\n" .
                    "/start — " . __('messages.register') . "\n" .
                    "/help — " . __('messages.help'),            ]);
        }

        return response()->noContent();
    }

    /**
     * Set the current language for the Telegram user and return language code.
     *
     * @param TelegramUser $user
     * @return string
     */
    private function setUserLanguage(TelegramUser $user): string
    {
        $lang = substr((string)$user->language_code, 0, 2);
        if (!in_array($lang, self::LANGUAGES)) {
            $lang = self::LANGUAGES[0]; // fallback to default language
        }

        app()->setLocale($lang);
        return $lang;
    }
}
