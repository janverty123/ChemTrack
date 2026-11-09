<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('private_identity')->nullable();
            $table->string('identity_hash', 64)->nullable()->unique();
            $table->timestamp('account_completed_at')->nullable();
            // Existing accounts retain their access and history during the controlled rollout.
            $table->boolean('registration_required')->default(false);
        });
        Schema::create('signup_challenges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('invitation_id')->nullable()->constrained('student_invitations');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('email');
            $table->string('session_hash', 64);
            $table->string('code_hash');
            $table->unsignedTinyInteger('failed_attempts')->default(0);
            $table->timestamp('sent_at');
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signup_challenges');
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['identity_hash']);
            $table->dropColumn(['private_identity', 'identity_hash', 'account_completed_at', 'registration_required']);
        });
    }
};
