<?php
namespace App\Telegram\Commands;

use App\Models\TelegramTask;
use App\Models\TelegramUser;
use App\Models\TelegramUserState;
use Telegram\Bot\Api;
use App\Telegram\Commands\TelegramCommandHandler;
use Illuminate\Support\Facades\Log;

class TaskCreateCommand implements TelegramCommandHandler
{
    /**
     * Constructor for the TaskCreateCommand.
     * @param Api $telegram The Telegram API instance.
     */
    public function __construct(
        protected Api $telegram
    ) {}

    /**
     * Handle the /newtask command.
     * @param array $message The message data.
     * @param string $dataText The text data.
     * @param TelegramUser $user The user object.
     * @return void
     */
    public function handle(array $message, string $dataText, TelegramUser $user): void
    {
        $chatId = $user->telegram_id;
        $text = trim($message['text'] ?? '');

        // Get or create the user's state
        $state = $user->state()->firstOrCreate([
            'telegram_user_id' => $user->id,
        ]);

        $step = $state->step ?? 'ask_title';
        $data = $state->data ?? [];

        switch ($step) {
            case 'ask_title':
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => '📌 Введите заголовок новой задачи:',
                ]);
                $state->step = 'save_title';
                $state->save();
                return;

            case 'save_title':
                if (mb_strlen($text) < 3) {
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => '❗ Заголовок слишком короткий. Попробуйте ещё раз:',
                    ]);
                    return;
                }

                $data['title'] = $text;

                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => '📝 Теперь введите описание задачи (или отправьте "-" для пропуска):',
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

                $state->delete(); // clear the state

                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "✅ Задача создана!\n\n*{$task->title}*" .
                              ($task->description ? "\n📝 {$task->description}" : ''),
                    'parse_mode' => 'Markdown',
                ]);
                return;

            default:
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => '⚠️ Произошла ошибка. Попробуйте начать сначала.',
                ]);
                $state->delete();
                return;
        }
    }
}
