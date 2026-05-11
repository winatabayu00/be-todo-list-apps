<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Services\DashboardService;
use Dentro\Yalr\Attributes;
use Illuminate\Http\Request;
use Winata\Core\Response\Http\Response;

#[Attributes\Prefix('dashboard')]
class DashboardController extends Controller
{
    /**
     * Get task statistics grouped by status and priority.
     *
     * @authenticated
     *
     * @queryParam project_id string optional Filter by project ID.
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "by_status": {"todo": 5, "in_progress": 3, "in_review": 2, "done": 10},
     *     "by_priority": {"urgent": 2, "high": 5, "normal": 8, "low": 5}
     *   }
     * }
     */
    #[Attributes\Get('stats')]
    public function stats(Request $request, DashboardService $service): Response
    {
        $projectId = $request->get('project_id');
        $stats = [
            'by_status' => $service->taskStatusStats($projectId),
            'by_priority' => $service->taskPriorityStats($projectId),
        ];
        return $this->response($stats);
    }

    /**
     * Get upcoming tasks due in the next X days (default 7).
     *
     * @authenticated
     *
     * @queryParam project_id string optional Filter by project ID.
     * @queryParam days integer optional Number of days to look ahead. Default 7.
     *
     * @response 200 {
     *   "success": true,
     *   "data": [{"id":"uuid","title":"...","due_date":"2025-05-20",...}]
     * }
     */
    #[Attributes\Get('upcoming-tasks')]
    public function upcomingTasks(Request $request, DashboardService $service): Response
    {
        $tasks = $service->upcomingTasks(
            $request->get('project_id'),
            $request->get('days', 7)
        );
        return $this->response(TaskResource::collection($tasks));
    }

    /**
     * Get overdue tasks (due date before today).
     *
     * @authenticated
     *
     * @queryParam project_id string optional Filter by project ID.
     *
     * @response 200 {
     *   "success": true,
     *   "data": [{"id":"uuid","title":"...","due_date":"2025-05-10",...}]
     * }
     */
    #[Attributes\Get('overdue-tasks')]
    public function overdueTasks(Request $request, DashboardService $service): Response
    {
        $tasks = $service->overdueTasks($request->get('project_id'));
        return $this->response(TaskResource::collection($tasks));
    }

    /**
     * Get recently created tasks (last X days, default 7).
     *
     * @authenticated
     *
     * @queryParam project_id string optional Filter by project ID.
     * @queryParam days integer optional Number of days to look back. Default 7.
     *
     * @response 200 {
     *   "success": true,
     *   "data": [{"id":"uuid","title":"...","created_at":"...",...}]
     * }
     */
    #[Attributes\Get('recent-tasks')]
    public function recentTasks(Request $request, DashboardService $service): Response
    {
        $tasks = $service->recentTasks(
            $request->get('project_id'),
            $request->get('days', 7)
        );
        return $this->response(TaskResource::collection($tasks));
    }

    /**
     * Get tasks assigned to the authenticated user.
     *
     * @authenticated
     *
     * @queryParam status string optional Filter by status (todo, in_progress, in_review, done).
     *
     * @response 200 {
     *   "success": true,
     *   "data": [{"id":"uuid","title":"...","status":"in_progress",...}]
     * }
     */
    #[Attributes\Get('my-tasks')]
    public function myTasks(Request $request, DashboardService $service): Response
    {
        $user = auth()->user();
        $tasks = $service->myTasks($user->id, $request->get('status'));
        return $this->response(TaskResource::collection($tasks));
    }

    /**
     * Get summary of a specific workspace (project, task, member counts).
     *
     * @authenticated
     *
     * @urlParam workspaceId string required The UUID of the workspace.
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": "uuid",
     *     "name": "My Workspace",
     *     "project_count": 5,
     *     "task_count": 67,
     *     "member_count": 8
     *   }
     * }
     */
    #[Attributes\Get('workspace/{workspaceId}/summary')]
    public function workspaceSummary(string $workspaceId, DashboardService $service): Response
    {
        $summary = $service->workspaceSummary($workspaceId);
        return $this->response($summary);
    }

    /**
     * Get overall summary for the authenticated user across all accessible workspaces.
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "total_tasks": 45,
     *     "completed_tasks": 12,
     *     "in_progress_tasks": 8,
     *     "overdue_tasks": 3,
     *     "completion_rate": 26.7,
     *     "time_spent_this_week_minutes": 380,
     *     "time_spent_this_week_hours": 6.3
     *   }
     * }
     */
    #[Attributes\Get('overall-summary')]
    public function overallSummary(DashboardService $service): Response
    {
        $user = auth()->user();
        $summary = $service->overallSummary($user->id);
        return $this->response($summary);
    }
}
