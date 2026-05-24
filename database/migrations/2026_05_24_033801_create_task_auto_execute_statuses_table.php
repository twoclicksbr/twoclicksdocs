<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tc_doc')->create('task_auto_execute_statuses', function (Blueprint $table) {
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('task_status_id')->constrained('task_statuses')->cascadeOnDelete();
            $table->primary(['task_id', 'task_status_id']);
            $table->index('task_status_id');
        });
    }

    public function down(): void
    {
        Schema::connection('tc_doc')->dropIfExists('task_auto_execute_statuses');
    }
};
