<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Workspaces\Workspace;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $workspaces = Workspace::all();
        $colors = ['#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF', '#00FFFF', '#FFA500', '#800480'];

        foreach ($workspaces as $workspace) {
            $tagNames = ['bug', 'feature', 'enhancement', 'documentation', 'urgent', 'low-priority', 'blocked', 'ready'];
            foreach ($tagNames as $name) {
                Tag::create([
                    'name' => $name,
                    'color' => $faker->randomElement($colors),
                    'workspace_id' => $workspace->id,
                ]);
            }
        }
    }
}
