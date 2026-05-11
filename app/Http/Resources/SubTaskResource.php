<?php

namespace App\Http\Resources\Tasks;

use App\Models\Tasks\SubTask;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property SubTask $resource
 */
class SubTaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'is_completed' => $this->resource->is_completed,
            'task_id' => $this->resource->task_id,
            'assignee_id' => $this->resource->assignee_id,
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
            'deleted_at' => $this->resource->deleted_at?->toISOString(),
            'task' => $this->whenLoaded('task', fn() => [
                'id' => $this->resource->task->id,
                'title' => $this->resource->task->title,
            ]),
            'assignee' => $this->whenLoaded('assignee', fn() => [
                'id' => $this->resource->assignee->id,
                'name' => $this->resource->assignee->name,
            ]),
        ];
    }
}
