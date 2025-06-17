<?php
namespace App\Telegram\Commands;

use App\Models\TelegramTask;
use App\Models\TelegramUser;
use App\Models\TelegramUserState;
use Telegram\Bot\Api;
use App\Telegram\Commands\TelegramCommandHandler;
use Illuminate\Support\Facades\Log;

class TaskCreateCommand implements TelegramCommandHandler
{
    /**
     * Constructor for the TaskCreateCommand.
     * @param Api $telegram The Telegram API instance.
     */
    public function __construct(
        protected Api $telegram
    ) {}

    /**
     * Handle the /newtask command.
     * @param array $message The message data.
     * @param string $dataText The text data.
     * @param TelegramUser $user The user object.
     * @return void
     */
    public function handle(array $message, string $dataText, TelegramUser $user): void
    {
        // Reset previous state if needed
        $user->state()?->delete();

        // Create new state
        $state = new TelegramUserState();
        $state->telegram_user_id = $user->id;
        $state->step = 'save_title'; // 👈 first step
        $state->data = [];
        $state->save();

        // Send the first question to the user
        $this->telegram->sendMessage([
            'chat_id' => $user->telegram_id,
            'text' => '📌 Введите заголовок новой задачи:',
        ]);
    }
}
