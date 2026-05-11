<?php
// app/Queries/ProjectQuery.php

namespace App\Queries;

use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Winata\QueryBuilder\Abstracts\BaseQueryBuilder;

class ProjectQuery extends BaseQueryBuilder
{
    public function getBaseQuery(): Builder
    {
        return Project::query();
    }

    public function applyFilterParams(): void
    {
        $filter = request()->input('filter', []);

        // Search by name or description
        $search = request()->input('search');
        $this->builder->when(!empty($search), function (Builder $builder) use ($search) {
            $builder->where(function (Builder $q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        });

        // Filter by workspace_id
        $workspaceId = $filter['workspace_id'] ?? null;
        $this->builder->when(!empty($workspaceId), function (Builder $builder) use ($workspaceId) {
            $builder->where('workspace_id', $workspaceId);
        });

        // Filter by visibility (comma-separated)
        $visibility = $filter['visibility'] ?? null;
        $this->builder->when(!empty($visibility), function (Builder $builder) use ($visibility) {
            $visibilities = is_array($visibility) ? $visibility : explode(',', $visibility);
            $builder->whereIn('visibility', $visibilities);
        });

        // Filter by created_by
        $createdBy = $filter['created_by'] ?? null;
        $this->builder->when(!empty($createdBy), function (Builder $builder) use ($createdBy) {
            $builder->where('created_by', $createdBy);
        });

        // Soft delete filter
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
            $this->builder->orderBy('created_at', 'desc');
            return;
        }

        $allowed = ['created_at', 'updated_at', 'name', 'visibility'];
        foreach ($sort as $field => $direction) {
            if (in_array($field, $allowed) && in_array($direction, ['asc', 'desc'])) {
                $this->builder->orderBy($field, $direction);
            }
        }
    }

    public function applyIncludes(): void
    {
        $includes = request()->input('include', []);
        if (empty($includes)) return;

        $allowed = ['workspace', 'creator', 'tasks'];
        $valid = array_intersect($includes, $allowed);
        if (!empty($valid)) {
            $this->builder->with($valid);
        }
    }
}
