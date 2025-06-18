<?php

namespace App\Telegram\Commands;

use App\Models\TelegramUser;
use \Illuminate\Support\Collection;

/**
 * Class TaskListCommand
 * Handles the listing of tasks in Telegram.
 * This command retrieves the latest tasks for a user and sends them in a formatted message.
 * @package App\Telegram\Commands
 */
class TaskListCommand extends AbstractTaskListCommand
{
    /**
     * Retrieve tasks based on the search query.
     * This method searches for tasks by title or description and returns a collection of matching tasks.
     *
     * @param TelegramUser $user
     * @param string $dataText
     * @return Collection
     */
    protected function getTasks(TelegramUser $user, string $dataText): Collection
    {
        return $user->tasks()
            ->latest()
            ->take(20)
            ->get();
    }
}
