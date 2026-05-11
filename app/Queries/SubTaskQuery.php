<?php

namespace App\Queries;

use App\Models\Tasks\SubTask;
use Illuminate\Database\Eloquent\Builder;
use Winata\QueryBuilder\Abstracts\BaseQueryBuilder;

class SubTaskQuery extends BaseQueryBuilder
{
    public function getBaseQuery(): Builder
    {
        return SubTask::query();
    }

    public function applyFilterParams(): void
    {
        $filter = request()->input('filter', []);
        $search = request()->input('search');

        // Search by title
        $this->builder->when(!empty($search), function (Builder $builder) use ($search) {
            $builder->where('title', 'LIKE', "%{$search}%");
        });

        // Filter by task_id
        $taskId = $filter['task_id'] ?? null;
        $this->builder->when(!empty($taskId), function (Builder $builder) use ($taskId) {
            $builder->where('task_id', $taskId);
        });

        // Filter by assignee_id
        $assigneeId = $filter['assignee_id'] ?? null;
        $this->builder->when(!empty($assigneeId), function (Builder $builder) use ($assigneeId) {
            $builder->where('assignee_id', $assigneeId);
        });

        // Filter by is_completed
        $isCompleted = $filter['is_completed'] ?? null;
        $this->builder->when($isCompleted !== null, function (Builder $builder) use ($isCompleted) {
            $builder->where('is_completed', filter_var($isCompleted, FILTER_VALIDATE_BOOLEAN));
        });

        // Soft delete filter
        $trashed = $filter['trashed'] ?? null;
        $this->builder->when($trashed === 'only', fn($q) => $q->onlyTrashed());
        $this->builder->when($trashed === 'with', fn($q) => $q->withTrashed());
    }

    public function applySorts(): void
    {
        $sort = request()->input('sort', []);
        if (empty($sort)) {
            $this->builder->orderBy('created_at', 'desc');
            return;
        }

        $allowed = ['created_at', 'updated_at', 'title', 'is_completed'];
        foreach ($sort as $field => $direction) {
            if (in_array($field, $allowed) && in_array($direction, ['asc', 'desc'])) {
                $this->builder->orderBy($field, $direction);
            }
        }
    }

    public function applyIncludes(): void
    {
        $includes = request()->input('include', []);
        if (empty($includes)) {
            return;
        }

        $allowed = ['task', 'assignee'];
        $valid = array_intersect($includes, $allowed);
        if (!empty($valid)) {
            $this->builder->with($valid);
        }
    }
}
