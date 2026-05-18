<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tc_doc')->create('tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('task_status_id')->constrained('task_statuses');
            $table->foreignId('task_fase_id')->constrained('task_fases');
            $table->foreignId('task_modulo_id')->constrained('task_modulos');
            $table->foreignId('task_tipo_id')->constrained('task_tipos');
            $table->foreignId('task_prioridade_id')->constrained('task_prioridades');
            $table->integer('order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::connection('tc_doc')->dropIfExists('tasks');
    }
};
