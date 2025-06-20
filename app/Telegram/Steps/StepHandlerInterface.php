<?php

namespace App\Telegram\Steps;

use App\Models\TelegramUser;

interface StepHandlerInterface
{
    public function handleStep(string $step, TelegramUser $user, array $message): void;
}
