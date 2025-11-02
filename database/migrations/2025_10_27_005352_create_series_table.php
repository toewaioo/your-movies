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
        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('synopsis')->nullable();
            $table->date('release_date')->nullable();
            $table->enum('status', ['ongoing', 'ended', 'upcoming'])->default('upcoming');
            $table->string('poster_url')->nullable();
            $table->float('rating')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->string('backdrop_url')->nullable();
            $table->boolean('is_vip')->default(false);
            $table->softDeletes();
            $table->timestamps();


            $table->index(['release_date', 'deleted_at']);
            $table->index(['status', 'deleted_at']);
            $table->index(['is_vip', 'deleted_at']);
            $table->index(['rating', 'deleted_at']);
            $table->index(['is_featured', 'deleted_at']);
            $table->fullText(['title', 'synopsis']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series');
    }
};
