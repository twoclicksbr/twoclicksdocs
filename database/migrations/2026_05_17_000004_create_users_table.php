<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tc_doc')->create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::connection('tc_doc')->dropIfExists('users');
    }
};
