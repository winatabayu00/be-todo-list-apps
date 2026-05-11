<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskTimeLogResource;
use App\Models\Tasks\Task;
use App\Services\TaskService;
use Dentro\Yalr\Attributes;
use Illuminate\Container\EntryNotFoundException;
use Illuminate\Contracts\Container\CircularDependencyException;
use Illuminate\Http\Request;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Winata\Core\Response\Http\Response;

#[Attributes\Prefix('tasks/{task}/time')]
class TimeTrackingController extends Controller
{
    /**
     * @param Request $request
     * @param Task $task
     * @param TaskService $service
     * @return Response
     */
    #[Attributes\Post('log')]
    public function log(Request $request, Task $task, TaskService $service): Response
    {
        $user = auth()->user();
        $timeLog = $service->logTime($task, $user, $request->input());

        return $this->response(TaskTimeLogResource::make($timeLog));
    }

    /**
     * @param Request $request
     * @param Task $task
     * @param TaskService $service
     * @return Response
     * @throws EntryNotFoundException
     * @throws CircularDependencyException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Attributes\Get('logs')]
    public function logs(Request $request, Task $task, TaskService $service): Response
    {
        $filters = $request->only(['date_from', 'date_to', 'user_id']);
        $logs = $service->getTimeLogs($task, $filters);

        return $this->response(TaskTimeLogResource::collection($logs));
    }

    /**
     * @param Task $task
     * @param TaskService $service
     * @return Response
     */
    #[Attributes\Get('total')]
    public function total(Task $task, TaskService $service): Response
    {
        $total = $service->getTotalTimeSpent($task);
        return $this->response([
            'total_minutes' => $total,
            'total_hours' => round($total / 60, 2),
        ]);
    }

    /**
     * @param Request $request
     * @param Task $task
     * @param TaskService $service
     * @return Response
     */
    #[Attributes\Put('estimate')]
    public function updateEstimate(Request $request, Task $task, TaskService $service): Response
    {
        $request->validate([
            'minutes' => 'required|integer|min:0',
        ]);
        $user = auth()->user();
        $task = $service->updateTimeEstimate($task, $user, $request->minutes);

        return $this->response([
            'time_estimate' => $task->time_estimate,
            'time_estimate_hours' => round($task->time_estimate / 60, 2),
        ]);
    }
}
