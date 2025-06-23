<?php

namespace App\Telegram\Commands;

use App\Models\TelegramUser;
use Telegram\Bot\Api;
use \Illuminate\Support\Collection;

/**
 * Abstract class for handling task list commands in Telegram.
 * This class provides a base implementation for commands that list tasks,
 * allowing subclasses to define specific task retrieval logic.
 */
abstract class AbstractTaskListCommand implements TelegramCommandHandler
{
    /**
     * Main command handlers.
     *
     * @var Api
     */
    public function __construct(
        protected Api $telegram
    ) {}

    /**
     * Method that returns tasks depending on the command.
     * This method should be implemented in subclasses
     * to provide the specific logic for fetching tasks.
     *
     * @param TelegramUser $user
     * @param string $dataText
     * @return Collection
     * @throws \Exception
     */
    abstract protected function getTasks(TelegramUser $user, string $dataText): Collection;

    /**
     * Returns the header text for the task list.
     * This method can be overridden in subclasses to provide a custom header.
     *
     * @param string $dataText
     * @return string
     */
    protected function getHeader(string $dataText): string
    {
        return '📋 *' . __('messages.your_tasks') . ":*";
    }

    /**
     * Handles the incoming message and retrieves tasks for the user.
     * This method sends a list of tasks to the user based on the provided data text.
     *
     * @param array $message
     * @param string $dataText
     * @param TelegramUser $user
     * @throws \Exception
     */
    public function handle(array $message, string $dataText, TelegramUser $user): void
    {
        $chatId = $message['chat']['id'];
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
            'text' => $this->getHeader($dataText),
            'parse_mode' => 'Markdown',
        ]);

        foreach ($tasks as $task) {
            $dt = $task->updated_at;
            $date = $dt->format($dateFormat);
            $time = $dt->format('H:i');

            $isGroup = $task->telegram_group_id !== null;
            $prefix = $isGroup ? '👥' : '👤';
            $groupTitle = $isGroup ? optional($task->group)->title : '';

            $text = "[{$date} {$time}] {$prefix} *{$task->title}*";
            if ($task->description) {
                $text .= "\n_{$task->description}_";
            }
            if ($groupTitle) {
                $text .= "\n📍 {$groupTitle}";
            }

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
                'reply_markup' => [
                    'inline_keyboard' => [[
                        [
                            'text' => '👁 ' . __('messages.view_detail'),
                            'callback_data' => "/task {$task->id}"
                        ]
                    ]]
                ]
            ]);
        }
    }
}
