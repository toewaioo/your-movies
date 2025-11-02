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
        Schema::create('movies', function (Blueprint $table) {
             $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('synopsis')->nullable();
            $table->date('release_date')->nullable();
            $table->unsignedInteger('runtime')->nullable()->comment('in minutes');
            $table->float('rating')->default(0);
            $table->string('poster_url')->nullable();
            $table->string('backdrop_url')->nullable();
            $table->json('links')->nullable();
            $table->boolean('is_vip')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedBigInteger('views')->default(0);
            $table->string('director')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['release_date', 'deleted_at']);
            $table->index(['is_vip', 'deleted_at']);
             $table->index(['is_featured', 'deleted_at']);
            $table->index(['rating', 'deleted_at']);
            $table->fullText(['title', 'synopsis', 'director']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
