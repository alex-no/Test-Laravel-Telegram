<?php
namespace App\Telegram\Commands;

use App\Models\TelegramUser;
use Telegram\Bot\Api;

class HelpCommand implements TelegramCommandHandler
{
    /**
     * Список поддерживаемых команд и их описания.
     */
    public const COMMAND_LIST = [
        '/start'   => 'messages.register',
        '/help'    => 'messages.help',
        '/newtask' => 'messages.task_create',
        '/tasks'   => 'messages.tasks_list',
        '/task id'    => 'messages.task_edit',
        '/search text'  => 'messages.task_search',
        'password:...' => 'messages.password_set',
    ];
    /**
     * Constructor for the HelpCommand.
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
            'text' => __('messages.commands') . ":\n" .
                collect(self::COMMAND_LIST)
                    ->map(fn($desc, $cmd) => $cmd . ' — ' . __($desc))
                    ->implode("\n"),
        ]);
    }
}

