<?php

namespace App\Telegram\Steps;

use App\Models\TelegramTask;
use App\Models\TelegramUser;
//use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;

class TaskEditStepHandler implements StepHandlerInterface
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
     * Handle the task editing steps.
     *
     * @param string $step The current step in the task editing process.
     * @param string $text The text input from the user.
     * @param TelegramUser $user The user object.
     * @return void
     */
    public function handleStep(string $step, string $text, TelegramUser $user): void
    {
        $chatId = $user->telegram_id;
        $state = $user->state()->firstOrCreate(['telegram_user_id' => $user->id]);
        $data = $state->data ?? [];
        $taskId = $data['task_id'] ?? null;

        switch ($step) {
            case 'task_edit_title':
                if (mb_strlen($text) < 3) {
                    $this->sendMessage($chatId, __('dialogs.headline_too_short'));
                    return;
                }

                $task = $this->getTaskById($user, $taskId);
                $state->delete();
                if (!$task) {
                    return;
                }

                $task->title = $text;
                $task->save();

                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => __('dialogs.title_updated') . ': *' . $task->title . '*',
                    'parse_mode' => 'Markdown',
                ]);
                return;
            case 'task_edit_desc':
                $task = $this->getTaskById($user, $taskId);
                $state->delete();
                if (!$task) {
                    return;
                }

                $task->description = $text;
                $task->save();

                $this->sendMessage($chatId, __('dialogs.description_updated'));
                break;

            case 'task_delete_confirm':
                $task = $this->getTaskById($user, $taskId);
                $state->delete();
                if (!$task) {
                    return;
                }

                $task->delete();

                $this->sendMessage($chatId, __('dialogs.task_deleted'));
                break;

            default:
                $this->sendMessage($chatId, __('dialogs.unknown_step'));
                break;

        }
    }

    /**
     * Retrieve a task by its ID for the given user.
     *
     * @param TelegramUser $user The user object.
     * @param int|string|null $taskId The ID of the task to retrieve.
     * @return TelegramTask|null The task if found, null otherwise.
     */
    protected function getTaskById(TelegramUser $user, int|string|null $taskId): ?TelegramTask
    {
        $chatId = $user->telegram_id;

        if (!$taskId || !is_numeric($taskId)) {
            $this->sendMessage($chatId, __('messages.task_not_found'));
            return null;
        }

        $task = $user->tasks()->find($taskId);
        if (!$task) {
            $this->sendMessage($chatId, __('messages.task_not_found'));
            return null;
        }
        return $task;
    }

    /**
     * Send a message to the user.
     *
     * @param int $chatId The chat ID of the user.
     * @param string $text The text message to send.
     * @return void
     */
    protected function sendMessage(int $chatId, string $text): void
    {
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
        ]);
    }
}
