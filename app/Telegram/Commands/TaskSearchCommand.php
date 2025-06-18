<?php

namespace App\Telegram\Commands;

use App\Models\TelegramUser;

class TaskSearchCommand extends AbstractTaskListCommand
{
    protected function getTasks(TelegramUser $user, string $dataText)
    {
        if (trim($dataText) === '') {
            return collect(); // Empty query — do not search anything
        }

        return $user->tasks()
            ->where(function ($query) use ($dataText) {
                $query->where('title', 'ILIKE', "%$dataText%")
                      ->orWhere('description', 'ILIKE', "%$dataText%");
            })
            ->latest()
            ->take(20)
            ->get();
    }
}
