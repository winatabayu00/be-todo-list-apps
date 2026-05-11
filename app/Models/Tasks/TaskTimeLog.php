<?php

namespace App\Models\Tasks;

use App\Concerns\HasTask;
use App\Contracts\InteractsWithTasks;
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
 * @property User $user
 */
class TaskTimeLog extends Model implements InteractsWithTasks
{
    use HasUuids, HasTask;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
