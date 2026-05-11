<?php

namespace App\Http\Controllers;

use App\Http\Resources\TagResource;
use App\Models\Tag;
use App\Models\Workspaces\Workspace;
use App\Queries\TagQuery;
use App\Services\TagService;
use Dentro\Yalr\Attributes;
use Illuminate\Container\EntryNotFoundException;
use Illuminate\Contracts\Container\CircularDependencyException;
use Illuminate\Http\Request;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Winata\Core\Response\Http\Response;

#[Attributes\Prefix('tags')]
class TagController extends Controller
{
    /**
     * @return Response
     * @throws EntryNotFoundException
     * @throws CircularDependencyException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
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
     * @param Request $request
     * @param TagService $service
     * @return Response
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
     * @param Tag $tag
     * @return Response
     */
    #[Attributes\Get('{tag}/detail')]
    public function show(Tag $tag): Response
    {
        $tag->load('workspace');
        return $this->response(TagResource::make($tag));
    }

    /**
     * @param Request $request
     * @param Tag $tag
     * @param TagService $service
     * @return Response
     */
    #[Attributes\Put('{tag}/update')]
    public function update(Request $request, Tag $tag, TagService $service): Response
    {
        $tag = $service->update($tag, $request->input());
        return $this->response(TagResource::make($tag));
    }

    /**
     * @param Tag $tag
     * @param TagService $service
     * @return Response
     */
    #[Attributes\Delete('{tag}/delete')]
    public function destroy(Tag $tag, TagService $service): Response
    {
        $service->delete($tag);
        return $this->response();
    }

    /**
     * @param string $id
     * @param TagService $service
     * @return Response
     */
    #[Attributes\Patch('{id}/restore')]
    public function restore(string $id, TagService $service): Response
    {
        $tag = $service->restore($id);
        return $this->response(TagResource::make($tag));
    }
}
