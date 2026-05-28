<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PROJECT_SLUG = 'docstwoclicks';
    private const STATUS_SLUG  = 'aprovacao-prod-twoclicks';

    public function up(): void
    {
        $projectId = DB::connection('tc_doc')
            ->table('projects')
            ->where('slug', self::PROJECT_SLUG)
            ->value('id');

        DB::connection('tc_doc')
            ->table('task_statuses')
            ->where('project_id', $projectId)
            ->where('slug', self::STATUS_SLUG)
            ->update([
                'status'     => false,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        $projectId = DB::connection('tc_doc')
            ->table('projects')
            ->where('slug', self::PROJECT_SLUG)
            ->value('id');

        DB::connection('tc_doc')
            ->table('task_statuses')
            ->where('project_id', $projectId)
            ->where('slug', self::STATUS_SLUG)
            ->update([
                'status'     => true,
                'updated_at' => now(),
            ]);
    }
};
