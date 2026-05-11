<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Models\Tasks\Task;
use App\Queries\TaskQuery;
use App\Services\TaskService;
use Dentro\Yalr\Attributes;
use Illuminate\Http\Request;
use Winata\Core\Response\Http\Response;

#[Attributes\Prefix('tasks')]
class TaskController extends Controller
{
    /**
     * Get paginated list of tasks with filters and search.
     *
     * @authenticated
     *
     * @queryParam filter[project_id] string optional Filter by project ID. Example: "abc-123"
     * @queryParam filter[status] string optional Comma-separated statuses (todo,in_progress,in_review,done). Example: "todo,in_progress"
     * @queryParam filter[priority] string optional Comma-separated priorities (urgent,high,normal,low). Example: "high,normal"
     * @queryParam filter[assignee_id] string optional Filter by assignee user ID. Example: "user-1"
     * @queryParam filter[tags] string optional Comma-separated tag IDs. Example: "tag1,tag2"
     * @queryParam search string optional Search in title or description.
     * @queryParam sort[field] string optional Sort field (created_at, due_date, priority, order_column). Example: "due_date"
     * @queryParam order string optional Sort direction (asc,desc). Default "desc".
     * @queryParam include string optional Comma-separated relations (project,assignee,tags,assignees,subtasks,creator).
     * @queryParam per_page integer optional Items per page. Default 15, max 100.
     *
     * @response 200 {
     *   "success": true,
     *   "data": [{"id":"uuid","title":"...",...}],
     *   "meta": {"current_page":1,"per_page":15,"total":100,"last_page":7}
     * }
     * @response 401 {"message": "Unauthenticated."}
     */
    #[Attributes\Get('')]
    public function index(): Response
    {
        $query = TaskQuery::filterColumn()
            ->orderColumn()
            ->getAllDataPaginated();

        return $this->response(TaskResource::collection($query));
    }

    /**
     * Create a new task.
     *
     * @authenticated
     *
     * @bodyParam title string required Task title. Example: "Finish documentation"
     * @bodyParam description string optional Description. Example: "Write API docs for Scramble"
     * @bodyParam status string optional Status: todo, in_progress, in_review, done. Default "todo"
     * @bodyParam priority string optional Priority: urgent, high, normal, low. Default "normal"
     * @bodyParam start_date date optional Start date (Y-m-d). Example: "2025-05-01"
     * @bodyParam due_date date optional Due date (Y-m-d). Must be after start_date.
     * @bodyParam assignee_id string optional User ID (UUID) to assign this task.
     * @bodyParam project_id string required Project ID (UUID).
     * @bodyParam time_estimate integer optional Estimated time in minutes. Example: 120
     * @bodyParam order_column integer optional Order for sorting. Example: 1
     * @bodyParam tags array optional Array of tag IDs (UUID).
     * @bodyParam assignees array optional Array of user IDs (UUID) for multiple assignees.
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Task created successfully.",
     *   "data": {"id":"uuid","title":"...",...}
     * }
     * @response 422 {"success": false, "message": "The title field is required.", "errors": {...}}
     * @response 401 {"message": "Unauthenticated."}
     */
    #[Attributes\Post('create')]
    public function create(Request $request, TaskService $service): Response
    {
        $user = auth()->user();
        $task = $service->create(user: $user, data: $request->input());

        return $this->response(TaskResource::make($task));
    }

    /**
     * Get details of a specific task.
     *
     * @authenticated
     *
     * @urlParam task string required The UUID of the task. Example: "abc-123"
     *
     * @response 200 {
     *   "success": true,
     *   "data": {"id":"uuid","title":"...","description":"...",...}
     * }
     * @response 404 {"success": false, "message": "Task not found."}
     * @response 401 {"message": "Unauthenticated."}
     */
    #[Attributes\get('{task}/detail')]
    public function show(Task $task): Response
    {
        return $this->response(TaskResource::make($task));
    }

    /**
     * Update an existing task.
     *
     * @authenticated
     *
     * @urlParam task string required The UUID of the task. Example: "abc-123"
     * @bodyParam title string optional New title.
     * @bodyParam description string optional New description.
     * @bodyParam status string optional New status: todo, in_progress, in_review, done.
     * @bodyParam priority string optional New priority: urgent, high, normal, low.
     * @bodyParam start_date date optional New start date.
     * @bodyParam due_date date optional New due date.
     * @bodyParam assignee_id string optional New assignee user ID.
     * @bodyParam project_id string optional New project ID.
     * @bodyParam time_estimate integer optional New estimated time.
     * @bodyParam order_column integer optional New order.
     * @bodyParam tags array optional Array of tag IDs to sync.
     * @bodyParam assignees array optional Array of user IDs for multiple assignees.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Task updated successfully.",
     *   "data": {"id":"uuid","title":"...",...}
     * }
     * @response 404 {"success": false, "message": "Task not found."}
     * @response 422 {"success": false, "message": "Validation error.", "errors": {...}}
     */
    #[Attributes\Put('{task}/update')]
    public function update(Request $request, Task $task, TaskService $service): Response
    {
        $user = auth()->user();
        $updatedTask = $service->update(task: $task, user: $user, inputs: $request->input());
        return $this->response(TaskResource::make($updatedTask));
    }

    /**
     * Soft delete a task.
     *
     * @authenticated
     *
     * @urlParam task string required The UUID of the task. Example: "abc-123"
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Task deleted successfully."
     * }
     * @response 404 {"success": false, "message": "Task not found."}
     */
    #[Attributes\Delete('{task}/delete')]
    public function destroy(Task $task, TaskService $service): Response
    {
        $user = auth()->user();
        $service->delete(task: $task, user: $user);
        return $this->response();
    }

    /**
     * Restore a soft-deleted task.
     *
     * @authenticated
     *
     * @urlParam task string required The UUID of the task. Example: "abc-123"
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Task restored successfully.",
     *   "data": {"id":"uuid","title":"...",...}
     * }
     * @response 404 {"success": false, "message": "Task not found."}
     */
    #[Attributes\Patch('{task}/restore')]
    public function restore(string $taskId, TaskService $service): Response
    {
        $user = auth()->user();
        $task = $service->restore(taskId: $taskId, user: $user);
        return $this->response(TaskResource::make($task));
    }
}
