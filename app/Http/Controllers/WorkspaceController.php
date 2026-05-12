<?php

namespace App\Http\Controllers;

use App\Http\Resources\WorkspaceResource;
use App\Models\Workspaces\Workspace;
use App\Queries\WorkspaceQuery;
use App\Services\WorkspaceService;
use Dentro\Yalr\Attributes;
use Illuminate\Http\Request;
use Winata\Core\Response\Http\Response;

#[Attributes\Prefix('workspaces')]
class WorkspaceController extends Controller
{
    /**
     * List all workspaces accessible by the authenticated user.
     *
     * @authenticated
     *
     * @queryParam search string optional Search by name or description. Example: "My Workspace"
     * @queryParam filter[owner_id] string optional Filter by owner ID.
     * @queryParam filter[member_id] string optional Filter by user ID who is a member.
     * @queryParam filter[trashed] string optional Filter soft-deleted: only, with. Example: "only"
     * @queryParam sort[field] string optional Sort field: created_at, updated_at, name.
     * @queryParam order string optional Sort direction: asc, desc.
     * @queryParam include string optional Eager load relations: owner, members, projects, tags.
     * @queryParam per_page integer optional Items per page (default 15, max 100).
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": "uuid",
     *       "name": "Acme Corp",
     *       "description": "Main workspace for Acme Corp",
     *       "owner_id": "user-uuid",
     *       "created_at": "2025-04-01T12:00:00Z",
     *       "updated_at": "2025-04-01T12:00:00Z",
     *       "owner": {"id":"user-uuid","name":"John Doe","email":"john@example.com"},
     *       "members": []
     *     }
     *   ],
     *   "meta": {"current_page":1,"per_page":15,"total":1,"last_page":1}
     * }
     * @response 401 {"success": false, "message": "Unauthenticated."}
     */
    #[Attributes\Get('')]
    public function index(): Response
    {
        $user = auth()->user();
        $query = WorkspaceQuery::with(['owner', 'members'])
            ->where('owner_id', $user->id)
            ->filterColumn()
            ->orderColumn()
            ->getAllDataPaginated();

        return $this->response(WorkspaceResource::collection($query));
    }

    /**
     * Create a new workspace.
     *
     * @authenticated
     *
     * @bodyParam name string required Workspace name. Example: "Acme Corp"
     * @bodyParam description string optional Workspace description. Example: "Main workspace for Acme Corp"
     *
     * @response 201 {
     *   "success": true,
     *   "data": {
     *     "id": "uuid",
     *     "name": "Acme Corp",
     *     "description": "Main workspace for Acme Corp",
     *     "owner_id": "user-uuid",
     *     "created_at": "2025-04-01T12:00:00Z",
     *     "updated_at": "2025-04-01T12:00:00Z"
     *   }
     * }
     * @response 422 {"success": false, "message": "Validation error", "errors": {...}}
     */
    #[Attributes\Post('create')]
    public function create(Request $request, WorkspaceService $service): Response
    {
        $user = auth()->user();
        $workspace = $service->create($user, $request->input());

        return $this->response(WorkspaceResource::make($workspace));
    }

    /**
     * Get workspace detail by ID.
     *
     * @authenticated
     *
     * @urlParam workspace string required Workspace ID (UUID). Example: "abc-123"
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": "abc-123",
     *     "name": "Acme Corp",
     *     "description": "...",
     *     "owner": {"id":"user-uuid","name":"John Doe","email":"john@example.com"},
     *     "members": [
     *       {"id":"user-uuid","name":"Jane Doe","email":"jane@example.com"}
     *     ]
     *   }
     * }
     * @response 404 {"success": false, "message": "No query results for model"}
     */
    #[Attributes\Get('{workspace}/detail')]
    public function show(Workspace $workspace): Response
    {
        $workspace->load(['owner', 'members']);
        return $this->response(WorkspaceResource::make($workspace));
    }

    /**
     * Update an existing workspace.
     *
     * @authenticated
     *
     * @urlParam workspace string required Workspace ID (UUID).
     * @bodyParam name string optional New workspace name. Example: "Updated Workspace"
     * @bodyParam description string optional New description. Example: "New description"
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": "uuid",
     *     "name": "Updated Workspace",
     *     "description": "New description",
     *     "owner_id": "user-uuid"
     *   }
     * }
     */
    #[Attributes\Put('{workspace}/update')]
    public function update(Request $request, Workspace $workspace, WorkspaceService $service): Response
    {
        $workspace = $service->update($workspace, $request->input());
        return $this->response(WorkspaceResource::make($workspace));
    }

    /**
     * Soft delete a workspace.
     *
     * @authenticated
     *
     * @urlParam workspace string required Workspace ID (UUID).
     *
     * @response 200 {"success": true, "data": null}
     * @response 403 {"success": false, "message": "Unauthorized"}
     */
    #[Attributes\Delete('{workspace}/delete')]
    public function destroy(Workspace $workspace, WorkspaceService $service): Response
    {
        $service->delete($workspace);
        return $this->response();
    }

    /**
     * Restore a soft-deleted workspace.
     *
     * @authenticated
     *
     * @urlParam id string required Workspace ID (UUID).
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": "uuid",
     *     "name": "Acme Corp",
     *     "description": "...",
     *     "owner_id": "user-uuid",
     *     "created_at": "2025-04-01T12:00:00Z"
     *   }
     * }
     */
    #[Attributes\Patch('{id}/restore')]
    public function restore(string $id, WorkspaceService $service): Response
    {
        $workspace = $service->restore($id);
        return $this->response(WorkspaceResource::make($workspace));
    }

    /**
     * Add a member to the workspace.
     *
     * @authenticated
     *
     * @urlParam workspace string required Workspace ID (UUID).
     * @bodyParam user_id string required User ID (UUID) to add as member. Example: "user-123"
     *
     * @response 200 {"success": true, "data": {"message": "Member added"}}
     * @response 422 {"success": false, "message": "The user_id field is required."}
     */
    #[Attributes\Post('{workspace}/members')]
    public function addMember(Workspace $workspace, Request $request, WorkspaceService $service): Response
    {
        $request->validate(['user_id' => 'required|uuid|exists:users,id']);
        $service->addMember($workspace, $request->user_id);
        return $this->response(['message' => 'Member added']);
    }

    /**
     * Remove a member from the workspace.
     *
     * @authenticated
     *
     * @urlParam workspace string required Workspace ID (UUID).
     * @urlParam userId string required User ID (UUID) to remove.
     *
     * @response 200 {"success": true, "data": {"message": "Member removed"}}
     */
    #[Attributes\Delete('{workspace}/members/{userId}')]
    public function removeMember(Workspace $workspace, string $userId, WorkspaceService $service): Response
    {
        $service->removeMember($workspace, $userId);
        return $this->response(['message' => 'Member removed']);
    }

    /**
     * Get list of members of a workspace with optional search.
     *
     * @authenticated
     *
     * @urlParam workspace string required Workspace ID (UUID).
     * @queryParam search string optional Search by member name. Example: "John"
     * @queryParam per_page integer optional Items per page (default 15).
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {"id": "user-uuid", "name": "Jane Doe", "email": "jane@example.com"}
     *   ],
     *   "meta": {"current_page":1,"per_page":15,"total":1,"last_page":1}
     * }
     */
    #[Attributes\Get('{workspace}/members')]
    public function members(Workspace $workspace, Request $request, WorkspaceService $service): Response
    {
        $members = $service->getMembers($workspace, $request->get('search'));
        return $this->response($members);
    }
}
