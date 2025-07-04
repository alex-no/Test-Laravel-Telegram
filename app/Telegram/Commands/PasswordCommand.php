<?php
namespace App\Telegram\Commands;

use App\Models\TelegramUser;
use Telegram\Bot\Api;

class PasswordCommand implements TelegramCommandHandler
{
    /**
     * Constructor for the PasswordCommand.
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
        // Validate the password format
        if (!preg_match('/^[a-zA-Z0-9]{6,10}$/', $dataText)) {
            $this->telegram->sendMessage([
                'chat_id' => $user->telegram_id,
                'text' => __('messages.invalid_password_format'),
            ]);
            return;
        }
        // Update the user's password
        $user->password = bcrypt($dataText);
        $user->save();

        $this->telegram->sendMessage([
            'chat_id' => $user->telegram_id,
            'text' => __('messages.password_is_set') . '.',
        ]);
    }
}

