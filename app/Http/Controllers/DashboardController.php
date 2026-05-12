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
     * Authenticated endpoint. Returns aggregated counts for tasks scoped to the
     * authenticated user's accessible workspaces. Optionally scope to a single project.
     *
     * @authenticated
     * @queryParam project_id string optional UUID of the project to filter statistics. Example: "3fa85f64-5717-4562-b3fc-2c963f66afa6"
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "by_status": {
     *       "todo": 5,
     *       "in_progress": 3,
     *       "in_review": 2,
     *       "done": 10
     *     },
     *     "by_priority": {
     *       "urgent": 2,
     *       "high": 5,
     *       "normal": 8,
     *       "low": 5
     *     }
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
     * Authenticated endpoint. Returns a collection of tasks ordered by due date
     * ascending. Each task is returned using the `TaskResource` shape.
     *
     * @authenticated
     * @queryParam project_id string optional UUID of the project to filter tasks. Example: "3fa85f64-5717-4562-b3fc-2c963f66afa6"
     * @queryParam days integer optional Number of days to look ahead. Default: 7. Example: 14
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": "uuid",
     *       "title": "Implement rate limiting",
     *       "description": "Use Laravel throttle middleware",
     *       "status": "in_progress",
     *       "priority": "high",
     *       "start_date": "2025-05-01",
     *       "due_date": "2025-05-20",
     *       "time_estimate": 120,
     *       "time_spent": 30,
     *       "time_remaining": 90,
     *       "order_column": 10,
     *       "created_at": "2025-04-01T12:00:00Z",
     *       "updated_at": "2025-04-05T12:00:00Z",
     *       "assignee": {"id":"user-uuid","name":"John Doe","email":"john@example.com"},
     *       "project": {"id":"project-uuid","name":"Backend API"},
     *       "tags": [],
     *       "subtasks": []
     *     }
     *   ]
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
     * Authenticated endpoint. Returns tasks whose `due_date` is strictly before
     * the current date. Optionally filter by project.
     *
     * @authenticated
     * @queryParam project_id string optional UUID of the project to filter tasks. Example: "3fa85f64-5717-4562-b3fc-2c963f66afa6"
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": "uuid",
     *       "title": "Fix payment bug",
     *       "due_date": "2025-05-10",
     *       "status": "todo",
     *       "priority": "urgent",
     *       "project": {"id":"project-uuid","name":"Payments"},
     *       "assignee": {"id":"user-uuid","name":"Jane Doe","email":"jane@example.com"}
     *     }
     *   ]
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
     * Authenticated endpoint. Returns tasks created within the last `days` days.
     * Optionally filter by project.
     *
     * @authenticated
     * @queryParam project_id string optional UUID of the project to filter tasks. Example: "3fa85f64-5717-4562-b3fc-2c963f66afa6"
     * @queryParam days integer optional Number of days to look back. Default: 7. Example: 30
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": "uuid",
     *       "title": "Add logging",
     *       "created_at": "2025-04-28T09:00:00Z",
     *       "status": "todo",
     *       "priority": "normal",
     *       "project": {"id":"project-uuid","name":"Backend API"}
     *     }
     *   ]
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
     * Authenticated endpoint. Returns tasks where the authenticated user is the
     * `assignee`. Optionally filter by `status`.
     *
     * @authenticated
     * @queryParam status string optional Filter by task status. One of: todo, in_progress, in_review, done. Example: "in_progress"
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": "uuid",
     *       "title": "Code review",
     *       "status": "in_progress",
     *       "priority": "high",
     *       "project": {"id":"project-uuid","name":"Backend API"},
     *       "time_estimate": 60,
     *       "time_spent": 15
     *     }
     *   ]
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
     * Authenticated endpoint. Returns counts scoped to the provided workspace
     * UUID. The authenticated user must have access to the workspace.
     *
     * @authenticated
     * @urlParam workspaceId string required UUID of the workspace. Example: "ws-3fa85f64-5717-4562-b3fc-2c963f66afa6"
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
     * Authenticated endpoint. Aggregates counts and time metrics across all
     * workspaces the user can access.
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
