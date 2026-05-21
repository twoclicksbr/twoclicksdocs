<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tc_doc')->table('task_statuses', function (Blueprint $table) {
            $table->string('model', 20)->nullable()->after('color');
            $table->string('runtime_location', 20)->nullable()->after('model');
        });
    }

    public function down(): void
    {
        Schema::connection('tc_doc')->table('task_statuses', function (Blueprint $table) {
            $table->dropColumn(['model', 'runtime_location']);
        });
    }
};
