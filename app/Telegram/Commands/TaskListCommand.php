<?php
namespace App\Telegram\Commands;

use App\Models\TelegramUser;
use Telegram\Bot\Api;
use App\Models\TelegramTask;

class TaskListCommand implements TelegramCommandHandler
{
    /**
     * Constructor for the TaskListCommand.
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
        $chatId = $user->telegram_id;

        // Get the user's latest 10 tasks (pagination can be added later)
        $tasks = $user->tasks()->latest()->take(10)->get();

        if ($tasks->isEmpty()) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => '📭 У вас пока нет задач.',
            ]);
            return;
        }

        $text = "📋 *Ваши задачи:*\n\n";
        foreach ($tasks as $task) {
            $text .= "• *{$task->title}*";
            if ($task->description) {
                $text .= "\n  _{$task->description}_";
            }
            $text .= "\n\n";
        }

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ]);
    }
}

