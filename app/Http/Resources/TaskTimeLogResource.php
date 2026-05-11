<?php

namespace App\Http\Resources;

use App\Models\Tasks\TaskTimeLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property TaskTimeLog $resource
 */
class TaskTimeLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'minutes' => $this->resource->minutes,
            'description' => $this->resource->description,
            'created_at' => $this->resource->created_at?->toISOString(),
            'user' => $this->whenLoaded('user', fn() => [
                'id' => $this->resource->user->id,
                'name' => $this->resource->user->name,
                'email' => $this->resource->user->email,
            ]),
        ];
    }
}
