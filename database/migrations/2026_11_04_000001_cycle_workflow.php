<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programmes', function (Blueprint $table) {
            $table->boolean('cycle_workflow')->default(false);
        });
        Schema::create('cycle_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('released_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('released_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cycle_releases');
        Schema::table('programmes', fn (Blueprint $table) => $table->dropColumn('cycle_workflow'));
    }
};
