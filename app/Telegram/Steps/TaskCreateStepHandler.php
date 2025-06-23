<?php
namespace App\Telegram\Steps;

use App\Models\TelegramTask;
use App\Models\TelegramUser;
use Telegram\Bot\Api;
// use Illuminate\Support\Facades\Log;

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
    public function handleStep(string $step, TelegramUser $user, array $message): void
    {
        $chatId = $message['chat']['id'];
        $state = $user->state()->firstOrCreate(['telegram_user_id' => $user->id]);
        $data = $state->data ?? [];
        $text = strtolower(trim($message['text'] ?? ''));

        switch ($step) {
            case 'ask_task_target':
                $answer = strtolower(trim($message['text']));
                $data = $user->state->data ?? [];

                if ($answer === strtolower(__('dialogs.personally_me'))) {
                    $data['target'] = 'user';
                    unset($data['telegram_group_id']);
                } else {
                    // search for the group by name
                    $group = $user->groups()->where('title', $answer)->first();
                    if (!$group) {
                        $this->telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text'    => __('dialogs.not_recognize_group'),
                        ]);
                        return;
                    }
                    $data['target'] = 'group:' . $group->id;
                    $data['telegram_group_id'] = $group->id;
                }

                // Update the step
                $user->state->step = 'save_title';
                $user->state->data = $data;
                $user->state->save();

                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text'    => '📌 ' . __('dialogs.enter_headline') . ':',
                ]);
                return;

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
                $state->data = $data;
                $state->step = 'wait_files';
                $state->save();

                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => '📎 ' . __('dialogs.can_attach_files') . ' "ready".',
                ]);
                return;

            case 'wait_files':
                if (mb_strtolower(trim($text)) === 'ready') {
                    $task = new TelegramTask([
                        'title' => $data['title'],
                        'description' => $data['description'],
                        'telegram_group_id' => $data['telegram_group_id'] ?? null,
                        'target' => $data['target'] ?? 'user',
                    ]);

                    $user->tasks()->save($task);

                    // Attach deferred files from session or database
                    if (!empty($data['files'])) {
                        foreach ($data['files'] as $file) {
                            $task->files()->create($file);
                        }
                    }

                    $state->delete();

                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => '✅ ' . __('dialogs.task_created') . "!\n\n*{$task->title}*"
                                . ($task->description ? "\n📝 {$task->description}" : '')
                                . (isset($data['files']) ? "\n📎 " . __('dialogs.files') . ': ' . count($data['files']) : ''),
                        'parse_mode' => 'Markdown',
                    ]);
                    return;
                }

                // File handling (document, photo, video, etc.)
                $fileInfo = $this->extractFileFromMessage($message);
                if ($fileInfo) {
                    $data['files'][] = $fileInfo;
                    $state->data = $data;
                    $state->save();

                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => '✅ ' . __('dialogs.file_attached'),
                    ]);
                    return;
                }

                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => '📎 ' . __('dialogs.send_file_or_type') . ' "ready".',
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

    /**
     * Extracts file information from the message.
     * This method checks for document, photo, or video files in the message.
     * If a file is found, it returns an array with the file's ID, unique ID, name, MIME type, and size.
     * If no file is found, it returns null.
     *
     * @param array $message The message data.
     * @return array|null Returns an array with file information or null if no file is found
     */
    protected function extractFileFromMessage(array $message): ?array
    {
        if (isset($message['document'])) {
            $file = $message['document'];
            return [
                'file_id' => $file['file_id'],
                'file_unique_id' => $file['file_unique_id'],
                'file_name' => $file['file_name'] ?? null,
                'mime_type' => $file['mime_type'] ?? null,
                'file_size' => $file['file_size'] ?? null,
                'file_type' => 'document',
            ];
        }

        if (isset($message['photo'])) {
            $file = end($message['photo']);
            return [
                'file_id' => $file['file_id'],
                'file_unique_id' => $file['file_unique_id'],
                'file_name' => null, // Photo files typically do not have a file name
                'mime_type' => 'image/jpeg',
                'file_size' => $file['file_size'] ?? null,
                'file_type' => 'photo',
            ];
        }

        if (isset($message['video'])) {
            $file = $message['video'];
            return [
                'file_id' => $file['file_id'],
                'file_unique_id' => $file['file_unique_id'],
                'file_name' => $file['file_name'] ?? null,
                'mime_type' => $file['mime_type'] ?? null,
                'file_size' => $file['file_size'] ?? null,
                'file_type' => 'video',
            ];
        }

        return null;
    }

}
