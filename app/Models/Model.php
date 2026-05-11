<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Winata\PackageBased\Database\Models\Model as BaseModel;


/**
 * @property Carbon|\DateTimeInterface $created_at
 * @property Carbon|\DateTimeInterface $updated_at
 * @property Carbon|\DateTimeInterface $deleted_at
 * */
abstract class Model extends BaseModel
{

    public function getSearchableColumns(): array
    {
        return [];
    }

    public function getOrderableColumns(): array
    {
        $defaultOrderableColumns = [];
        if (Schema::hasColumn($this->table, 'created_at')) {
            $defaultOrderableColumns = [
                'created_at'
            ];
        }
        return $defaultOrderableColumns;
    }
}
