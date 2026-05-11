<?php

namespace App\Enums;

enum Table: string
{
    case USERS = 'users';

    #CORE TABLES
    case WORKSPACES = 'workspaces';
    case WORKSPACE_USERS = 'workspace_users';
    case PROJECTS = 'projects';
    case TAGS = 'tags';

    #TASK TABLES
    case TASKS = 'tasks';
    case SUB_TASKS = 'sub_tasks';
    case TASK_TAGS = 'task_tags';
    case TASK_USERS = 'task_users';
    case TASK_LOGS = 'task_logs';
    case TASK_TIME_LOGS = 'task_time_logs';

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
