<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Tasks\Task;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $projects = Project::with('workspace')->get();
        $allTags = Tag::all();
        $users = User::all();

        foreach ($projects as $project) {
            $tasksCount = rand(5, 15);
            for ($i = 0; $i < $tasksCount; $i++) {
                $status = $faker->randomElement(['todo', 'in_progress', 'in_review', 'done']);
                $task = Task::create([
                    'title' => ucfirst($faker->sentence(5)),
                    'description' => $faker->paragraphs(rand(1, 3), true),
                    'status' => $status,
                    'priority' => $faker->randomElement(['urgent', 'high', 'normal', 'low']),
                    'start_date' => $faker->optional(0.7)->dateTimeBetween('-1 month', 'now'),
                    'due_date' => $faker->optional(0.8)->dateTimeBetween('now', '+2 months'),
                    'assignee_id' => $users->random()->id,
                    'project_id' => $project->id,
                    'created_by' => $project->created_by,
                    'time_estimate' => $faker->optional(0.6)->numberBetween(30, 1200),
                    'time_spent' => $status === 'done' ? $faker->numberBetween(30, 600) : 0,
                    'order_column' => $i,
                ]);

                // Attach tags (0-3 tags from same workspace)
                $availableTags = $allTags->where('workspace_id', $project->workspace_id);
                if ($availableTags->count()) {
                    $tagsToAttach = $availableTags->random(rand(0, 3))->pluck('id')->toArray();
                    if (!empty($tagsToAttach)) {
                        $task->tags()->attach($tagsToAttach);
                    }
                }

                // Attach additional assignees (0-2)
                $additional = $users->where('id', '!=', $task->assignee_id)->random(rand(0, 2))->pluck('id')->toArray();
                if (!empty($additional)) {
                    $task->assignees()->attach($additional);
                }
            }
        }
    }
}
