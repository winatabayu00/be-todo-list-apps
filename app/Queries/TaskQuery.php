<?php

namespace App\Queries;

use App\Models\Tasks\Task;
use Illuminate\Database\Eloquent\Builder;
use Winata\QueryBuilder\Abstracts\BaseQueryBuilder;

class TaskQuery extends BaseQueryBuilder
{
    public function getBaseQuery(): Builder
    {
        return Task::query();
    }

    public function applyFilterParams(): void
    {
        $filter = request()->input('filter', []);
        $search = request()->input('search');

        // Search by title or description
        $search = $search ?? null;
        $this->builder->when(!empty($search), function (Builder $builder) use ($search) {
            $builder->where(function (Builder $q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        });

        // Filter by project_id
        $projectId = $filter['project_id'] ?? null;
        $this->builder->when(!empty($projectId), function (Builder $builder) use ($projectId) {
            $builder->where('project_id', $projectId);
        });

        // Filter by status (comma-separated)
        $status = $filter['status'] ?? null;
        $this->builder->when(!empty($status), function (Builder $builder) use ($status) {
            $statuses = is_array($status) ? $status : explode(',', $status);
            $builder->whereIn('status', $statuses);
        });

        // Filter by priority (comma-separated)
        $priority = $filter['priority'] ?? null;
        $this->builder->when(!empty($priority), function (Builder $builder) use ($priority) {
            $priorities = is_array($priority) ? $priority : explode(',', $priority);
            $builder->whereIn('priority', $priorities);
        });

        // Filter by assignee_id
        $assigneeId = $filter['assignee_id'] ?? null;
        $this->builder->when(!empty($assigneeId), function (Builder $builder) use ($assigneeId) {
            $builder->where('assignee_id', $assigneeId);
        });

        // Filter by tags (many-to-many)
        $tags = $filter['tags'] ?? null;
        $this->builder->when(!empty($tags), function (Builder $builder) use ($tags) {
            $tagIds = is_array($tags) ? $tags : explode(',', $tags);
            $builder->whereHas('tags', function (Builder $q) use ($tagIds) {
                $q->whereIn('tags.id', $tagIds);
            });
        });

        // Filter due_date range
        $dueDateFrom = $filter['due_date_from'] ?? null;
        $this->builder->when(!empty($dueDateFrom), function (Builder $builder) use ($dueDateFrom) {
            $builder->whereDate('due_date', '>=', $dueDateFrom);
        });

        $dueDateTo = $filter['due_date_to'] ?? null;
        $this->builder->when(!empty($dueDateTo), function (Builder $builder) use ($dueDateTo) {
            $builder->whereDate('due_date', '<=', $dueDateTo);
        });

        // Soft delete filter (trashed)
        $trashed = $filter['trashed'] ?? null;
        $this->builder->when($trashed === 'only', function (Builder $builder) {
            $builder->onlyTrashed();
        });
        $this->builder->when($trashed === 'with', function (Builder $builder) {
            $builder->withTrashed();
        });
    }

    public function applySorts(): void
    {
        $sort = request()->input('sort', []);
        if (empty($sort)) {
            $this->builder->orderBy('order_column', 'asc')
                ->orderBy('created_at', 'desc');
            return;
        }

        $allowed = ['created_at', 'updated_at', 'due_date', 'priority', 'order_column', 'title'];
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

        $allowed = ['project', 'assignee', 'tags', 'assignees', 'subtasks', 'creator'];
        $valid = array_intersect($includes, $allowed);
        if (!empty($valid)) {
            $this->builder->with($valid);
        }
    }
}
