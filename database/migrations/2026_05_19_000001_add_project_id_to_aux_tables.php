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
            Schema::connection('tc_doc')->table($table, function (Blueprint $t) use ($table) {
                $t->foreignId('project_id')->nullable()->after('id')
                  ->constrained('projects')->restrictOnDelete();

                // Drop global unique on slug, add project-scoped unique
                $t->dropUnique("{$table}_slug_unique");
                $t->unique(['project_id', 'slug']);
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::connection('tc_doc')->table($table, function (Blueprint $t) use ($table) {
                $t->dropUnique(["{$table}_project_id_slug_unique"]);
                $t->unique('slug');
                $t->dropConstrainedForeignId('project_id');
            });
        }
    }
};
