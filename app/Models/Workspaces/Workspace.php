<?php

namespace App\Models\Workspaces;

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
use Illuminate\Database\Eloquent\SoftDeletes;

// asumsi User extends Authenticatable

/**
 * @property string $id
 * @property string $name
 * @property string $description
 * @property string $owner_id
 * @property User $owner
 * @property Collection<Project> $projects
 * @property Collection<User> $members
 * @property Collection<Tag> $tags
 */
class Workspace extends Model
{
    use HasUuids, SoftDeletes;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Table::WORKSPACES->tableName();
    }

    protected $fillable = [
        'name',
        'description',
        'owner_id',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_user');
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }
}
