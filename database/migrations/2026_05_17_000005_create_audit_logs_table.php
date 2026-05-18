<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tc_doc')->create('audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('person_id')->nullable()->constrained('people')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('token_name', 20)->nullable();
            $table->string('action', 20);
            $table->string('table_name', 50);
            $table->unsignedBigInteger('record_id');
            $table->jsonb('old_values')->nullable();
            $table->jsonb('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['table_name', 'record_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('tc_doc')->dropIfExists('audit_logs');
    }
};
