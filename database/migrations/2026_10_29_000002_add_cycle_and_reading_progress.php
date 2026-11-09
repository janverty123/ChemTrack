<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->unsignedTinyInteger('cycle')->nullable();
            $table->string('document_path')->nullable();
            $table->string('assessment_source')->default('native');
            $table->string('google_form_url', 2048)->nullable();
        });
        Schema::create('reading_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('activity_id')->constrained()->restrictOnDelete();
            $table->string('content_version', 64);
            $table->decimal('position', 5, 2)->default(0);
            $table->decimal('furthest_position', 5, 2)->default(0);
            $table->unsignedSmallInteger('current_page')->nullable();
            $table->unsignedSmallInteger('page_count')->nullable();
            $table->json('viewed_pages')->nullable();
            $table->timestamp('saved_at');
            $table->timestamps();
            $table->unique(['user_id', 'activity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_progress');
        Schema::table('activities', fn (Blueprint $table) => $table->dropColumn(['cycle', 'document_path', 'assessment_source', 'google_form_url']));
    }
};
