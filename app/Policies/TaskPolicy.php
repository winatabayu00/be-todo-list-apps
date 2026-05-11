<?php

namespace App\Policies;

use App\Models\Tasks\Task;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        $project = $task->project;
        return $user->id === $task->created_by ||
            $user->id === $task->assignee_id ||
            $project->workspace->owner_id === $user->id ||
            $project->workspace->members()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Task $task): bool
    {
        $project = $task->project;
        return $user->id === $task->created_by ||
            $project->workspace->owner_id === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }

    public function logTime(User $user, Task $task): bool
    {
        return $user->id === $task->assignee_id ||
            $user->id === $task->created_by ||
            $task->project->workspace->owner_id === $user->id;
    }
}
