<?php

namespace App\Telegram\Commands;

use App\Models\TelegramUser;
use App\Models\TelegramUserState;
use Telegram\Bot\Api;

class TaskDeleteCommand implements TelegramCommandHandler
{
    public function __construct(
        protected Api $telegram
    ) {}

    public function handle(array $message, string $dataText, TelegramUser $user): void
    {
        // Get the chat ID from the message data
        $chatId = $message['chat']['id'];

        $taskId = trim($dataText);
        $task = $user->tasks()->find($taskId);

        if (!$task) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => __('messages.task_not_found'),
            ]);
            return;
        }

        $state = new TelegramUserState();
        $state->telegram_user_id = $user->id;
        $state->step = 'task_delete_confirm';
        $state->data = ['task_id' => $task->id];
        $state->save();

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => __('dialogs.confirm_task_deletion'),
        ]);
    }
}
