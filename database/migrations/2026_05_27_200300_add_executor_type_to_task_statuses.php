<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tc_doc';

    public function up(): void
    {
        Schema::connection('tc_doc')->table('task_statuses', function (Blueprint $table) {
            $table->enum('executor_type', ['code', 'shell'])->default('code')->after('code_prompt');
        });
    }

    public function down(): void
    {
        Schema::connection('tc_doc')->table('task_statuses', function (Blueprint $table) {
            $table->dropColumn('executor_type');
        });
    }
};
