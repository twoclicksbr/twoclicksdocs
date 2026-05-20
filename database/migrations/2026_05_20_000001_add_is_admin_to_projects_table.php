<?php

use App\Models\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tc_doc')->table('projects', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('status');
        });

        Project::where('slug', 'docs-twoclicks')->update(['is_admin' => true]);
    }

    public function down(): void
    {
        Schema::connection('tc_doc')->table('projects', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }
};
