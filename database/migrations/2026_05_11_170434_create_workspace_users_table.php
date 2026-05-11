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
        Schema::create(\App\Enums\Table::WORKSPACE_USERS->tableName(), function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Workspaces\Workspace::class, 'workspace_id')
                ->constrained(Table::WORKSPACES->tableName())
                ->onDelete('cascade');
            $table->foreignIdFor(\App\Models\User::class, 'user_id')
                ->constrained(Table::USERS->tableName())
                ->onDelete('cascade');
            $table->primary(['workspace_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(\App\Enums\Table::WORKSPACE_USERS->tableName());
    }
};
