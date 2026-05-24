<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tc_doc')->create('sandbox_dumps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('executed_by_person_id')->nullable()
                ->constrained('people')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->string('status', 20)->default('running'); // running | success | failed
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::connection('tc_doc')->dropIfExists('sandbox_dumps');
    }
};
