<?php
namespace App\Http\Resources;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Project $resource
 */
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'description' => $this->resource->description,
            'visibility' => $this->resource->visibility,
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
            'deleted_at' => $this->resource->deleted_at?->toISOString(),
            'workspace' => $this->whenLoaded('workspace', fn() => [
                'id' => $this->resource->workspace->id,
                'name' => $this->resource->workspace->name,
            ]),
            'creator' => $this->whenLoaded('creator', fn() => [
                'id' => $this->resource->creator->id,
                'name' => $this->resource->creator->name,
            ]),
            'tasks_count' => $this->whenCounted('tasks'), // jika pakai withCount
        ];
    }
}
