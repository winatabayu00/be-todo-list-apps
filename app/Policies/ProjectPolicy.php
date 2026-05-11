<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        return $user->id === $project->created_by ||
            $project->workspace->owner_id === $user->id ||
            $project->workspace->members()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->id === $project->created_by || $project->workspace->owner_id === $user->id;
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->created_by || $project->workspace->owner_id === $user->id;
    }
}
