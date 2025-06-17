<?php

namespace App\Telegram\Commands;

use App\Models\TelegramUser;

interface TelegramCommandHandler
{
    public function handle(array $message, string $dataText, TelegramUser $user): void;
}
