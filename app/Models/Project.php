<?php

namespace App\Models;

use App\Enums\Table;
use App\Models\Tasks\Task;
use App\Models\Workspaces\Workspace;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $name
 * @property string $description
 * @property string $visibility
 * @property string $workspace_id
 * @property string $created_by
 * @property Workspace $workspace
 * @property User $creator
 * @property Collection<Task> $tasks
 */
class Project extends Model
{
    use HasUuids, SoftDeletes;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Table::PROJECTS->tableName();
    }

    protected $fillable = [
        'name',
        'description',
        'visibility',
        'workspace_id',
        'created_by',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
