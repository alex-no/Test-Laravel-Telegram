<?php

namespace App\Telegram\Listeners;

use Telegram\Bot\Objects\Update;

class HandleIncomingMessage
{
    public function __invoke()
    // public function __invoke(Update $update)
    {
        // $message = $update->getMessage();
        $message = null;

        if ($message) {
            $chatId = $message->getChat()->getId();
            $text = $message->getText();

            // Здесь можешь отправить сообщение или логировать
            // \Telegram::sendMessage([
            //     'chat_id' => $chatId,
            //     'text' => "Вы написали: {$text}",
            // ]);
        }
    }
}
