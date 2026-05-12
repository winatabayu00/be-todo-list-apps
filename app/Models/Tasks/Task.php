<?php

namespace App\Models\Tasks;

use App\Enums\Table;
use App\Models\Model;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $title
 * @property string $description
 * @property string $status
 * @property string $priority
 * @property Carbon $start_date
 * @property Carbon $due_date
 * @property string $assignee_id
 * @property string $project_id
 * @property string $created_by
 * @property int $time_estimate
 * @property int $time_spent
 * @property int $order_column
 * @property Project $project
 * @property User $assignee
 * @property User $creator
 * @property Collection<SubTask> $subtasks
 * @property Collection<Tag> $tags
 * @property Collection<User> $assignees
 * @property Collection<TaskTimeLog> $timeLogs
 */
class Task extends Model
{
    use HasUuids, SoftDeletes;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Table::TASKS->tableName();
    }

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'start_date',
        'due_date',
        'assignee_id',
        'project_id',
        'created_by',
        'time_estimate',
        'time_spent',
        'order_column',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date'   => 'date',
        'time_estimate' => 'integer',
        'time_spent'    => 'integer',
        'order_column'  => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(SubTask::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, Table::TASK_TAGS->tableName());
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, Table::TASK_USERS->tableName());
    }

    // Scopes
    public function scopeOfStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOfPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(TaskTimeLog::class, 'task_id');
    }

    // Boot events untuk logging
    protected static function booted()
    {
        static::created(function ($task) {
            TaskLog::create([
                'task_id'   => $task->id,
                'user_id'   => auth()->id(),
                'action'    => 'created',
                'changes'   => json_encode($task->toArray()),
            ]);
        });

        static::updated(function ($task) {
            TaskLog::create([
                'task_id'   => $task->id,
                'user_id'   => auth()->id(),
                'action'    => 'updated',
                'changes'   => json_encode($task->getChanges()),
            ]);
        });
    }
}
