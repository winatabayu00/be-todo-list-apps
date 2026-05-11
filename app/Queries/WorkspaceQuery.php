<?php

namespace App\Queries;

use App\Models\Workspaces\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Winata\QueryBuilder\Abstracts\BaseQueryBuilder;

class WorkspaceQuery extends BaseQueryBuilder
{
    public function getBaseQuery(): Builder
    {
        return Workspace::query();
    }

    public function applyFilterParams(): void
    {
        $filter = request()->input('filter', []);
        $search = request()->input('search');

        // Search by name or description
        $this->builder->when(!empty($search), function (Builder $builder) use ($search) {
            $builder->where(function (Builder $q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        });

        // Filter by owner_id
        $ownerId = $filter['owner_id'] ?? null;
        $this->builder->when(!empty($ownerId), function (Builder $builder) use ($ownerId) {
            $builder->where('owner_id', $ownerId);
        });

        // Filter by member (user who is member of workspace)
        $memberId = $filter['member_id'] ?? null;
        $this->builder->when(!empty($memberId), function (Builder $builder) use ($memberId) {
            $builder->whereHas('members', function (Builder $q) use ($memberId) {
                $q->where('user_id', $memberId);
            });
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

        $allowed = ['created_at', 'updated_at', 'name'];
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

        $allowed = ['owner', 'members', 'projects', 'tags'];
        $valid = array_intersect($includes, $allowed);
        if (!empty($valid)) {
            $this->builder->with($valid);
        }
    }
}
