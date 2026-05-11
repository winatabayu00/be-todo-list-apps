<?php

namespace App\Http\Controllers;

use App\Http\Resources\SubTaskResource;
use App\Models\Tasks\SubTask;
use App\Queries\SubTaskQuery;
use App\Services\SubTaskService;
use Dentro\Yalr\Attributes;
use Illuminate\Container\EntryNotFoundException;
use Illuminate\Contracts\Container\CircularDependencyException;
use Illuminate\Http\Request;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Winata\Core\Response\Http\Response;

#[Attributes\Prefix('subtasks')]
class SubTaskController extends Controller
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
        $query = SubTaskQuery::filterColumn()
            ->orderColumn()
            ->getAllDataPaginated();
        $subTasks = $query->paginate(request()->get('per_page', 15));

        return $this->response(SubTaskResource::collection($subTasks));
    }

    /**
     * @param Request $request
     * @param SubTaskService $service
     * @return Response
     */
    #[Attributes\Post('create')]
    public function create(Request $request, SubTaskService $service): Response
    {
        $user = auth()->user();
        $subTask = $service->create($user, $request->input());

        return $this->response(SubTaskResource::make($subTask));
    }

    /**
     * @param SubTask $subTask
     * @return Response
     */
    #[Attributes\Get('{subTask}/detail')]
    public function show(SubTask $subTask): Response
    {
        $subTask->load(['task', 'assignee']);
        return $this->response(SubTaskResource::make($subTask));
    }

    /**
     * @param Request $request
     * @param SubTask $subTask
     * @param SubTaskService $service
     * @return Response
     */
    #[Attributes\Put('{subTask}/update')]
    public function update(Request $request, SubTask $subTask, SubTaskService $service): Response
    {
        $subTask = $service->update($subTask, $request->input());
        return $this->response(SubTaskResource::make($subTask));
    }

    /**
     * @param SubTask $subTask
     * @param SubTaskService $service
     * @return Response
     */
    #[Attributes\Delete('{subTask}/delete')]
    public function destroy(SubTask $subTask, SubTaskService $service): Response
    {
        $service->delete($subTask);
        return $this->response();
    }

    /**
     * @param string $id
     * @param SubTaskService $service
     * @return Response
     */
    #[Attributes\Patch('{id}/restore')]
    public function restore(string $id, SubTaskService $service): Response
    {
        $subTask = $service->restore($id);
        return $this->response(SubTaskResource::make($subTask));
    }

    /**
     * @param SubTask $subTask
     * @param SubTaskService $service
     * @return Response
     */
    #[Attributes\Patch('{subTask}/toggle')]
    public function toggle(SubTask $subTask, SubTaskService $service): Response
    {
        $subTask = $service->toggleComplete($subTask);
        return $this->response(SubTaskResource::make($subTask));
    }
}
