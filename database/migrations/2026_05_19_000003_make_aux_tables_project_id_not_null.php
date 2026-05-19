<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'task_statuses',
        'task_fases',
        'task_modulos',
        'task_tipos',
        'task_prioridades',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::connection('tc_doc')->table($table, function (Blueprint $t) {
                $t->foreignId('project_id')->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::connection('tc_doc')->table($table, function (Blueprint $t) {
                $t->foreignId('project_id')->nullable()->change();
            });
        }
    }
};
