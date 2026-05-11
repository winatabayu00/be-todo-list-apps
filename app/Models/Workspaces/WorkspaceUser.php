<?php

namespace App\Models\Workspaces;

use App\Enums\Table;
use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $workspace_id
 * @property string $user_id
 * @property Workspace $workspace
 * @property User $user
 */
class WorkspaceUser extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Table::WORKSPACE_USERS->tableName();
    }

    protected $fillable = [
        'workspace_id',
        'user_id',
    ];

    public $timestamps = false;

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
