<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Services\DashboardService;
use Dentro\Yalr\Attributes;
use Illuminate\Http\Request;
use Winata\Core\Response\Http\Response;

#[Attributes\Prefix('dashboard')]
class DashboardController extends Controller
{

    /**
     * @param Request $request
     * @param DashboardService $service
     * @return Response
     */
    #[Attributes\Get('stats')]
    public function stats(Request $request, DashboardService $service): Response
    {
        $projectId = $request->get('project_id');
        $stats = [
            'by_status' => $service->taskStatusStats($projectId),
            'by_priority' => $service->taskPriorityStats($projectId),
        ];
        return $this->response($stats);
    }

    /**
     * @param Request $request
     * @param DashboardService $service
     * @return Response
     */
    #[Attributes\Get('upcoming-tasks')]
    public function upcomingTasks(Request $request, DashboardService $service): Response
    {
        $tasks = $service->upcomingTasks(
            $request->get('project_id'),
            $request->get('days', 7)
        );
        return $this->response(TaskResource::collection($tasks));
    }

    /**
     * @param Request $request
     * @param DashboardService $service
     * @return Response
     */
    #[Attributes\Get('overdue-tasks')]
    public function overdueTasks(Request $request, DashboardService $service): Response
    {
        $tasks = $service->overdueTasks($request->get('project_id'));
        return $this->response(TaskResource::collection($tasks));
    }

    /**
     * @param Request $request
     * @param DashboardService $service
     * @return Response
     */
    #[Attributes\Get('recent-tasks')]
    public function recentTasks(Request $request, DashboardService $service): Response
    {
        $tasks = $service->recentTasks(
            $request->get('project_id'),
            $request->get('days', 7)
        );
        return $this->response(TaskResource::collection($tasks));
    }

    /**
     * @param Request $request
     * @param DashboardService $service
     * @return Response
     */
    #[Attributes\Get('my-tasks')]
    public function myTasks(Request $request, DashboardService $service): Response
    {
        $user = auth()->user();
        $tasks = $service->myTasks($user->id, $request->get('status'));
        return $this->response(TaskResource::collection($tasks));
    }

    /**
     * @param string $workspaceId
     * @param DashboardService $service
     * @return Response
     */
    #[Attributes\Get('workspace/{workspaceId}/summary')]
    public function workspaceSummary(string $workspaceId, DashboardService $service): Response
    {
        $summary = $service->workspaceSummary($workspaceId);
        return $this->response($summary);
    }

    /**
     * @param DashboardService $service
     * @return Response
     */
    #[Attributes\Get('overall-summary')]
    public function overallSummary(DashboardService $service): Response
    {
        $user = auth()->user();
        $summary = $service->overallSummary($user->id);
        return $this->response($summary);
    }
}
