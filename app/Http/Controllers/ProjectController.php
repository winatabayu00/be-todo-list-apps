<?php
// app/Http/Controllers/ProjectController.php

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
    #[Attributes\Get('')]
    public function index(): Response
    {
        $query = ProjectQuery::filterColumn()
            ->orderColumn()
            ->getAllDataPaginated();
        $projects = $query->paginate(request()->get('per_page', 15));
        return $this->response(ProjectResource::collection($projects));
    }

    #[Attributes\Post('create')]
    public function create(Request $request, ProjectService $service): Response
    {
        $user = auth()->user();
        $project = $service->create($user, $request->input());
        return $this->response(ProjectResource::make($project));
    }

    #[Attributes\Get('{project}/detail')]
    public function show(Project $project): Response
    {
        return $this->response(ProjectResource::make($project->load(['workspace', 'creator', 'tasks'])));
    }

    #[Attributes\Put('{project}/update')]
    public function update(Request $request, Project $project, ProjectService $service): Response
    {
        $user = auth()->user();
        $updated = $service->update($project, $user, $request->input());
        return $this->response(ProjectResource::make($updated));
    }

    #[Attributes\Delete('{project}/delete')]
    public function destroy(Project $project, ProjectService $service): Response
    {
        $user = auth()->user();
        $service->delete($project, $user);
        return $this->response();
    }

    #[Attributes\Patch('{project}/restore')]
    public function restore(string $project, ProjectService $service): Response
    {
        $user = auth()->user();
        $restored = $service->restore($project, $user);
        return $this->response(ProjectResource::make($restored));
    }
}
