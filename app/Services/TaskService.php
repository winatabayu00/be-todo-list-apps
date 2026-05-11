<?php

namespace App\Services;

use App\Enums\TasksLogActions;
use App\Enums\TasksPriority;
use App\Enums\TasksStatus;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskLog;
use App\Models\User;
use Illuminate\Validation\Rules\Enum;
use Winata\PackageBased\Abstracts\BaseService;

class TaskService extends BaseService
{
    /**
     * @param User $user
     * @param array $data
     * @return Task
     */
    public function create(User $user, array $data): Task
    {
        $validated = $this->validate(
            inputs: $data,
            rules: [
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'status' => ['nullable', new Enum(TasksStatus::class)],
                'priority' => ['nullable', new Enum(TasksPriority::class)],
                'start_date' => ['nullable', 'date'],
                'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
                'assignee_id' => ['nullable', 'uuid', 'exists:users,id'],
                'project_id' => ['required', 'uuid', 'exists:projects,id'],
                'time_estimate' => ['nullable', 'integer', 'min:0'],
                'order_column' => ['nullable', 'integer'],
                'tags' => ['nullable', 'array'],
                'tags.*' => ['uuid', 'exists:tags,id'],
                'assignees' => ['nullable', 'array'],
                'assignees.*' => ['uuid', 'exists:users,id'],
            ]);

        $input = Task::getFillableAttribute($validated);
        $input['created_by'] = $user->id;
        $task = Task::query()->create($input);

        if (!empty($validated['tags']) && is_array($validated['tags']) && count($validated['tags']) > 0) {
            $task->tags()->sync($validated['tags']);
        }

        if (!empty($validated['assignees']) && is_array($validated['assignees']) && count($validated['assignees']) > 0) {
            $task->assignees()->sync($validated['assignees']);
        }

        return $task->load(['project', 'assignee', 'tags', 'assignees']);
    }

    /**
     * @param Task $task
     * @param User $user
     * @param array $inputs
     * @return Task
     */
    public function update(Task $task, User $user, array $inputs): Task
    {
        $validated = $this->validate($inputs, [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', new Enum(TasksStatus::class)],
            'priority' => ['nullable', new Enum(TasksPriority::class)],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'assignee_id' => ['nullable', 'uuid', 'exists:users,id'],
            'project_id' => ['sometimes', 'uuid', 'exists:projects,id'],
            'time_estimate' => ['nullable', 'integer', 'min:0'],
            'order_column' => ['nullable', 'integer'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['uuid', 'exists:tags,id'],
            'assignees' => ['nullable', 'array'],
            'assignees.*' => ['uuid', 'exists:users,id'],
        ]);

        $oldStatus = $task->status;
        $oldPriority = $task->priority;

        $taskData = method_exists(Task::class, 'getFillableAttribute')
            ? Task::getFillableAttribute($validated)
            : $validated;

        $task->update($taskData);

        if (array_key_exists('tags', $validated)) {
            $task->tags()->sync($validated['tags']);
        }
        if (array_key_exists('assignees', $validated)) {
            $task->assignees()->sync($validated['assignees']);
        }

        if ($oldStatus !== $task->status) {
            TaskLog::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'action' => TasksLogActions::STATUS_CHANGED->value,
                'changes' => json_encode(['from' => $oldStatus, 'to' => $task->status]),
            ]);
        }

        if ($oldPriority !== $task->priority) {
            TaskLog::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'action' => TasksLogActions::PRIORITY_CHANGED->value,
                'changes' => json_encode(['from' => $oldPriority, 'to' => $task->priority]),
            ]);
        }

        return $task->load(['project', 'assignee', 'tags', 'assignees']);
    }

    /**
     * @param Task $task
     * @param User $user
     * @return void
     */
    public function delete(Task $task, User $user): void
    {
        TaskLog::create([
            'task_id' => $task->id,
            'user_id' => $user,
            'action' => TasksLogActions::DELETED->value,
            'changes' => $task->toArray(),
        ]);
        $task->delete();
    }


    /**
     * @param string $taskId
     * @param User $user
     * @return Task
     */
    public function restore(string $taskId, User $user): Task
    {
        /** @var Task $task */
        $task = Task::withTrashed()->findOrFail($taskId);
        $task->restore();

        TaskLog::create([
            'task_id' => $task->id,
            'user_id' => $user,
            'action' => TasksLogActions::RESTORED->value,
            'changes' => null,
        ]);

        return $task;
    }
}
