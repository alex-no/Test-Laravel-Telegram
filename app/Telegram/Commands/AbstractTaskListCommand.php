<?php

namespace App\Telegram\Commands;

use App\Models\TelegramUser;
use Telegram\Bot\Api;

abstract class AbstractTaskListCommand implements TelegramCommandHandler
{
    public function __construct(
        protected Api $telegram
    ) {}

    /**
    * Method that returns tasks depending on the command.
     */
    abstract protected function getTasks(TelegramUser $user, string $dataText);

    public function handle(array $message, string $dataText, TelegramUser $user): void
    {
        $chatId = $user->telegram_id;
        $tasks = $this->getTasks($user, $dataText);

        if ($tasks->isEmpty()) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => '📭 ' . __('messages.tasks_not_found') . '.',
            ]);
            return;
        }

        $lang = substr($user->language_code ?? 'en', 0, 2);
        $dateFormat = ($lang === 'en') ? 'm.d.Y' : 'd.m.Y';

        // Send the list header
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
                            'text' => '👁 ' . __('messages.view'),
                            'callback_data' => "/task {$task->id}"
                        ]
                    ]]
                ]
            ]);
        }
    }
}
