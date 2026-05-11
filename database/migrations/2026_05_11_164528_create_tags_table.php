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
        Schema::create(Table::TAGS->tableName(), function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('color')->nullable();
            $table->foreignIdFor(\App\Models\Workspaces\Workspace::class, 'workspace_id')
                ->constrained(Table::WORKSPACES->tableName())
                ->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['name', 'workspace_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Table::TAGS->tableName());
    }
};
