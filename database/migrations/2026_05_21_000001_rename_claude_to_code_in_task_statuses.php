<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::connection('tc_doc')
            ->table('task_statuses')
            ->where('slug', 'analise-claude')
            ->update(['slug' => 'analise-code', 'name' => 'Análise - Code']);

        DB::connection('tc_doc')
            ->table('task_statuses')
            ->where('slug', 'revisao-claude')
            ->update(['slug' => 'revisao-code', 'name' => 'Revisão - Code']);
    }

    public function down(): void
    {
        DB::connection('tc_doc')
            ->table('task_statuses')
            ->where('slug', 'analise-code')
            ->update(['slug' => 'analise-claude', 'name' => 'Análise - Claude']);

        DB::connection('tc_doc')
            ->table('task_statuses')
            ->where('slug', 'revisao-code')
            ->update(['slug' => 'revisao-claude', 'name' => 'Revisão - Claude']);
    }
};
