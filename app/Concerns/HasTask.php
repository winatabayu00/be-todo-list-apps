<?php

namespace App\Concerns;

use App\Models\Tasks\Task;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property Task $task
 * */
trait HasTask
{
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
