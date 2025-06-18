<?php

namespace App\Telegram\Commands;

use App\Models\TelegramUser;
use \Illuminate\Support\Collection;

/**
 * Class TaskSearchCommand
 * Handles the search functionality for tasks in Telegram.
 * This command allows users to search for tasks by title or description.
 * @package App\Telegram\Commands
 */
class TaskSearchCommand extends AbstractTaskListCommand
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
