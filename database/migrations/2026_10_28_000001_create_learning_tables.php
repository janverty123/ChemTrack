<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('student')->index();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
        });
        Schema::create('programmes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->restrictOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('published')->default(false);
            $table->timestamps();
        });
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('type');
            $table->unsignedInteger('position')->default(0);
            $table->longText('content')->nullable();
            $table->string('video_url', 2048)->nullable();
            $table->json('questions')->nullable();
            $table->timestamps();
            $table->index(['programme_id', 'position']);
        });
        Schema::create('programme_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->timestamps();
            $table->unique(['programme_id', 'user_id']);
        });
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('activity_id')->constrained()->restrictOnDelete();
            $table->json('answers')->nullable();
            $table->unsignedTinyInteger('score')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('saved_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'activity_id']);
        });
        Schema::create('completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('activity_id')->constrained()->restrictOnDelete();
            $table->timestamp('completed_at');
            $table->unique(['user_id', 'activity_id']);
        });
    }

    public function down(): void
    {
        foreach (['completions', 'attempts', 'programme_user', 'activities', 'programmes'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('teacher_id');
            $table->dropColumn('role');
        });
    }
};
