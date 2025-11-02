<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('series_actor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('series_id')->constrained()->onDelete('cascade');
            $table->foreignId('actor_id')->constrained()->onDelete('cascade');
            $table->string('character_name');
            $table->timestamps();

            $table->unique(['series_id', 'actor_id']);
            $table->index('actor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series_actor');
    }
};
