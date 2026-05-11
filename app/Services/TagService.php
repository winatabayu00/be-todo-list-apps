<?php

namespace App\Services;

use App\Models\Tag;
use App\Models\Workspaces\Workspace;
use Winata\PackageBased\Abstracts\BaseService;

class TagService extends BaseService
{
    /**
     * @param Workspace $workspace
     * @param array $data
     * @return Tag
     */
    public function create(Workspace $workspace, array $data): Tag
    {
        $validated = $this->validate($data, [
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
        ]);

        $validated['workspace_id'] = $workspace->id;
        $input = Tag::getFillableAttribute($validated);
        return Tag::query()->create($input);
    }

    /**
     * @param Tag $tag
     * @param array $data
     * @return Tag
     */
    public function update(Tag $tag, array $data): Tag
    {
        $validated = $this->validate($data, [
            'name' => ['sometimes', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
        ]);

        $input = Tag::getFillableAttribute($validated);
        $tag->update($input);
        return $tag->fresh();
    }

    /**
     * @param Tag $tag
     * @return void
     */
    public function delete(Tag $tag): void
    {
        $tag->delete();
    }

    /**
     * @param string $tagId
     * @return Tag
     */
    public function restore(string $tagId): Tag
    {
        $tag = Tag::withTrashed()->findOrFail($tagId);
        $tag->restore();
        return $tag;
    }
}
