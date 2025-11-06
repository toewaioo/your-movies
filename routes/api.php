<?php
// routes/api_v1.php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\MovieController;
use App\Http\Controllers\Api\V1\SeriesController;
use App\Http\Controllers\Api\V1\EpisodeController;
use App\Http\Controllers\Api\V1\ActorController;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\GenreController;
use App\Http\Controllers\Api\V1\HomepageController;
use App\Http\Middleware\Admin;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh-token', [AuthController::class, 'refreshToken']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    // Movies
    // Route::apiResource('movies', MovieController::class)->except(['store', 'update', 'destroy']);
    // Route::prefix('movies')->group(function () {
    //     Route::post('{movie}/restore', [MovieController::class, 'restore']);
    //     Route::post('bulk', [MovieController::class, 'bulk']);
    //     Route::get('export', [MovieController::class, 'export']);
    // });

    // Admin only routes for movies
    // Route::middleware(Admin::class)->prefix('movies')->group(function () {
    //     Route::post('', [MovieController::class, 'store']);
    //     Route::put('{movie}', [MovieController::class, 'update']);
    //     Route::delete('{movie}', [MovieController::class, 'destroy']);
    // });

    // Similar route structure for Series, Episodes, Actors, Tags, Genres...

    // Series
    //Route::apiResource('series', SeriesController::class)->except(['store', 'update', 'destroy']);
    // Route::prefix('series')->group(function () {
    //     Route::get('{series}/episodes', [EpisodeController::class, 'indexBySeries']);
    //     Route::post('{series}/restore', [SeriesController::class, 'restore']);
    // });

    // Route::middleware([Admin::class])->prefix('series')->group(function () {
    //     Route::post('', [SeriesController::class, 'store']);
    //     Route::put('{series}', [SeriesController::class, 'update']);
    //     Route::delete('{series}', [SeriesController::class, 'destroy']);
    // });

    // Episodes
    Route::apiResource('episodes', EpisodeController::class)->except(['store', 'update', 'destroy']);
    Route::prefix('episodes')->group(function () {
        Route::post('{episode}/restore', [EpisodeController::class, 'restore']);
    });

    Route::middleware([Admin::class])->prefix('episodes')->group(function () {
        Route::post('', [EpisodeController::class, 'store']);
        Route::put('{episode}', [EpisodeController::class, 'update']);
        Route::delete('{episode}', [EpisodeController::class, 'destroy']);
    });

    // Tags and Actors (read-only for regular users)
    Route::apiResource('tags', TagController::class)->only(['index', 'show']);
    Route::apiResource('actors', ActorController::class)->only(['index', 'show']);
    Route::apiResource('genres', GenreController::class)->only(['index', 'show']);

    // Admin only for tags and actors
    Route::middleware([Admin::class])->group(function () {
        Route::apiResource('tags', TagController::class)->except(['index', 'show']);
        Route::apiResource('actors', ActorController::class)->except(['index', 'show']);
        Route::apiResource('genres', GenreController::class)->except(['index', 'show']);
    });
});

Route::get('public/movies', [MovieController::class, 'index']);
Route::get('public/movies/{movie}', [MovieController::class, 'show']);
Route::get('public/series', [SeriesController::class, 'index']);
Route::get('homepage', [HomepageController::class, 'index']);
