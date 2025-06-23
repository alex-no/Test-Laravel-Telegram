<?php
namespace App\Telegram\Commands;

// use App\Models\TelegramTask;
use App\Models\TelegramUser;
use App\Models\TelegramUserState;
use Telegram\Bot\Api;
use App\Telegram\Commands\TelegramCommandHandler;
use App\Models\TelegramGroup;
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
        // Get chat info
        $chat = $message['chat'];
        $chatId = $chat['id'];
        $chatType = $chat['type']; // group | supergroup | private
        $isGroup = in_array($chatType, ['group', 'supergroup']);

        // Reset previous state if needed
        $user->state()?->delete();

        // Create new state
        $state = new TelegramUserState();
        $state->telegram_user_id = $user->id;

        $groups = $user->groups()->get();
        if ($groups->count() > 0 && !$isGroup) {
            // The user is a member of groups, ask who the task is for
            $state->step = 'ask_task_target';
            $state->data = [
                'group_options' => $groups->pluck('title', 'id')->toArray(),
                'target' => null,
                'telegram_group_id' => null,
            ];
            $state->save();

            $keyboard = [
                [__('dialogs.personally_me')],
                ...$groups->map(fn($g) => [$g->title])->toArray(),
            ];

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
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
        if ($isGroup) {
            $group = TelegramGroup::firstOrCreate(
                ['telegram_chat_id' => $chatId],
                ['title' => $chat['title'] ?? 'Unnamed group', 'type' => $chat['type']]
            );
            $state->data = [
                'target' => 'group:' . $group->id,
                'telegram_group_id' => $group->id,
            ];
        } else {
            $state->data = [
                'target' => 'user',
                'telegram_group_id' => null,
            ];
        }
        $state->save();

        // Send the first question to the user
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => '📌 ' . __('dialogs.enter_headline') . ':',
        ]);
    }
}
