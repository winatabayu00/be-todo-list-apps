<?php

namespace App\Http\Controllers;

use App\Enums\ProjectVisibility;
use App\Enums\TasksLogActions;
use App\Enums\TasksPriority;
use App\Enums\TasksStatus;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Tasks\Task;
use App\Models\User;
use App\Models\Workspaces\Workspace;
use Dentro\Yalr\Attributes;
use Illuminate\Http\Request;
use Winata\Core\Response\Http\Response;

#[Attributes\Prefix('')]
class OptionController extends Controller
{
    /**
     * Get list of workspaces (id, name) accessible by the authenticated user.
     *
     * @authenticated
     *
     * @queryParam search string optional Search by workspace name. Example: "Acme"
     *
     * @response array{success: bool, data: array{id: string, name: string}[]}
     * @response status=401 {"success": false, "message": "Unauthenticated."}
     */
    #[Attributes\Get('get-workspace')]
    public function getWorkspace(Request $request): Response
    {
        $user = auth()->user();
        $query = Workspace::where('owner_id', $user->id)
            ->orWhereHas('members', fn($q) => $q->where('user_id', $user->id));

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $data = $query->limit(100)->get()->map(fn($item) => [
            'id' => $item->id,
            'name' => $item->name,
        ]);

        return $this->response($data);
    }

    /**
     * Get list of projects (id, name, workspace_id) with optional filters.
     *
     * @authenticated
     *
     * @queryParam workspace_id string optional Filter by workspace ID (UUID). Example: "ws-123"
     * @queryParam search string optional Search by project name. Example: "Mobile App"
     *
     * @response array{success: bool, data: array{id: string, name: string, workspace_id: string}[]}
     */
    #[Attributes\Get('get-projects')]
    public function getProjects(Request $request): Response
    {
        $user = auth()->user();
        $accessibleWorkspaceIds = Workspace::where('owner_id', $user->id)
            ->orWhereHas('members', fn($q) => $q->where('user_id', $user->id))
            ->pluck('id');

        $query = Project::whereIn('workspace_id', $accessibleWorkspaceIds);
        if ($request->has('workspace_id')) {
            $query->where('workspace_id', $request->workspace_id);
        }
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $data = $query->limit(100)->get()->map(fn($item) => [
            'id' => $item->id,
            'name' => $item->name,
            'workspace_id' => $item->workspace_id,
        ]);

        return $this->response($data);
    }

    /**
     * Get list of users (id, name, email) for assignment.
     *
     * @authenticated
     *
     * @queryParam search string optional Search by name or email. Example: "john"
     *
     * @response array{success: bool, data: array{id: string, name: string, email: string}[]}
     */
    #[Attributes\Get('get-users')]
    public function getUsers(Request $request): Response
    {
        $query = User::query();
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $data = $query->limit(100)->get()->map(fn($item) => [
            'id' => $item->id,
            'name' => $item->name,
            'email' => $item->email,
        ]);

        return $this->response($data);
    }

    /**
     * Get list of tags (id, name, color) with optional workspace filter.
     *
     * @authenticated
     *
     * @queryParam workspace_id string optional Filter by workspace ID (UUID).
     * @queryParam search string optional Search by tag name.
     *
     * @response array{success: bool, data: array{id: string, name: string, color: string|null}[]}
     */
    #[Attributes\Get('get-tags')]
    public function getTags(Request $request): Response
    {
        $user = auth()->user();
        $accessibleWorkspaceIds = Workspace::where('owner_id', $user->id)
            ->orWhereHas('members', fn($q) => $q->where('user_id', $user->id))
            ->pluck('id');

        $query = Tag::whereIn('workspace_id', $accessibleWorkspaceIds);
        if ($request->has('workspace_id')) {
            $query->where('workspace_id', $request->workspace_id);
        }
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $data = $query->limit(100)->get()->map(fn($item) => [
            'id' => $item->id,
            'name' => $item->name,
            'color' => $item->color,
        ]);

        return $this->response($data);
    }

    /**
     * Get list of tasks (id, title) with optional project filter.
     *
     * @authenticated
     *
     * @queryParam project_id string optional Filter by project ID (UUID).
     * @queryParam search string optional Search by task title.
     *
     * @response array{success: bool, data: array{id: string, title: string}[]}
     */
    #[Attributes\Get('get-tasks')]
    public function getTasks(Request $request): Response
    {
        $user = auth()->user();
        $accessibleWorkspaceIds = Workspace::where('owner_id', $user->id)
            ->orWhereHas('members', fn($q) => $q->where('user_id', $user->id))
            ->pluck('id');
        $accessibleProjectIds = Project::whereIn('workspace_id', $accessibleWorkspaceIds)->pluck('id');

        $query = Task::whereIn('project_id', $accessibleProjectIds);
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $data = $query->limit(100)->get()->map(fn($item) => [
            'id' => $item->id,
            'title' => $item->title,
        ]);

        return $this->response($data);
    }

    /**
     * Get all task statuses (todo, in_progress, in_review, done) for dropdown.
     *
     * @authenticated
     *
     * @response array{success: bool, data: array{value: string, label: string}[]}
     */
    #[Attributes\Get('get-task-statuses')]
    public function getTaskStatuses(): Response
    {
        $data = array_map(fn($case) => [
            'value' => $case->value,
            'label' => ucfirst(str_replace('_', ' ', $case->value)),
        ], TasksStatus::cases());

        return $this->response($data);
    }

    /**
     * Get all task priorities (urgent, high, normal, low) for dropdown.
     *
     * @authenticated
     *
     * @response array{success: bool, data: array{value: string, label: string}[]}
     */
    #[Attributes\Get('get-task-priorities')]
    public function getTaskPriorities(): Response
    {
        $data = array_map(fn($case) => [
            'value' => $case->value,
            'label' => ucfirst($case->value),
        ], TasksPriority::cases());

        return $this->response($data);
    }

    /**
     * Get project visibility options (private, team, public) for dropdown.
     *
     * @authenticated
     *
     * @response array{success: bool, data: array{value: string, label: string}[]}
     */
    #[Attributes\Get('get-project-visibilities')]
    public function getProjectVisibilities(): Response
    {
        $data = array_map(fn($case) => [
            'value' => $case->value,
            'label' => ucfirst($case->value),
        ], ProjectVisibility::cases());

        return $this->response($data);
    }

    /**
     * Get all task log actions for dropdown/filter.
     *
     * @authenticated
     *
     * @response array{success: bool, data: array{value: string, label: string}[]}
     */
    #[Attributes\Get('get-task-log-actions')]
    public function getTaskLogActions(): Response
    {
        $data = array_map(fn($case) => [
            'value' => $case->value,
            'label' => ucfirst(str_replace('_', ' ', $case->value)),
        ], TasksLogActions::cases());

        return $this->response($data);
    }

    /**
     * Get all enum values in one request (task statuses, priorities, visibilities, log actions) for efficiency.
     *
     * @authenticated
     *
     * @response array{success: bool, data: array{task_statuses: array[], task_priorities: array[], project_visibilities: array[], task_log_actions: array[]}}
     */
    #[Attributes\Get('get-enums')]
    public function getAllEnums(): Response
    {
        return $this->response([
            'task_statuses' => array_map(fn($c) => ['value' => $c->value, 'label' => ucfirst(str_replace('_', ' ', $c->value))], TasksStatus::cases()),
            'task_priorities' => array_map(fn($c) => ['value' => $c->value, 'label' => ucfirst($c->value)], TasksPriority::cases()),
            'project_visibilities' => array_map(fn($c) => ['value' => $c->value, 'label' => ucfirst($c->value)], ProjectVisibility::cases()),
            'task_log_actions' => array_map(fn($c) => ['value' => $c->value, 'label' => ucfirst(str_replace('_', ' ', $c->value))], TasksLogActions::cases()),
        ]);
    }
}
