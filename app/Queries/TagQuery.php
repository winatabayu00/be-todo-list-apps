<?php

namespace App\Queries;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Winata\QueryBuilder\Abstracts\BaseQueryBuilder;

class TagQuery extends BaseQueryBuilder
{
    public function getBaseQuery(): Builder
    {
        return Tag::query();
    }

    public function applyFilterParams(): void
    {
        $filter = request()->input('filter', []);
        $search = request()->input('search');

        // Search by name
        $this->builder->when(!empty($search), function (Builder $builder) use ($search) {
            $builder->where('name', 'LIKE', "%{$search}%");
        });

        // Filter by workspace_id
        $workspaceId = $filter['workspace_id'] ?? null;
        $this->builder->when(!empty($workspaceId), function (Builder $builder) use ($workspaceId) {
            $builder->where('workspace_id', $workspaceId);
        });

        // Filter by color
        $color = $filter['color'] ?? null;
        $this->builder->when(!empty($color), function (Builder $builder) use ($color) {
            $builder->where('color', $color);
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
            $this->builder->orderBy('name', 'asc');
            return;
        }

        $allowed = ['name', 'color', 'created_at', 'updated_at'];
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

        $allowed = ['workspace'];
        $valid = array_intersect($includes, $allowed);
        if (!empty($valid)) {
            $this->builder->with($valid);
        }
    }
}
