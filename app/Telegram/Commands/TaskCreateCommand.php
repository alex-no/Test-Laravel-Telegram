<?php
namespace App\Telegram\Commands;

// use App\Models\TelegramTask;
use App\Models\TelegramUser;
use App\Models\TelegramUserState;
use Telegram\Bot\Api;
use App\Telegram\Commands\TelegramCommandHandler;
// use Illuminate\Support\Facades\Log;

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
        // Reset previous state if needed
        $user->state()?->delete();

        // Create new state
        $state = new TelegramUserState();
        $state->telegram_user_id = $user->id;

        $groups = $user->groups()->get();
        if ($groups->count() > 0 && $message['chat']['type'] === 'private') {
            // The user is a member of groups, ask who the task is for
            $state->step = 'ask_task_target';
            $state->data = ['group_options' => $groups->pluck('title', 'id')->toArray()];
            $state->save();

            $keyboard = [
                [__('dialogs.personally_me')],
                ...$groups->map(fn($g) => [$g->title])->toArray(),
            ];

            $this->telegram->sendMessage([
                'chat_id' => $user->telegram_id,
                'text'    => __('dialogs.for_whom_task'),
                'reply_markup' => json_encode([
                    'keyboard' => $keyboard,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
            ]);
            return;
        }

        // No groups — start with the headline right away
        $state->step = 'save_title';
        $state->data = ['target' => 'user']; // default — personal task
        $state->save();

        // Send the first question to the user
        $this->telegram->sendMessage([
            'chat_id' => $user->telegram_id,
            'text' => '📌 ' . __('dialogs.enter_headline') . ':',
        ]);
    }
}
