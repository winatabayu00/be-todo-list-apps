<?php

namespace App\Models\Tasks;

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
 * @property string $task_id
 * @property string $assignee_id
 * @property Task $task
 * @property User $assignee
 */
class SubTask extends Model
{
    use HasUuids, SoftDeletes;

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

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }
}
