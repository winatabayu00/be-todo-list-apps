<?php

namespace App\Http\Controllers;

use App\Http\Resources\TagResource;
use App\Models\Tag;
use App\Models\Workspaces\Workspace;
use App\Queries\TagQuery;
use App\Services\TagService;
use Dentro\Yalr\Attributes;
use Illuminate\Http\Request;
use Winata\Core\Response\Http\Response;

#[Attributes\Prefix('tags')]
class TagController extends Controller
{
    /**
     * List all tags with optional filters.
     *
     * @authenticated
     *
     * @queryParam search string optional Search by tag name. Example: "urgent"
     * @queryParam filter[workspace_id] string optional Filter by workspace ID (UUID). Example: "workspace-123"
     * @queryParam filter[color] string optional Filter by color code. Example: "#FF0000"
     * @queryParam filter[trashed] string optional Filter soft-deleted: only, with.
     * @queryParam sort[field] string optional Sort field: name, color, created_at, updated_at.
     * @queryParam order string optional asc/desc (default asc).
     * @queryParam include string optional Eager load relations: workspace.
     * @queryParam per_page integer optional Items per page (default 15, max 100).
     *
     * @response array{success: bool, data: TagResource[], meta: array{current_page: int, per_page: int, total: int, last_page: int}}
     * @response status=401 {"success": false, "message": "Unauthenticated."}
     */
    #[Attributes\Get('')]
    public function index(): Response
    {
        $query = TagQuery::filterColumn()
            ->orderColumn()
            ->getAllDataPaginated();
        $tags = $query->paginate(request()->get('per_page', 15));

        return $this->response(TagResource::collection($tags));
    }

    /**
     * Create a new tag within a workspace.
     *
     * @authenticated
     *
     * @bodyParam workspace_id string required Workspace ID (UUID). Example: "workspace-123"
     * @bodyParam name string required Tag name. Example: "bug"
     * @bodyParam color string optional Hex color code. Example: "#FF0000"
     *
     * @response 201 {"success": true, "data": {"id": "uuid", "name": "bug", "color": "#FF0000", "workspace_id": "..."}}
     * @response 422 {"success": false, "message": "Validation error", "errors": {...}}
     */
    #[Attributes\Post('create')]
    public function create(Request $request, TagService $service): Response
    {
        $request->validate([
            'workspace_id' => ['required', 'uuid', 'exists:workspaces,id'],
        ]);
        /** @var Workspace $workspace */
        $workspace = Workspace::query()->findOrFail($request->workspace_id);
        $tag = $service->create($workspace, $request->input());
        return $this->response(TagResource::make($tag));
    }

    /**
     * Get tag detail by ID.
     *
     * @authenticated
     *
     * @urlParam tag string required Tag ID (UUID). Example: "tag-123"
     *
     * @response {"success": true, "data": {"id": "...", "name": "...", "color": "...", "workspace": {...}}}
     * @response status=404 {"success": false, "message": "No query results for model"}
     */
    #[Attributes\Get('{tag}/detail')]
    public function show(Tag $tag): Response
    {
        $tag->load('workspace');
        return $this->response(TagResource::make($tag));
    }

    /**
     * Update an existing tag.
     *
     * @authenticated
     *
     * @urlParam tag string required Tag ID (UUID).
     * @bodyParam name string optional New tag name. Example: "critical"
     * @bodyParam color string optional New hex color. Example: "#00FF00"
     *
     * @response {"success": true, "data": {"id": "...", "name": "critical", "color": "#00FF00", ...}}
     */
    #[Attributes\Put('{tag}/update')]
    public function update(Request $request, Tag $tag, TagService $service): Response
    {
        $tag = $service->update($tag, $request->input());
        return $this->response(TagResource::make($tag));
    }

    /**
     * Soft delete a tag.
     *
     * @authenticated
     *
     * @urlParam tag string required Tag ID (UUID).
     *
     * @response {"success": true, "message": "Tag deleted"}
     */
    #[Attributes\Delete('{tag}/delete')]
    public function destroy(Tag $tag, TagService $service): Response
    {
        $service->delete($tag);
        return $this->response();
    }

    /**
     * Restore a soft-deleted tag.
     *
     * @authenticated
     *
     * @urlParam id string required Tag ID (UUID).
     *
     * @response {"success": true, "data": {...}}
     */
    #[Attributes\Patch('{id}/restore')]
    public function restore(string $id, TagService $service): Response
    {
        $tag = $service->restore($id);
        return $this->response(TagResource::make($tag));
    }
}
