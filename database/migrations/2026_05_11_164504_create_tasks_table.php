<?php

use App\Enums\Table;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(Table::TASKS->tableName(), function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('todo');
            $table->string('priority')->default('normal');
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->foreignIdFor(\App\Models\User::class, 'assignee_id')->nullable()
                ->constrained(Table::USERS->tableName())
                ->onDelete('set null');
            $table->foreignIdFor(\App\Models\Project::class, 'project_id')
                ->constrained(Table::PROJECTS->tableName())
                ->onDelete('cascade');
            $table->foreignIdFor(\App\Models\User::class, 'created_by')
                ->constrained(Table::USERS->tableName())
                ->onDelete('cascade');
            $table->integer('time_estimate')->nullable();
            $table->integer('time_spent')->default(0);
            $table->integer('order_column')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'status', 'priority', 'assignee_id', 'order_column']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Table::TASKS->tableName());
    }
};
