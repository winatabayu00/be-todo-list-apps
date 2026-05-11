<?php

namespace App\Models\Tasks;

use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $task_id
 * @property string $user_id
 * @property int $minutes
 * @property string $description
 * @property Task $task
 * @property User $user
 */
class TaskTimeLog extends Model
{
    use HasUuids;

    protected $table = 'task_time_logs';
    protected $fillable = [
        'task_id',
        'user_id',
        'minutes',
        'description',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
