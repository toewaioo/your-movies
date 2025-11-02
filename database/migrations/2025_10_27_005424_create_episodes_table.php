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
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('series_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('season');
            $table->unsignedInteger('episode_number');
            $table->string('title');
            $table->text('synopsis')->nullable();
            $table->unsignedInteger('runtime')->nullable();
            $table->date('release_date')->nullable();
            $table->json('links')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['series_id', 'season', 'episode_number']);
            $table->index(['series_id', 'release_date']);
            $table->fullText(['title', 'synopsis']);

            $table->unique(['series_id', 'season', 'episode_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
