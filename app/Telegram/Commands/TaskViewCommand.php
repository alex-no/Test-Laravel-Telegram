<?php
namespace App\Telegram\Commands;

use App\Models\TelegramUser;
use Telegram\Bot\Api;

class TaskViewCommand implements TelegramCommandHandler
{
    /**
     * Constructor for the TaskViewCommand.
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
            'chat_id' => $user->telegram_id,
            'text' => __('messages.welcome') . '.',
        ]);
    }
}

