<?php

namespace App\Http\Controllers;

use App\Http\Resources\SubTaskResource;
use App\Models\Tasks\SubTask;
use App\Queries\SubTaskQuery;
use App\Services\SubTaskService;
use Dentro\Yalr\Attributes;
use Illuminate\Http\Request;
use Winata\Core\Response\Http\Response;

#[Attributes\Prefix('subtasks')]
class SubTaskController extends Controller
{
    /**
     * List all subtasks with optional filters.
     *
     * @authenticated
     *
     * @queryParam search string optional Search by title. Example: "fix"
     * @queryParam filter[task_id] string optional Filter by parent task ID (UUID). Example: "task-123"
     * @queryParam filter[assignee_id] string optional Filter by assignee user ID (UUID).
     * @queryParam filter[is_completed] boolean optional Filter by completion status: true/false.
     * @queryParam filter[trashed] string optional Filter soft-deleted: only, with.
     * @queryParam sort[field] string optional Sort field: created_at, updated_at, title, is_completed.
     * @queryParam order string optional asc/desc (default desc).
     * @queryParam include string optional Eager load relations: task, assignee.
     * @queryParam per_page integer optional Items per page (default 15, max 100).
     *
     * @response array{success: bool, data: SubTaskResource[], meta: array}
     */
    #[Attributes\Get('')]
    public function index(): Response
    {
        $query = SubTaskQuery::filterColumn()
            ->orderColumn()
            ->getAllDataPaginated();

        return $this->response(SubTaskResource::collection($query));
    }

    /**
     * Create a new subtask.
     *
     * @authenticated
     *
     * @bodyParam title string required Subtask title. Example: "Write documentation"
     * @bodyParam task_id string required Parent task ID (UUID). Example: "task-123"
     * @bodyParam assignee_id string optional Assignee user ID (UUID).
     * @bodyParam is_completed boolean optional Initial completion status (default false).
     *
     * @response 201 {"success": true, "data": {"id": "...", "title": "...", ...}}
     */
    #[Attributes\Post('create')]
    public function create(Request $request, SubTaskService $service): Response
    {
        $user = auth()->user();
        $subTask = $service->create($user, $request->input());

        return $this->response(SubTaskResource::make($subTask));
    }

    /**
     * Get subtask detail by ID.
     *
     * @authenticated
     *
     * @urlParam subTask string required Subtask ID (UUID). Example: "subtask-123"
     *
     * @response {"success": true, "data": {"id": "...", "title": "...", "task": {...}, "assignee": {...}}}
     */
    #[Attributes\Get('{subTask}/detail')]
    public function show(SubTask $subTask): Response
    {
        $subTask->load(['task', 'assignee']);
        return $this->response(SubTaskResource::make($subTask));
    }

    /**
     * Update an existing subtask.
     *
     * @authenticated
     *
     * @urlParam subTask string required Subtask ID (UUID).
     * @bodyParam title string optional New title.
     * @bodyParam assignee_id string optional New assignee ID.
     * @bodyParam is_completed boolean optional New completion status.
     *
     * @response {"success": true, "data": {...}}
     */
    #[Attributes\Put('{subTask}/update')]
    public function update(Request $request, SubTask $subTask, SubTaskService $service): Response
    {
        $subTask = $service->update($subTask, $request->input());
        return $this->response(SubTaskResource::make($subTask));
    }

    /**
     * Soft delete a subtask.
     *
     * @authenticated
     *
     * @urlParam subTask string required Subtask ID (UUID).
     *
     * @response {"success": true, "message": "Subtask deleted"}
     */
    #[Attributes\Delete('{subTask}/delete')]
    public function destroy(SubTask $subTask, SubTaskService $service): Response
    {
        $service->delete($subTask);
        return $this->response(['message' => 'Subtask deleted']);
    }

    /**
     * Restore a soft-deleted subtask.
     *
     * @authenticated
     *
     * @urlParam id string required Subtask ID (UUID).
     *
     * @response {"success": true, "data": {...}}
     */
    #[Attributes\Patch('{id}/restore')]
    public function restore(string $id, SubTaskService $service): Response
    {
        $subTask = $service->restore($id);
        return $this->response(SubTaskResource::make($subTask));
    }

    /**
     * Toggle completion status of a subtask.
     *
     * @authenticated
     *
     * @urlParam subTask string required Subtask ID (UUID).
     *
     * @response {"success": true, "data": {"id": "...", "is_completed": true, ...}}
     */
    #[Attributes\Patch('{subTask}/toggle')]
    public function toggle(SubTask $subTask, SubTaskService $service): Response
    {
        $subTask = $service->toggleComplete($subTask);
        return $this->response(SubTaskResource::make($subTask));
    }
}
