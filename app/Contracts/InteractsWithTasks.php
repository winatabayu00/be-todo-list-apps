<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface InteractsWithTasks
{
    public function task(): BelongsTo;
}
