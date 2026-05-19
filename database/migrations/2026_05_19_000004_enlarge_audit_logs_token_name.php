<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tc_doc';

    public function up(): void
    {
        Schema::connection('tc_doc')->table('audit_logs', function (Blueprint $table) {
            $table->string('token_name', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::connection('tc_doc')->table('audit_logs', function (Blueprint $table) {
            $table->string('token_name', 20)->nullable()->change();
        });
    }
};
