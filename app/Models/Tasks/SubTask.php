<?php

namespace App\Models\Tasks;

use App\Concerns\HasTask;
use App\Contracts\InteractsWithTasks;
use App\Enums\Table;
use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $title
 * @property bool $is_completed
 * @property string $assignee_id
 * @property Task $task
 * @property User $assignee
 */
class SubTask extends Model implements InteractsWithTasks
{
    use HasUuids, HasTask;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Table::SUB_TASKS->tableName();
    }

    protected $fillable = [
        'title',
        'is_completed',
        'task_id',
        'assignee_id',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
    ];


    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }
}
