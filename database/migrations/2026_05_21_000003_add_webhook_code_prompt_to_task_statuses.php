<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tc_doc')->table('task_statuses', function (Blueprint $table) {
            $table->text('webhook_url')->nullable()->after('runtime_location');
            $table->text('code_prompt')->nullable()->after('webhook_url');
        });
    }

    public function down(): void
    {
        Schema::connection('tc_doc')->table('task_statuses', function (Blueprint $table) {
            $table->dropColumn(['webhook_url', 'code_prompt']);
        });
    }
};
