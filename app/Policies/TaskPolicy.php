<?php

namespace App\Policies;

use App\Models\Tasks\Task;
use App\Models\User;
use App\Models\Workspaces\Workspace;

class TaskPolicy
{
    /**
     * Determine if user can view the task.
     */
    public function view(User $user, Task $task): bool
    {
        return $this->hasAccessToTask($user, $task);
    }

    /**
     * Determine if user can update the task.
     */
    public function update(User $user, Task $task): bool
    {
        return $this->hasAccessToTask($user, $task);
    }

    /**
     * Determine if user can delete the task.
     */
    public function delete(User $user, Task $task): bool
    {
        return $this->hasAccessToTask($user, $task);
    }

    /**
     * Check if user has access to task's workspace.
     */
    protected function hasAccessToTask(User $user, Task $task): bool
    {
        $workspace = $task->project?->workspace;
        if (!$workspace) {
            return false;
        }

        // Owner atau member workspace
        if ($workspace->owner_id === $user->id) {
            return true;
        }

        return $workspace->members()->where('user_id', $user->id)->exists();
    }
}
