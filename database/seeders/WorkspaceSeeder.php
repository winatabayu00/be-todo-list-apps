<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Workspaces\Workspace;
use Illuminate\Database\Seeder;

class WorkspaceSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        // Workspace 1: owned by Admin
        $workspace1 = Workspace::create([
            'name' => 'Acme Corporation',
            'description' => 'Main workspace for Acme Corp',
            'owner_id' => User::where('email', 'admin@example.com')->first()->id,
        ]);
        $workspace1->members()->attach($users->pluck('id')->toArray());

        // Workspace 2: owned by John Doe
        $workspace2 = Workspace::create([
            'name' => 'Startup Incubator',
            'description' => 'Workspace for startup projects',
            'owner_id' => User::where('email', 'john@example.com')->first()->id,
        ]);
        $workspace2->members()->attach($users->random(3)->pluck('id')->toArray());

        // Workspace 3: owned by Jane Smith
        $workspace3 = Workspace::create([
            'name' => 'Freelance Hub',
            'description' => 'Client projects',
            'owner_id' => User::where('email', 'jane@example.com')->first()->id,
        ]);
        $workspace3->members()->attach($users->random(2)->pluck('id')->toArray());
    }
}
