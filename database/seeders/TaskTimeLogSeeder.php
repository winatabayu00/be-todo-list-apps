<?php

namespace Database\Seeders;

use App\Models\Tasks\Task;
use App\Models\Tasks\TaskTimeLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class TaskTimeLogSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $tasks = Task::all();
        $users = User::all();

        foreach ($tasks as $task) {
            // Generate random time logs only if task has time_spent > 0
            if ($task->time_spent > 0) {
                $remaining = $task->time_spent;
                while ($remaining > 0) {
                    $minutes = min($remaining, $faker->numberBetween(15, 60));
                    TaskTimeLog::create([
                        'task_id' => $task->id,
                        'user_id' => $task->assignee_id ?? $users->random()->id,
                        'minutes' => $minutes,
                        'description' => $faker->sentence(),
                        'created_at' => $faker->dateTimeBetween('-1 month', 'now'),
                    ]);
                    $remaining -= $minutes;
                }
            }
        }
    }
}
