<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskTimeLogResource;
use App\Models\Tasks\Task;
use App\Services\TaskService;
use Dentro\Yalr\Attributes;
use Illuminate\Container\EntryNotFoundException;
use Illuminate\Contracts\Container\CircularDependencyException;
use Illuminate\Http\Request;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Winata\Core\Response\Http\Response;

#[Attributes\Prefix('tasks/{task}/time')]
class TimeTrackingController extends Controller
{
    /**
     * Log time spent on a specific task.
     *
     * @authenticated
     *
     * @urlParam task string required Task ID (UUID). Example: "abc-123"
     * @bodyParam minutes int required Time spent in minutes (1-1440). Example: 45
     * @bodyParam description string optional Description of the work done. Example: "Fixed authentication bug"
     *
     * @response 201 {
     *   "success": true,
     *   "data": {
     *     "id": "uuid",
     *     "minutes": 45,
     *     "description": "Fixed authentication bug",
     *     "created_at": "2025-05-12T10:30:00Z",
     *     "user": {
     *       "id": "user-id",
     *       "name": "John Doe",
     *       "email": "john@example.com"
     *     }
     *   }
     * }
     * @response 422 {"success": false, "message": "Validation error", "errors": {...}}
     * @response 401 {"success": false, "message": "Unauthenticated."}
     */
    #[Attributes\Post('log')]
    public function log(Request $request, Task $task, TaskService $service): Response
    {
        $user = auth()->user();
        $timeLog = $service->logTime($task, $user, $request->input());

        return $this->response(TaskTimeLogResource::make($timeLog));
    }

    /**
     * Get paginated list of time logs for a task with optional filters.
     *
     * @authenticated
     *
     * @urlParam task string required Task ID (UUID). Example: "abc-123"
     * @queryParam date_from date optional Filter logs created after this date (Y-m-d). Example: "2025-05-01"
     * @queryParam date_to date optional Filter logs created before this date (Y-m-d). Example: "2025-05-31"
     * @queryParam user_id string optional Filter logs by user ID (UUID). Example: "user-123"
     * @queryParam per_page int optional Items per page (default 15, max 100).
     *
     * @response {
     *   "success": true,
     *   "data": TaskTimeLogResource[],
     *   "meta": {
     *     "current_page": 1,
     *     "per_page": 15,
     *     "total": 10,
     *     "last_page": 1
     *   }
     * }
     */
    #[Attributes\Get('logs')]
    public function logs(Request $request, Task $task, TaskService $service): Response
    {
        $filters = $request->only(['date_from', 'date_to', 'user_id']);
        $logs = $service->getTimeLogs($task, $filters);

        return $this->response(TaskTimeLogResource::collection($logs));
    }

    /**
     * Get total time spent on a task.
     *
     * @authenticated
     *
     * @urlParam task string required Task ID (UUID). Example: "abc-123"
     *
     * @response {
     *   "success": true,
     *   "data": {
     *     "total_minutes": 120,
     *     "total_hours": 2.0
     *   }
     * }
     */
    #[Attributes\Get('total')]
    public function total(Task $task, TaskService $service): Response
    {
        $total = $service->getTotalTimeSpent($task);
        return $this->response([
            'total_minutes' => $total,
            'total_hours' => round($total / 60, 2),
        ]);
    }

    /**
     * Update time estimate for a task.
     *
     * @authenticated
     *
     * @urlParam task string required Task ID (UUID). Example: "abc-123"
     * @bodyParam minutes int required Estimated time in minutes. Example: 180
     *
     * @response {
     *   "success": true,
     *   "data": {
     *     "time_estimate": 180,
     *     "time_estimate_hours": 3.0
     *   }
     * }
     * @response 422 {"success": false, "message": "The minutes field is required."}
     */
    #[Attributes\Put('estimate')]
    public function updateEstimate(Request $request, Task $task, TaskService $service): Response
    {
        $request->validate([
            'minutes' => 'required|integer|min:0',
        ]);
        $user = auth()->user();
        $task = $service->updateTimeEstimate($task, $user, $request->minutes);

        return $this->response([
            'time_estimate' => $task->time_estimate,
            'time_estimate_hours' => round($task->time_estimate / 60, 2),
        ]);
    }
}
