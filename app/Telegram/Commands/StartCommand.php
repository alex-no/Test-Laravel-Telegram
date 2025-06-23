<?php
namespace App\Telegram\Commands;

use App\Models\TelegramUser;
use Telegram\Bot\Api;

class StartCommand implements TelegramCommandHandler
{
    /**
     * Constructor for the StartCommand.
     * @param Api $telegram The Telegram API instance.
     */
    public function __construct(
        protected Api $telegram
    ) {}

    /**
     * Handle the /start command.
     * @param array $message The message data.
     * @param string $dataText The text data.
     * @param TelegramUser $user The user object.
     * @return void
     */
    public function handle(array $message, string $dataText, TelegramUser $user): void
    {
        $this->telegram->sendMessage([
            'chat_id' => $message['chat']['id'],
            'text' => __('messages.welcome') . ", {$user->first_name}! 👋\n" . __('messages.successful_command') . '.',
        ]);
    }
}

