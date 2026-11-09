<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('preferences')->nullable();
            $table->string('avatar_path')->nullable();
            $table->string('school')->nullable();
            $table->string('designation')->nullable();
            $table->text('pending_email')->nullable();
            $table->string('pending_email_token_hash', 64)->nullable();
            $table->timestamp('pending_email_expires_at')->nullable();
            $table->text('app_authentication_secret')->nullable();
            $table->text('app_authentication_recovery_codes')->nullable();
        });
        Schema::create('account_security_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('action', 64);
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_security_events');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['preferences', 'avatar_path', 'school', 'designation', 'pending_email', 'pending_email_token_hash', 'pending_email_expires_at', 'app_authentication_secret', 'app_authentication_recovery_codes']));
    }
};
