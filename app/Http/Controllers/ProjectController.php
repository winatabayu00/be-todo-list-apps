<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Queries\ProjectQuery;
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
     * @response array{success: bool, data: ProjectResource[], meta: array}
     */
    #[Attributes\Get('')]
    public function index(): Response
    {
        $query = ProjectQuery::filterColumn()
            ->orderColumn()
            ->getAllDataPaginated();
        $projects = $query->paginate(request()->get('per_page', 15));
        return $this->response(ProjectResource::collection($projects));
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
     * @response 201 {"success": true, "data": {"id": "...", "name": "...", ...}}
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
     * @response {"success": true, "data": {"id": "...", "workspace": {...}, "creator": {...}, "tasks": [...]}}
     */
    #[Attributes\Get('{project}/detail')]
    public function show(Project $project): Response
    {
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
     * @response {"success": true, "data": {...}}
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
     * @response {"success": true, "message": "Project deleted"}
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
     * @response {"success": true, "data": {...}}
     */
    #[Attributes\Patch('{project}/restore')]
    public function restore(string $project, ProjectService $service): Response
    {
        $user = auth()->user();
        $restored = $service->restore($project, $user);
        return $this->response(ProjectResource::make($restored));
    }
}
