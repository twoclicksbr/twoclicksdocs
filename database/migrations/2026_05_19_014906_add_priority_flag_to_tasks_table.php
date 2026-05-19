<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tc_doc';

    public function up(): void
    {
        Schema::connection('tc_doc')->table('tasks', function (Blueprint $table) {
            $table->boolean('priority_flag')->default(false)->after('order');
            $table->index('priority_flag');
        });
    }

    public function down(): void
    {
        Schema::connection('tc_doc')->table('tasks', function (Blueprint $table) {
            $table->dropIndex(['priority_flag']);
            $table->dropColumn('priority_flag');
        });
    }
};
