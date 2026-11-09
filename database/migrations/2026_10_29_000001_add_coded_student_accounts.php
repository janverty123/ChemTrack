<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('student_code', 40)->nullable()->unique();
            $table->char('category', 1)->nullable();
        });
        Schema::create('student_code_counters', function (Blueprint $table) {
            $table->char('category', 1)->primary();
            $table->unsignedBigInteger('last_number')->default(0);
        });
        DB::table('student_code_counters')->insert([
            ['category' => 'G', 'last_number' => 0],
            ['category' => 'B', 'last_number' => 0],
        ]);
        Schema::create('student_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('programme_id')->constrained()->restrictOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->foreignId('used_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_invitations');
        Schema::dropIfExists('student_code_counters');
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['student_code']);
            $table->dropColumn(['student_code', 'category']);
        });
        // Email stays nullable: rolling back must not fabricate identities or lose coded accounts.
    }
};
