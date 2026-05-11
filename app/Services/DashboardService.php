<?php

namespace App\Services;

use App\Enums\TasksPriority;
use App\Enums\TasksStatus;
use App\Models\Tasks\Task;
use App\Models\Workspaces\Workspace;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get task statistics by status for a specific project (or all accessible projects).
     */
    public function taskStatusStats(?string $projectId = null): array
    {
        $query = Task::query();

        if ($projectId) {
            $query->where('project_id', $projectId);
        } else {
            // Filter by accessible projects (through workspace membership)
            $accessibleProjectIds = $this->getAccessibleProjectIds();
            $query->whereIn('project_id', $accessibleProjectIds);
        }

        $stats = [];
        foreach (TasksStatus::values() as $status) {
            $stats[$status] = (clone $query)->where('status', $status)->count();
        }

        return $stats;
    }

    /**
     * Get task statistics by priority.
     */
    public function taskPriorityStats(?string $projectId = null): array
    {
        $query = Task::query();

        if ($projectId) {
            $query->where('project_id', $projectId);
        } else {
            $ids = $this->getAccessibleProjectIds();
            $query->whereIn('project_id', $ids);
        }

        $stats = [];
        foreach (TasksPriority::values() as $priority) {
            $stats[$priority] = (clone $query)->where('priority', $priority)->count();
        }

        return $stats;
    }

    /**
     * Get upcoming tasks (due in next 7 days).
     */
    public function upcomingTasks(?string $projectId = null, int $days = 7): Collection
    {
        $query = Task::with(['project', 'assignee'])
            ->where('due_date', '>=', Carbon::today())
            ->where('due_date', '<=', Carbon::today()->addDays($days))
            ->where('status', '!=', TasksStatus::DONE->value);

        if ($projectId) {
            $query->where('project_id', $projectId);
        } else {
            $ids = $this->getAccessibleProjectIds();
            $query->whereIn('project_id', $ids);
        }

        return $query->orderBy('due_date')->limit(10)->get();
    }

    /**
     * Get overdue tasks.
     */
    public function overdueTasks(?string $projectId = null): Collection
    {
        $query = Task::with(['project', 'assignee'])
            ->where('due_date', '<', Carbon::today())
            ->where('status', '!=', TasksStatus::DONE->value);

        if ($projectId) {
            $query->where('project_id', $projectId);
        } else {
            $ids = $this->getAccessibleProjectIds();
            $query->whereIn('project_id', $ids);
        }

        return $query->orderBy('due_date')->limit(10)->get();
    }

    /**
     * Get recent tasks (last 7 days created).
     */
    public function recentTasks(?string $projectId = null, int $days = 7): Collection
    {
        $query = Task::with(['project', 'assignee', 'creator'])
            ->where('created_at', '>=', Carbon::now()->subDays($days));

        if ($projectId) {
            $query->where('project_id', $projectId);
        } else {
            $ids = $this->getAccessibleProjectIds();
            $query->whereIn('project_id', $ids);
        }

        return $query->orderByDesc('created_at')->limit(10)->get();
    }

    /**
     * Get tasks assigned to current user.
     */
    public function myTasks(string $userId, ?string $status = null): Collection
    {
        $query = Task::with(['project'])
            ->where('assignee_id', $userId)
            ->orderBy('due_date');

        if ($status && in_array($status, TasksStatus::values())) {
            $query->where('status', $status);
        }

        return $query->limit(20)->get();
    }

    /**
     * Get workspace summary (number of projects, tasks, members).
     */
    public function workspaceSummary(string $workspaceId): array
    {
        $workspace = Workspace::withTrashed()->findOrFail($workspaceId);

        $projectCount = $workspace->projects()->count();
        $taskCount = Task::whereIn('project_id', $workspace->projects()->pluck('id'))->count();
        $memberCount = $workspace->members()->count();

        return [
            'id' => $workspace->id,
            'name' => $workspace->name,
            'project_count' => $projectCount,
            'task_count' => $taskCount,
            'member_count' => $memberCount,
        ];
    }

    /**
     * Get overall summary for the user (across all accessible workspaces/projects).
     */
    public function overallSummary(string $userId): array
    {
        $accessibleProjectIds = $this->getAccessibleProjectIds();

        $totalTasks = Task::whereIn('project_id', $accessibleProjectIds)->count();
        $completedTasks = Task::whereIn('project_id', $accessibleProjectIds)
            ->where('status', TasksStatus::DONE->value)->count();
        $inProgressTasks = Task::whereIn('project_id', $accessibleProjectIds)
            ->where('status', TasksStatus::IN_PROGRESS->value)->count();
        $overdueTasks = Task::whereIn('project_id', $accessibleProjectIds)
            ->where('due_date', '<', Carbon::today())
            ->where('status', '!=', TasksStatus::DONE->value)->count();

        // Sum of time spent this week
        $timeSpentThisWeek = DB::table('task_time_logs')
            ->whereIn('task_id', function ($q) use ($accessibleProjectIds) {
                $q->select('id')->from('tasks')->whereIn('project_id', $accessibleProjectIds);
            })
            ->where('created_at', '>=', Carbon::now()->startOfWeek())
            ->sum('minutes');

        return [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'in_progress_tasks' => $inProgressTasks,
            'overdue_tasks' => $overdueTasks,
            'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0,
            'time_spent_this_week_minutes' => $timeSpentThisWeek,
            'time_spent_this_week_hours' => round($timeSpentThisWeek / 60, 1),
        ];
    }

    /**
     * Helper: get IDs of projects the user can access (through workspace membership).
     */
    protected function getAccessibleProjectIds(): array
    {
        $user = auth()->user();
        if (!$user) {
            return [];
        }

        // User can access projects from workspaces where they are member or owner
        $workspaceIds = Workspace::where('owner_id', $user->id)
            ->orWhereHas('members', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->pluck('id');

        if ($workspaceIds->isEmpty()) {
            return [];
        }

        return \App\Models\Project::whereIn('workspace_id', $workspaceIds)->pluck('id')->toArray();
    }
}
