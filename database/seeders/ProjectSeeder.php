<?php

namespace Database\Seeders;

use App\Models\Workspaces\Workspace;
use App\Models\Project;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $workspaces = Workspace::all();

        foreach ($workspaces as $workspace) {
            $projectsCount = rand(2, 4);
            for ($i = 0; $i < $projectsCount; $i++) {
                Project::create([
                    'name' => ucfirst($faker->words(3, true)),
                    'description' => $faker->sentence,
                    'visibility' => $faker->randomElement(['private', 'team', 'public']),
                    'workspace_id' => $workspace->id,
                    'created_by' => $workspace->owner_id,
                ]);
            }
        }
    }
}
