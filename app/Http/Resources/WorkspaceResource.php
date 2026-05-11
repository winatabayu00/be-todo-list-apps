<?php

namespace App\Http\Resources;

use App\Models\Workspaces\Workspace;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Workspace $resource
 */
class WorkspaceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'description' => $this->resource->description,
            'owner_id' => $this->resource->owner_id,
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
            'deleted_at' => $this->resource->deleted_at?->toISOString(),
            'owner' => $this->whenLoaded('owner', fn() => [
                'id' => $this->resource->owner->id,
                'name' => $this->resource->owner->name,
                'email' => $this->resource->owner->email,
            ]),
            'members' => $this->whenLoaded('members', fn() => $this->resource->members->map(fn($member) => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
            ])),
        ];
    }
}
