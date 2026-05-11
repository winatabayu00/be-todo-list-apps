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
        Schema::create(Table::PROJECTS->tableName(), function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('visibility', ['private', 'team', 'public'])->default('private');
            $table->foreignIdFor(\App\Models\Workspace::class, 'workspace_id')
                ->constrained(Table::WORKSPACES->tableName())
                ->onDelete('cascade');
            $table->foreignIdFor(\App\Models\User::class, 'created_by')
                ->constrained(Table::USERS->tableName())
                ->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Table::PROJECTS->tableName());
    }
};
