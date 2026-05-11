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
     * @return Response
     */
    #[Attributes\Get('')]
    public function index(): Response
    {
        $data = TaskQuery::filterColumn()
            ->orderColumn()
            ->getAllDataPaginated();

        return $this->response(TaskResource::collection($data));
    }

    /**
     * @param Request $request
     * @param TaskService $service
     * @return Response
     */
    #[Attributes\Post('create')]
    public function create(Request $request, TaskService $service): Response
    {
        $user = auth()->user();
        $service->create(user: $user, data: $request->input());

        return $this->response();
    }

    /**
     * @param Task $task
     * @return Response
     */
    #[Attributes\get('{task}/detail')]
    public function show(Task $task): Response
    {
        return $this->response(TaskResource::make($task));
    }

    /**
     * @param Request $request
     * @param Task $task
     * @param TaskService $service
     * @return Response
     */
    #[Attributes\Put('{task}/update')]
    public function update(Request $request, Task $task, TaskService $service): Response
    {
        $user = auth()->user();
        $service->update(task: $task,user: $user, inputs: $request->input() );
        return $this->response();
    }

    /**
     * @param Task $task
     * @param TaskService $service
     * @return Response
     */
    #[Attributes\Delete('{task}/delete')]
    public function destroy(Task $task, TaskService $service): Response
    {
        $user = auth()->user();
        $service->delete(task: $task, user: $user);
        return $this->response();
    }

    /**
     * @param string $taskId
     * @param TaskService $service
     * @return Response
     */
    #[Attributes\Patch('{task}/restore')]
    public function restore(string $taskId, TaskService $service): Response
    {
        $user = auth()->user();
        $service->restore(taskId: $taskId, user: $user);
        return $this->response();
    }
}
