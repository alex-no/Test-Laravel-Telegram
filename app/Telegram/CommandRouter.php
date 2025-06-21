<?php
namespace App\Telegram;

use App\Models\TelegramUser;
use Telegram\Bot\Api;
use App\Telegram\Commands\TelegramCommandHandler;
// use Illuminate\Support\Facades\Log;

class CommandRouter
{
    protected const COMMAND_MAIN_HANDLERS = [
        'start' => \App\Telegram\Commands\StartCommand::class,
        'help'  => \App\Telegram\Commands\HelpCommand::class,
        'newtask'  => \App\Telegram\Commands\TaskCreateCommand::class,
        'tasks'    => \App\Telegram\Commands\TaskListCommand::class,
        'task'     => \App\Telegram\Commands\TaskViewCommand::class,
        'search'   => \App\Telegram\Commands\TaskSearchCommand::class,
    ];
    protected const STEP_HANDLERS = [
        'ask_title'        => \App\Telegram\Steps\TaskCreateStepHandler::class,
        'save_title'       => \App\Telegram\Steps\TaskCreateStepHandler::class,
        'save_description' => \App\Telegram\Steps\TaskCreateStepHandler::class,
        'wait_files'       => \App\Telegram\Steps\TaskCreateStepHandler::class,

        'task_edit_title'     => \App\Telegram\Steps\TaskEditStepHandler::class,
        'task_edit_desc'      => \App\Telegram\Steps\TaskEditStepHandler::class,
        'task_delete_confirm' => \App\Telegram\Steps\TaskEditStepHandler::class,
        // other steps from other dialogs can be added here
    ];
    protected const COMMAND_ADD_HANDLERS = [
        'password' => \App\Telegram\Commands\PasswordCommand::class,

        'task.edit.title' => \App\Telegram\Commands\TaskEditTitleCommand::class,
        'task.edit.desc'  => \App\Telegram\Commands\TaskEditDescriptionCommand::class,
        'task.delete'     => \App\Telegram\Commands\TaskDeleteCommand::class,
    ];

    /**
     * Main command handlers.
     * @var Telegram\Bot\Api $telegram
     */
    public function __construct(
        protected Api $telegram
    ) {}

    /**
     * Handle incoming messages and route them to the appropriate command handler.
     * @param  array  $message
     * @param  TelegramUser  $user
     * @return void
     */
    public function handle(array $message, TelegramUser $user): void
    {
        // ✅ Universal step handler
        $step = $user->state?->step;
        if ($step && isset(self::STEP_HANDLERS[$step])) {
            /** @var StepHandlerInterface $handler */
            $handler = app(self::STEP_HANDLERS[$step]);
            $handler->handleStep($step, $user, $message);
            return;
        }

        $text    = strtolower(trim($message['text']));
        $dataText = '';
        $command = null;

        // /command or /command param
        if (preg_match('/^\/?(\w+)(?:\s+(.*))?$/', $text, $matches)) {
            $cmd = $matches[1];
            $dataText = $matches[2] ?? '';
            if (isset(self::COMMAND_MAIN_HANDLERS[$cmd])) {
                $command = self::COMMAND_MAIN_HANDLERS[$cmd];
            }
        }
        // command:param (without slash)
        elseif (preg_match('/^\/?([\w\.]+):(.+)$/', $text, $matches)) {
            $cmd = $matches[1];
            $dataText = $matches[2];
            if (isset(self::COMMAND_ADD_HANDLERS[$cmd])) {
                $command = self::COMMAND_ADD_HANDLERS[$cmd];
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

