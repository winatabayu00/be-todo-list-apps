<?php

namespace Database\Seeders;

use App\Models\Tasks\SubTask;
use App\Models\Tasks\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class SubTaskSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $tasks = Task::all();
        $users = User::all();

        foreach ($tasks as $task) {
            $subTasksCount = rand(0, 4);
            for ($i = 0; $i < $subTasksCount; $i++) {
                SubTask::create([
                    'title' => ucfirst($faker->sentence(3)),
                    'is_completed' => $faker->boolean(30),
                    'task_id' => $task->id,
                    'assignee_id' => $users->random()->id,
                ]);
            }
        }
    }
}
