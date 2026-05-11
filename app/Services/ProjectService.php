<?php
// app/Services/ProjectService.php

namespace App\Services;

use App\Enums\ProjectVisibility;
use App\Models\Project;
use App\Models\User;
use Winata\PackageBased\Abstracts\BaseService;

class ProjectService extends BaseService
{
    public function create(User $user, array $data): Project
    {
        $validated = $this->validate($data, [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'visibility' => ['nullable', 'in:' . implode(',', ProjectVisibility::values())],
            'workspace_id' => ['required', 'uuid', 'exists:workspaces,id'],
        ]);

        $input = Project::getFillableAttribute($validated);
        $input['created_by'] = $user->id;
        $project = Project::query()->create($input);

        return $project->load(['workspace', 'creator']);
    }

    public function update(Project $project, User $user, array $data): Project
    {
        $validated = $this->validate($data, [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'visibility' => ['nullable', 'in:' . implode(',', ProjectVisibility::values())],
            'workspace_id' => ['sometimes', 'uuid', 'exists:workspaces,id'],
        ]);

        $input = Project::getFillableAttribute($validated);
        $project->update($input);

        return $project->load(['workspace', 'creator']);
    }

    public function delete(Project $project, User $user): void
    {
        // Optional: soft delete or force delete
        $project->delete();
    }

    public function restore(string $projectId, User $user): Project
    {
        $project = Project::withTrashed()->findOrFail($projectId);
        $project->restore();
        return $project;
    }
}
