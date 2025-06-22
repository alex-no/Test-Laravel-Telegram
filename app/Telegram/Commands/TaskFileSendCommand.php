<?php
namespace App\Telegram\Commands;

use App\Models\TelegramTaskFile;
use App\Models\TelegramUser;
use Telegram\Bot\Api;

class TaskFileSendCommand implements TelegramCommandHandler
{
    public function __construct(
        protected Api $telegram
    ) {}

    public function handle(array $message, string $dataText, TelegramUser $user): void
    {
        $chatId = $user->telegram_id;

        $fileId = (int) trim($dataText);
        $file = TelegramTaskFile::find($fileId);

        if (!$file || $file->task->telegram_user_id !== $user->id) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => '❗ ' . __('messages.file_not_found_or_forbidden'),
            ]);
            return;
        }

        $sendData = [
            'chat_id' => $chatId,
            'caption' => $file->file_name ?? null,
        ];

        if (!empty($file->mime_type) && str_starts_with($file->mime_type, 'image')) {
            $sendData['photo'] = $file->file_id;
            $this->telegram->sendPhoto($sendData);
        } elseif (!empty($file->mime_type) && str_starts_with($file->mime_type, 'video')) {
            $sendData['video'] = $file->file_id;
            $this->telegram->sendVideo($sendData);
        } elseif (empty($file->mime_type) && empty($file->file_name)) {
            // Most likely, this is an image without a mime type — send as photo
            $sendData['photo'] = $file->file_id;
            $this->telegram->sendPhoto($sendData);
        } else {
            $sendData['document'] = $file->file_id;
            $this->telegram->sendDocument($sendData);
        }
    }
}
