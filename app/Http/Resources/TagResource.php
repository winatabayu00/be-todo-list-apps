<?php

namespace App\Http\Resources;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Tag $resource
 */
class TagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'color' => $this->resource->color,
            'workspace_id' => $this->resource->workspace_id,
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
            'deleted_at' => $this->resource->deleted_at?->toISOString(),
            'workspace' => $this->whenLoaded('workspace', fn() => [
                'id' => $this->resource->workspace->id,
                'name' => $this->resource->workspace->name,
            ]),
        ];
    }
}
