<?php

namespace App\Http\Controllers;

use App\Http\Resources\WorkspaceResource;
use App\Models\Workspaces\Workspace;
use App\Queries\WorkspaceQuery;
use App\Services\WorkspaceService;
use Dentro\Yalr\Attributes;
use Illuminate\Container\EntryNotFoundException;
use Illuminate\Contracts\Container\CircularDependencyException;
use Illuminate\Http\Request;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Winata\Core\Response\Http\Response;

#[Attributes\Prefix('workspaces')]
class WorkspaceController extends Controller
{
    /**
     * @return Response
     */
    #[Attributes\Get('')]
    public function index(): Response
    {
        $query = WorkspaceQuery::filterColumn()
            ->orderColumn()
            ->getAllDataPaginated();

        return $this->response(WorkspaceResource::collection($query));
    }

    /**
     * @param Request $request
     * @param WorkspaceService $service
     * @return Response
     */
    #[Attributes\Post('create')]
    public function create(Request $request, WorkspaceService $service): Response
    {
        $user = auth()->user();
        $workspace = $service->create($user, $request->input());

        return $this->response(WorkspaceResource::make($workspace));
    }

    /**
     * @param Workspace $workspace
     * @return Response
     */
    #[Attributes\Get('{workspace}/detail')]
    public function show(Workspace $workspace): Response
    {
        $workspace->load(['owner', 'members']);
        return $this->response(WorkspaceResource::make($workspace));
    }

    /**
     * @param Request $request
     * @param Workspace $workspace
     * @param WorkspaceService $service
     * @return Response
     */
    #[Attributes\Put('{workspace}/update')]
    public function update(Request $request, Workspace $workspace, WorkspaceService $service): Response
    {
        $workspace = $service->update($workspace, $request->input());
        return $this->response(WorkspaceResource::make($workspace));
    }

    /**
     * @param Workspace $workspace
     * @param WorkspaceService $service
     * @return Response
     */
    #[Attributes\Delete('{workspace}/delete')]
    public function destroy(Workspace $workspace, WorkspaceService $service): Response
    {
        $service->delete($workspace);
        return $this->response();
    }

    /**
     * @param string $id
     * @param WorkspaceService $service
     * @return Response
     */
    #[Attributes\Patch('{id}/restore')]
    public function restore(string $id, WorkspaceService $service): Response
    {
        $workspace = $service->restore($id);
        return $this->response(WorkspaceResource::make($workspace));
    }

    /**
     * @param Workspace $workspace
     * @param Request $request
     * @param WorkspaceService $service
     * @return Response
     */
    #[Attributes\Post('{workspace}/members')]
    public function addMember(Workspace $workspace, Request $request, WorkspaceService $service): Response
    {
        $request->validate(['user_id' => 'required|uuid|exists:users,id']);
        $service->addMember($workspace, $request->user_id);
        return $this->response(['message' => 'Member added']);
    }

    /**
     * @param Workspace $workspace
     * @param string $userId
     * @param WorkspaceService $service
     * @return Response
     */
    #[Attributes\Delete('{workspace}/members/{userId}')]
    public function removeMember(Workspace $workspace, string $userId, WorkspaceService $service): Response
    {
        $service->removeMember($workspace, $userId);
        return $this->response(['message' => 'Member removed']);
    }

    /**
     * @param Workspace $workspace
     * @param Request $request
     * @param WorkspaceService $service
     * @return Response
     * @throws EntryNotFoundException
     * @throws CircularDependencyException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Attributes\Get('{workspace}/members')]
    public function members(Workspace $workspace, Request $request, WorkspaceService $service): Response
    {
        $members = $service->getMembers($workspace, $request->get('search'));
        return $this->response($members);
    }
}
