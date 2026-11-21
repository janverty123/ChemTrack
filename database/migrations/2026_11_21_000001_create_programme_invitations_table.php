<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kept separate from programmes so issuing or resetting a code never touches frozen programme content.
        Schema::create('programme_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('code', 16)->unique();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programme_invitations');
    }
};
