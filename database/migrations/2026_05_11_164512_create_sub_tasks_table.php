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
        Schema::create(Table::SUB_TASKS->tableName(), function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->boolean('is_completed')->default(false);
            $table->foreignIdFor(\App\Models\Tasks\Task::class, 'task_id')
                ->constrained(Table::TASKS->tableName())
                ->onDelete('cascade');
            $table->foreignIdFor(\App\Models\User::class, 'assignee_id')->nullable()
                ->constrained(Table::USERS->tableName())
                ->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Table::SUB_TASKS->tableName());
    }
};
