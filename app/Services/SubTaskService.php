<?php

namespace App\Services;

use App\Models\Tasks\SubTask;
use App\Models\Tasks\Task;
use App\Models\User;
use Winata\PackageBased\Abstracts\BaseService;

class SubTaskService extends BaseService
{
    /**
     * @param User $user
     * @param array $data
     * @return SubTask
     */
    public function create(User $user, array $data): SubTask
    {
        $validated = $this->validate($data, [
            'title' => ['required', 'string', 'max:255'],
            'is_completed' => ['nullable', 'boolean'],
            'task_id' => ['required', 'uuid', 'exists:tasks,id'],
            'assignee_id' => ['nullable', 'uuid', 'exists:users,id'],
        ]);

        $input = SubTask::getFillableAttribute($validated);
        $subTask = SubTask::query()->create($input);

        return $subTask->load(['task', 'assignee']);
    }

    /**
     * @param SubTask $subTask
     * @param array $data
     * @return SubTask
     */
    public function update(SubTask $subTask, array $data): SubTask
    {
        $validated = $this->validate($data, [
            'title' => ['sometimes', 'string', 'max:255'],
            'is_completed' => ['sometimes', 'boolean'],
            'assignee_id' => ['nullable', 'uuid', 'exists:users,id'],
        ]);

        $input = SubTask::getFillableAttribute($validated);
        $subTask->update($input);

        return $subTask->fresh()->load(['task', 'assignee']);
    }

    /**
     * @param SubTask $subTask
     * @return void
     */
    public function delete(SubTask $subTask): void
    {
        $subTask->delete();
    }

    /**
     * @param string $subTaskId
     * @return SubTask
     */
    public function restore(string $subTaskId): SubTask
    {
        $subTask = SubTask::withTrashed()->findOrFail($subTaskId);
        $subTask->restore();
        return $subTask;
    }

    /**
     * @param SubTask $subTask
     * @return SubTask
     */
    public function toggleComplete(SubTask $subTask): SubTask
    {
        $subTask->update(['is_completed' => !$subTask->is_completed]);
        return $subTask->fresh();
    }
}
