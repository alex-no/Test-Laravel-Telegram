<?php

namespace App\Telegram\Commands;

use App\Models\TelegramUser;

class TaskListCommand extends AbstractTaskListCommand
{
    protected function getTasks(TelegramUser $user, string $dataText)
    {
        return $user->tasks()
            ->latest()
            ->take(20)
            ->get();
    }
}
