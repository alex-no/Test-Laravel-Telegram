<?php
namespace App\Telegram;

use App\Models\TelegramUser;
use Telegram\Bot\Api;
use App\Telegram\Commands\TelegramCommandHandler;

class CommandRouter
{
    protected const COMMAND_MAIN_HANDLERS = [
        'start' => \App\Telegram\Commands\StartCommand::class,
        'help'  => \App\Telegram\Commands\HelpCommand::class,
    ];
    protected const COMMAND_ADD_HANDLERS = [
        'password' => \App\Telegram\Commands\PasswordCommand::class,
    ];

    /**
     * Main command handlers.
     * @var Telegram\Bot\Api $telegram
     */
    public function __construct(protected Api $telegram) {}

    /**
     * Handle incoming messages and route them to the appropriate command handler.
     * @param  array  $message
     * @param  TelegramUser  $user
     * @return void
     */
    public function handle(array $message, TelegramUser $user): void
    {
        $text    = strtolower(trim($message['text']));
        $dataText = '';
        $command = null;

        if (preg_match('/^\/(\w+)$/', $text, $matches)) {
            if (isset(self::COMMAND_MAIN_HANDLERS[$matches[1]])) {
                $command = self::COMMAND_MAIN_HANDLERS[$matches[1]] ?? null;
            }
        } elseif (preg_match('/^(\w+)\:(.+)$/', $text, $matches)) {
            if (isset(self::COMMAND_ADD_HANDLERS[$matches[1]])) {
            $command = self::COMMAND_ADD_HANDLERS[$matches[1]] ?? null;
            $dataText = $matches[2] ?? '';
            }
        }

        if (!$command) {
            // If the command is not found, you can send an error message or ignore
            $this->telegram->sendMessage([
                'chat_id' => $user->telegram_id,
                'text' => __('messages.command_not_found'),
            ]);
            return;
        }

        // Instantiate the command handler and call its handle method
        /** @var TelegramCommandHandler $handler */
        $handler = app($command);
        $handler->handle($message, $dataText, $user);
    }
}

