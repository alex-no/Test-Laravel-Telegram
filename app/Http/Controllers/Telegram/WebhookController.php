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
    public function __invoke(Request $request): Response
    {
        $telegram = app(Api::class);
        $update = $telegram->getWebhookUpdate();
        $message = $update->get('message');

        if (!$message) {
            return response()->noContent();
        }

        $telegramUser = TelegramUser::getUser($message);
        $this->setUserLanguage($telegramUser);

        app(\App\Telegram\CommandRouter::class)->handle($message, $telegramUser);

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
