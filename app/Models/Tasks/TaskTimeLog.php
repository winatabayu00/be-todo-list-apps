<?php

namespace App\Models\Tasks;

use App\Enums\Table;
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

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Table::TASK_TIME_LOGS->tableName();
    }
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
