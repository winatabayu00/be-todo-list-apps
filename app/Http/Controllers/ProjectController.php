<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Queries\ProjectQuery;
use App\Queries\WorkspaceQuery;
use App\Services\ProjectService;
use Dentro\Yalr\Attributes;
use Illuminate\Http\Request;
use Winata\Core\Response\Http\Response;

#[Attributes\Prefix('projects')]
class ProjectController extends Controller
{
    /**
     * List all projects with optional filters.
     *
     * @authenticated
     *
     * @queryParam search string optional Search by name or description. Example: "API"
     * @queryParam filter[workspace_id] string optional Filter by workspace ID (UUID).
     * @queryParam filter[visibility] string optional Visibility: private, team, public (comma-separated).
     * @queryParam filter[created_by] string optional Filter by creator user ID.
     * @queryParam filter[trashed] string optional only/with for soft-deleted.
     * @queryParam sort[field] string optional created_at, updated_at, name, visibility.
     * @queryParam include string optional workspace, creator, tasks.
     * @queryParam per_page integer optional Items per page (default 15).
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": "uuid",
     *       "name": "Mobile App",
     *       "description": "...",
     *       "visibility": "team",
     *       "created_at": "2025-04-01T12:00:00Z",
     *       "updated_at": "2025-04-01T12:00:00Z",
     *       "workspace": {"id":"ws-uuid","name":"Acme"},
     *       "creator": {"id":"user-uuid","name":"John Doe"},
     *       "tasks_count": 12
     *     }
     *   ],
     *   "meta": {"current_page":1,"per_page":15,"total":10,"last_page":1}
     * }
     */
    #[Attributes\Get('')]
    public function index(): Response
    {
        $user = auth()->user();
        $workspaces = WorkspaceQuery::where('owner_id', $user->id)
            ->filterColumn()
            ->orderColumn()
            ->getAllData();

        $query = ProjectQuery::with(['workspace', 'creator'])->withCount('tasks')
            ->whereIn('workspace_id', $workspaces->pluck('id')->toArray())
            ->orderColumn()
            ->getAllDataPaginated();
        return $this->response(ProjectResource::collection($query));
    }

    /**
     * Create a new project.
     *
     * @authenticated
     *
     * @bodyParam name string required Project name. Example: "Mobile App"
     * @bodyParam description string optional Description.
     * @bodyParam visibility string optional private|team|public (default private).
     * @bodyParam workspace_id string required Workspace ID (UUID).
     *
     * @response 201 {
     *   "success": true,
     *   "data": {
     *     "id": "uuid",
     *     "name": "Mobile App",
     *     "description": "...",
     *     "visibility": "private",
     *     "workspace": {"id":"ws-uuid","name":"Acme"},
     *     "creator": {"id":"user-uuid","name":"John Doe"}
     *   }
     * }
     */
    #[Attributes\Post('create')]
    public function create(Request $request, ProjectService $service): Response
    {
        $user = auth()->user();
        $project = $service->create($user, $request->input());
        return $this->response(ProjectResource::make($project));
    }

    /**
     * Get project detail by ID.
     *
     * @authenticated
     *
     * @urlParam project string required Project ID (UUID). Example: "proj-123"
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": "uuid",
     *     "name": "Mobile App",
     *     "description": "...",
     *     "visibility": "team",
     *     "workspace": {"id":"ws-uuid","name":"Acme"},
     *     "creator": {"id":"user-uuid","name":"John Doe"},
     *     "tasks": [
     *       {"id":"task-uuid","title":"..."}
     *     ]
     *   }
     * }
     */
    #[Attributes\Get('{project}/detail')]
    public function show(Project $project): Response
    {
        $project->loadMissing(['workspace', 'creator']);
        $project->loadCount('tasks');
        return $this->response(ProjectResource::make($project->load(['workspace', 'creator', 'tasks'])));
    }

    /**
     * Update an existing project.
     *
     * @authenticated
     *
     * @urlParam project string required Project ID (UUID).
     * @bodyParam name string optional New name.
     * @bodyParam description string optional New description.
     * @bodyParam visibility string optional New visibility.
     * @bodyParam workspace_id string optional New workspace ID.
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": "uuid",
     *     "name": "Mobile App",
     *     "description": "...",
     *     "visibility": "team",
     *     "workspace": {"id":"ws-uuid","name":"Acme"},
     *     "creator": {"id":"user-uuid","name":"John Doe"}
     *   }
     * }
     */
    #[Attributes\Put('{project}/update')]
    public function update(Request $request, Project $project, ProjectService $service): Response
    {
        $user = auth()->user();
        $updated = $service->update($project, $user, $request->input());
        return $this->response(ProjectResource::make($updated));
    }

    /**
     * Soft delete a project.
     *
     * @authenticated
     *
     * @urlParam project string required Project ID (UUID).
     *
     * @response 200 {
     *   "success": true,
     *   "data": null
     * }
     */
    #[Attributes\Delete('{project}/delete')]
    public function destroy(Project $project, ProjectService $service): Response
    {
        $user = auth()->user();
        $service->delete($project, $user);
        return $this->response();
    }

    /**
     * Restore a soft-deleted project.
     *
     * @authenticated
     *
     * @urlParam project string required Project ID (UUID).
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": "uuid",
     *     "name": "Mobile App",
     *     "description": "...",
     *     "visibility": "team",
     *     "workspace": {"id":"ws-uuid","name":"Acme"},
     *     "creator": {"id":"user-uuid","name":"John Doe"}
     *   }
     * }
     */
    #[Attributes\Patch('{project}/restore')]
    public function restore(string $project, ProjectService $service): Response
    {
        $user = auth()->user();
        $restored = $service->restore($project, $user);
        return $this->response(ProjectResource::make($restored));
    }
}
