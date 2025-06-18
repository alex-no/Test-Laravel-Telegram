<?php

namespace App\Telegram\Commands;

//use App\Models\TelegramTask;
use App\Models\TelegramUser;
use Telegram\Bot\Api;
use App\Models\TelegramUserState;

class TaskEditTitleCommand implements TelegramCommandHandler
{
    /**
     * Constructor for the TaskCreateStepHandler.
     *
     * @param Api $telegram The Telegram API instance.
     */
    public function __construct(
        protected Api $telegram
    ) {}

    /**
     * Handle the task editing title command.
     * @param array $message The incoming message data.
     * @param string $dataText The text input from the user, expected to be the task ID.
     * @param TelegramUser $user The Telegram user associated with the command.
     * @return void
     */
    public function handle(array $message, string $dataText, TelegramUser $user): void
    {
        $taskId = trim($dataText);
        $task = $user->tasks()->find($taskId);
        if (!$task) {
            $this->telegram->sendMessage([
                'chat_id' => $user->telegram_id,
                'text' => __('messages.task_not_found'),
            ]);
            return;
        }

        // Save state
        $state = new TelegramUserState();
        $state->telegram_user_id = $user->id;
        $state->step = 'task_edit_title';
        $state->data = ['task_id' => $task->id];
        $state->save();

        $this->telegram->sendMessage([
            'chat_id' => $user->telegram_id,
            'text' => __('dialogs.enter_new_title'),
        ]);
    }
}
