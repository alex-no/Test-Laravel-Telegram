<?php
namespace App\Telegram\Steps;

use App\Models\TelegramTask;
use App\Models\TelegramUser;
//use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;

class TaskCreateStepHandler implements StepHandlerInterface
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
     * Handle the task creation steps.
     *
     * @param string $step The current step in the task creation process.
     * @param string $text The text input from the user.
     * @param TelegramUser $user The user object.
     * @return void
     */
    public function handleStep(string $step, string $text, TelegramUser $user): void
    {
        $chatId = $user->telegram_id;
        $state = $user->state()->firstOrCreate(['telegram_user_id' => $user->id]);
        $data = $state->data ?? [];

        switch ($step) {
            // case 'ask_title':
            //     $this->telegram->sendMessage([
            //         'chat_id' => $chatId,
            //         'text' => '📌 ' . __('dialogs.enter_headline') . ':',
            //     ]);
            //     $state->step = 'save_title';
            //     $state->save();
            //     return;

            case 'save_title':
                $clean = preg_replace('/[^\p{L}\p{N}]/u', '', $text);

                if (mb_strlen($clean, 'UTF-8') < 3) {
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => '❗ ' . __('dialogs.headline_too_short') . ':',
                    ]);
                    return;
                }

                $data['title'] = $text;

                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => '📝 ' . __('dialogs.enter_description') . ':',
                ]);
                $state->step = 'save_description';
                $state->data = $data;
                $state->save();
                return;

            case 'save_description':
                $data['description'] = ($text === '-') ? null : $text;

                $task = new TelegramTask([
                    'title'       => $data['title'],
                    'description' => $data['description'],
                ]);
                $user->tasks()->save($task);
                $state->delete();

                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => '✅ ' . __('dialogs.task_created') . "!\n\n*{$task->title}*" .
                              ($task->description ? "\n📝 {$task->description}" : ''),
                    'parse_mode' => 'Markdown',
                ]);
                return;

            default:
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => '⚠️ ' . __('dialogs.unknown_step') . ' /newtask.',
                ]);
                $state->delete();
        }
    }
}
