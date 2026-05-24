<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tc_doc')->table('task_statuses', function (Blueprint $table) {
            $table->boolean('show_on_task')->default(false)->after('webhook_url');
            $table->boolean('auto_execute_default')->default(false)->after('show_on_task');
        });
    }

    public function down(): void
    {
        Schema::connection('tc_doc')->table('task_statuses', function (Blueprint $table) {
            $table->dropColumn(['show_on_task', 'auto_execute_default']);
        });
    }
};
