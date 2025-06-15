<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Api;

class TelegramController extends Controller
{
    public function webhook(Request $request)
    {
        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

        $update = $telegram->getWebhookUpdate();

        $message = $update->getMessage();
        $chatId = $message->getChat()->getId();
        $text = $message->getText();

        $telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "You wrote: $text",
        ]);

        return 'ok';
    }
}

