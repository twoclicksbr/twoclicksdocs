<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection('tc_doc')->create('shares', function (Blueprint $table) {
            $table->id();
            $table->string('hash', 12)->unique();
            $table->foreignId('project_id')->constrained('projects');
            $table->jsonb('payload');
            $table->foreignId('created_by_token_id')->nullable()->constrained('personal_access_tokens')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index('hash');
        });
    }

    public function down(): void
    {
        Schema::connection('tc_doc')->dropIfExists('shares');
    }
};
