<?php

namespace App\Enums;

enum Table: string
{
    case WORKSPACES = 'workspaces';
    case PROJECTS = 'projects';
    case TASKS = 'tasks';
    case SUB_TASKS = 'sub_tasks';
    case TAGS = 'tags';
    case TASK_TAGS = 'task_tags';
    case TASK_USERS = 'task_users';

    /**
     * @return string
     */
    public static function connection(): string
    {
        return 'public';
    }

    /**
     * @param string|null $connection
     * @return string
     */
    public function tableName(?string $connection = null): string
    {
        if (empty($connection)) {
            $connection = static::connection();
        }
        return $connection . '.' . $this->value;
    }
}
