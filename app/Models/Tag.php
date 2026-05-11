<?php

namespace App\Models;

use App\Enums\Table;
use App\Models\Tasks\Task;
use App\Models\Workspaces\Workspace;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $name
 * @property string $color
 * @property string $workspace_id
 * @property Workspace $workspace
 * @property Collection<Task> $tasks
 */
class Tag extends Model
{
    use HasUuids, SoftDeletes;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Table::TAGS->tableName();
    }

    protected $fillable = [
        'name',
        'color',
        'workspace_id',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_tag');
    }
}
