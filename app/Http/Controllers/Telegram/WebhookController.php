<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use Telegram\Bot\Api;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\TelegramUser;
use App\Telegram\CommandRouter;
use Illuminate\Support\Facades\Log;

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
        $update = $request->all();
        $wrapper = new \App\Telegram\TelegramMessageWrapper($update);

        $text = $wrapper->getText();
        $message = $wrapper->getMessage();

        if (!$text || !$message) {
            Log::warning('No message text found');
            return response()->noContent();
        }

        $message['text'] = $text;

        $telegramUser = TelegramUser::getUser($wrapper->getMessage());
        $this->setUserLanguage($telegramUser);

        app(CommandRouter::class)->handle($message, $telegramUser);

        // Respond to callback if needed
        if ($wrapper->isCallback()) {
            app(Api::class)->answerCallbackQuery([
                'callback_query_id' => $wrapper->getCallbackQueryId(),
                'text' => '⏱️ ' . __('messages.performed') . '...',
                'show_alert' => false,
            ]);
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
