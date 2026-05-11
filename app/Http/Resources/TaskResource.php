<?php

namespace App\Http\Resources;

use App\Models\Tasks\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Task $resource
 * */
class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'description' => $this->resource->description,
            'status' => $this->resource->status,
            'priority' => $this->resource->priority,
            'start_date' => $this->resource->start_date?->toDateString(),
            'due_date' => $this->resource->due_date?->toDateString(),
            'time_estimate' => $this->resource->time_estimate,
            'time_spent' => $this->resource->time_spent,
            'time_remaining' => $this->resource->time_estimate - $this->resource->time_spent,
            'order_column' => $this->resource->order_column,
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
            'deleted_at' => $this->resource->deleted_at?->toISOString(),
            'assignee' => $this->whenLoaded('assignee', fn() => [
                'id' => $this->resource->assignee->id,
                'name' => $this->resource->assignee->name,
                'email' => $this->resource->assignee->email,
            ]),
            'creator' => $this->whenLoaded('creator', fn() => [
                'id' => $this->resource->creator->id,
                'name' => $this->resource->creator->name,
            ]),
            'project' => $this->whenLoaded('project', fn() => [
                'id' => $this->resource->project->id,
                'name' => $this->resource->project->name,
            ]),
//            'tags' => TagResource::collection($this->whenLoaded('tags')),
//            'assignees' => UserSimpleResource::collection($this->whenLoaded('assignees')),
//            'subtasks' => SubTaskResource::collection($this->whenLoaded('subtasks')),
        ];
    }
}
