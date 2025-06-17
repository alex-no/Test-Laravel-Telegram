<?php
namespace App\Telegram\Steps;

use App\Models\TelegramTask;
use App\Models\TelegramUser;
//use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;

class TaskCreateStepHandler implements StepHandlerInterface
{
    public function __construct(
        protected Api $telegram
    ) {}

    public function handleStep(string $step, string $text, TelegramUser $user): void
    {
        $chatId = $user->telegram_id;
        $state = $user->state()->firstOrCreate(['telegram_user_id' => $user->id]);
        $data = $state->data ?? [];

        switch ($step) {
            // case 'ask_title':
            //     $this->telegram->sendMessage([
            //         'chat_id' => $chatId,
            //         'text' => '📌 Введите заголовок новой задачи:',
            //     ]);
            //     $state->step = 'save_title';
            //     $state->save();
            //     return;

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
                    'text' => '📝 Теперь введите описание задачи (или "-" для пропуска):',
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
                    'text' => "✅ Задача создана!\n\n*{$task->title}*" .
                              ($task->description ? "\n📝 {$task->description}" : ''),
                    'parse_mode' => 'Markdown',
                ]);
                return;

            default:
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => '⚠️ Неизвестный шаг. Попробуйте ещё раз /newtask.',
                ]);
                $state->delete();
        }
    }
}
