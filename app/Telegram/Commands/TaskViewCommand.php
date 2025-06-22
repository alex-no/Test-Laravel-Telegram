<?php

namespace App\Telegram\Commands;

// use App\Models\TelegramTask;
use App\Models\TelegramUser;
use Telegram\Bot\Api;
use Illuminate\Support\Facades\Log;

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

        $task = $user->tasks()->with('files')->find($taskId);
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

        if ($task->files->isNotEmpty()) {
            $text .= "\n\n📎 " . __('dialogs.attached_files') . ':';
            foreach ($task->files as $file) {
                $label = $file->file_name ?: __('dialogs.file');
                $keyboard[] = [[
                    'text' => "📎 {$label}",
                    'callback_data' => "/task.file:{$file->id}",
                ]];
            }
        }

        // Create inline keyboard
       $keyboard[] = [
            ['text' => '✏', 'callback_data' => "/task.edit.title:{$task->id}"],
            ['text' => '📝', 'callback_data' => "/task.edit.desc:{$task->id}"],
            ['text' => '🗑', 'callback_data' => "/task.delete:{$task->id}"],
        ];
Log::debug('keyboard generated', $keyboard);


        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
            'reply_markup' => ['inline_keyboard' => $keyboard],
        ]);

        // Send files one by one
        foreach ($task->files as $file) {
            $sendData = [
                'chat_id' => $chatId,
                'caption' => $file->file_name ?? null,
            ];

            if (str_starts_with($file->mime_type ?? '', 'image')) {
                $sendData['photo'] = $file->file_id;
                $this->telegram->sendPhoto($sendData);
            } elseif (str_starts_with($file->mime_type ?? '', 'video')) {
                $sendData['video'] = $file->file_id;
                $this->telegram->sendVideo($sendData);
            } else {
                $sendData['document'] = $file->file_id;
                $this->telegram->sendDocument($sendData);
            }
        }
    }
}
