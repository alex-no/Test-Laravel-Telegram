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

        // Get the user's latest 20 tasks (pagination can be added later)
        $tasks = $user->tasks()->latest()->take(20)->get();

        if ($tasks->isEmpty()) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => '📭 ' . __('messages.tasks_not_found') . '.',
            ]);
            return;
        }

        $lang = substr($user->language_code ?? 'en', 0, 2);
        // Format the date and time based on the user's language-code
        $dateFormat = ($lang === 'en') ? 'm.d.Y' : 'd.m.Y';

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => '📋 *' . __('messages.your_tasks') . ":*",
            'parse_mode' => 'Markdown',
        ]);
        foreach ($tasks as $task) {
            $dt = $task->updated_at;
            $date = $dt->format($dateFormat);
            $time = $dt->format('H:i');

            $text = "[{$date} {$time}] *{$task->title}*";
            if ($task->description) {
                $text .= "\n_{$task->description}_";
            }

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
                'reply_markup' => [
                    'inline_keyboard' => [[
                        [
                            'text' => '🔧 ' . __('messages.edit'),
                            'callback_data' => "/task {$task->id}"
                        ]
                    ]]
                ]
            ]);
        }
    }
}

