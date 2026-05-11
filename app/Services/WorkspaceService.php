<?php

namespace App\Services;

use App\Models\User;
use App\Models\Workspaces\Workspace;
use Illuminate\Container\EntryNotFoundException;
use Illuminate\Contracts\Container\CircularDependencyException;
use Illuminate\Pagination\LengthAwarePaginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Winata\PackageBased\Abstracts\BaseService;

class WorkspaceService extends BaseService
{
    /**
     * @param User $user
     * @param array $data
     * @return Workspace
     */
    public function create(User $user, array $data): Workspace
    {
        $validated = $this->validate($data, [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['owner_id'] = $user->id;
        $input = Workspace::getFillableAttribute($validated);
        $workspace = Workspace::query()->create($input);

        // Auto add owner as member
        $workspace->members()->syncWithoutDetaching([$user->id]);

        return $workspace->load(['owner', 'members']);
    }

    /**
     * @param Workspace $workspace
     * @param array $data
     * @return Workspace
     */
    public function update(Workspace $workspace, array $data): Workspace
    {
        $validated = $this->validate($data, [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $input = Workspace::getFillableAttribute($validated);
        $workspace->update($input);

        return $workspace->fresh()->load(['owner', 'members']);
    }

    /**
     * @param Workspace $workspace
     * @return void
     */
    public function delete(Workspace $workspace): void
    {
        $workspace->delete();
    }

    /**
     * @param string $workspaceId
     * @return Workspace
     */
    public function restore(string $workspaceId): Workspace
    {
        $workspace = Workspace::withTrashed()->findOrFail($workspaceId);
        $workspace->restore();
        return $workspace;
    }

    /**
     * @param Workspace $workspace
     * @param string $userId
     * @return void
     */
    public function addMember(Workspace $workspace, string $userId): void
    {
        $workspace->members()->syncWithoutDetaching([$userId]);
    }

    /**
     * @param Workspace $workspace
     * @param string $userId
     * @return void
     */
    public function removeMember(Workspace $workspace, string $userId): void
    {
        $workspace->members()->detach($userId);
    }

    /**
     * @param Workspace $workspace
     * @param string|null $search
     * @return LengthAwarePaginator
     * @throws EntryNotFoundException
     * @throws CircularDependencyException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getMembers(Workspace $workspace, ?string $search = null): LengthAwarePaginator
    {
        $query = $workspace->members();
        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        }
        return $query->paginate(request()->get('per_page', 15));
    }
}
