<?php

use App\Enums\Table;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(Table::TASK_USERS->tableName(), function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Tasks\Task::class, 'task_id')
                ->constrained(Table::TASKS->tableName())
                ->onDelete('cascade');
            $table->foreignIdFor(\App\Models\User::class, 'user_id')
                ->constrained(Table::USERS->tableName())
                ->onDelete('cascade');
            $table->primary(['task_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Table::TASK_USERS->tableName());
    }
};
