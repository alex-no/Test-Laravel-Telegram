<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use Telegram\Bot\Api;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\TelegramUser;
use App\Telegram\CommandRouter;
use App\Telegram\Middleware\AutoGroupRegistrar;
use App\Telegram\TelegramMessageWrapper;
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
        $wrapper = new TelegramMessageWrapper($update);

        $text = $wrapper->getText();
        $message = $wrapper->getMessage();

        try {
            if (!$message) {
                throw new \Exception('Empty Telegram message');
            }

            // Automatically register group if applicable
            AutoGroupRegistrar::handle($message);

            if (empty($text) && !$wrapper->hasMedia()) {
                if (!empty($message['group_chat_created']) || !empty($message['new_chat_members'])) {
                    return response()->noContent();
                }
                throw new \InvalidArgumentException('Message ignored: no text or media');
            }

            $telegramUser = TelegramUser::getUser($message);
            $this->setUserLanguage($telegramUser);

            $message['text'] = $text;

            app(CommandRouter::class)->handle($message, $telegramUser);
        } catch (\InvalidArgumentException $e) {
            return $this->handleError($e->getMessage(), $message);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage(), $message ?? []);
        }

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

    /**
     * Handle errors during message processing.
     * @param string $errorMessage error message
     * @param array $message Bot message data array
     * @return Response
     * @throws \Exception
     */
    private function handleError(string $errorMessage, array $message): Response
    {
            Log::error('Error processing Telegram message', [
                'error'   => $errorMessage,
                'message' => $message,
            ]);
            return response()->noContent();
    }
}
