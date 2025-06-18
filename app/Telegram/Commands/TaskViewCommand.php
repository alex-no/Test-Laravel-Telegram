<?php

namespace App\Telegram\Commands;

use App\Models\TelegramTask;
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
     * Handle the /task id command.
     * @param array $message The message data.
     * @param string $dataText The text data.
     * @param TelegramUser $user The user object.
     * @return void
     */
    public function handle(array $message, string $dataText, TelegramUser $user): void
    {
        $chatId = $user->telegram_id;

        $taskId = (int) trim($dataText);
        if (!$taskId) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => '❗ ' . __('messages.invalid_task_id'),
            ]);
            return;
        }

        $task = $user->tasks()->find($taskId);
        if (!$task) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => '❗ ' . __('messages.task_not_found'),
            ]);
            return;
        }

        // Compose message
        $text = "🗂 *{$task->title}*";
        if ($task->description) {
            $text .= "\n\n📝 {$task->description}";
        }

        // Send with buttons
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
            'reply_markup' => [
                'inline_keyboard' => [[
                    ['text' => '✏', 'callback_data' => "/task.edit.title:{$task->id}"],
                    ['text' => '📝', 'callback_data' => "/task.edit.desc:{$task->id}"],
                    ['text' => '🗑', 'callback_data' => "/task.delete:{$task->id}"],
                ]]
            ],
        ]);
    }
}
