<?php

use App\Http\Controllers\Admin\MovieController as AdminMovieController;
use App\Http\Controllers\SeriesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SeriesController as AdminSeriesController;
use App\Http\Controllers\Admin\GenreController;
use App\Http\Controllers\Admin\PersonController;
use App\Http\Controllers\RatingController;
use Inertia\Inertia;

// Route::get("/",function(){
//     return Inertia::render('NotFound');
// });
// // Public Routes
// // Public Routes
//Route::get('/', [HomeController::class, 'index'])->name('home');
// Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');
// Route::get('/movies/{slug}', [MovieController::class, 'show'])->name('movies.show');
// Route::get('/series', [SeriesController::class, 'index'])->name('series.index');
// Route::get('/series/{slug}', [SeriesController::class, 'show'])->name('series.show');

// // Sitemap
// Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');
// Route::get('/search', [HomeController::class, 'search'])->name('search');


// // Admin web routes (Inertia pages)
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        $stats = [
            'movies' => \App\Models\Movie::count(),
            'series' => \App\Models\Series::count(),
            'genres' => \App\Models\Genre::count(),
            'persons' => \App\Models\Person::count(),
        ];
        return Inertia::render('Admin/Dashboard', compact('stats'));
    })->name('admin.dashboard');
    Route::get('/movies', [AdminMovieController::class, 'index'])->name('admin.movies');

    Route::post('/movies', [AdminMovieController::class, 'store'])->name('admin.movies.store');
    Route::put('/movies/{movie}', [AdminMovieController::class, 'update'])->name('admin.movies.update');
    Route::delete('/movies/{movie}', [AdminMovieController::class, 'destroy'])->name('admin.movies.destroy');
    Route::get('/series', [AdminSeriesController::class, 'index'])->name('admin.series');
    Route::post('/series', [AdminSeriesController::class, 'store'])->name('admin.series.store');
    Route::put('/series/{series}', [AdminSeriesController::class, 'update'])->name('admin.series.update');
    Route::delete('/series/{series}', [AdminSeriesController::class, 'destroy'])->name('admin.series.destroy');
    Route::get('/movies/check-slug', [AdminMovieController::class, 'checkSlug'])->name('admin.movies.check-slug');
    Route::get('/series/check-slug', [AdminSeriesController::class, 'checkSlug'])->name('admin.series.check-slug');

    // Genres
    Route::get('/genres', [GenreController::class, 'index'])->name('admin.genres');
    Route::post('/genres', [GenreController::class, 'store'])->name('admin.genres.store');
    Route::put('/genres/{genre}', [GenreController::class, 'update'])->name('admin.genres.update');
    Route::delete('/genres/{genre}', [GenreController::class, 'destroy'])->name('admin.genres.destroy');

    Route::get('/persons', [PersonController::class, 'index'])->name('admin.persons');
    Route::post('/persons', [PersonController::class, 'store'])->name('admin.persons.store');
    Route::put('/persons/{person}', [PersonController::class, 'update'])->name('admin.persons.update');
    Route::delete('/persons/{person}', [PersonController::class, 'destroy'])->name('admin.persons.destroy');

    Route::get('/ratings', [RatingController::class, 'index'])->name('admin.ratings');
    Route::get('/ratings/{rating}', [RatingController::class, 'show'])->name('admin.ratings.show');
    Route::post('/ratings', [RatingController::class, 'store'])->name('admin.ratings.store');
    Route::put('/ratings/{rating}', [RatingController::class, 'update'])->name('admin.ratings.update');

    Route::get('/download-links', function () {
        return Inertia::render('Admin/DownloadLinks', [
            'downloadLinks' => \App\Models\DownloadLink::all(),
        ]);
    })->name('admin.downloadLinks');

    Route::get('/watch-links', function () {
        return Inertia::render('Admin/WatchLinks', [
            'watchLinks' => \App\Models\WatchLink::all(),
        ]);
    })->name('admin.watchLinks');

    Route::get('/seasons', function () {
        return Inertia::render('Admin/Seasons', [
            'seasons' => \App\Models\Season::all(),
        ]);
    })->name('admin.seasons');

    Route::get('/episodes', function () {
        return Inertia::render('Admin/Episodes', [
            'episodes' => \App\Models\Episode::all(),
        ]);
    })->name('admin.episodes');
});

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

//     // Ratings
//     Route::post('/ratings', [\App\Http\Controllers\RatingController::class, 'store'])->name('ratings.store');
//     Route::put('/ratings/{rating}', [\App\Http\Controllers\RatingController::class, 'update'])->name('ratings.update');
//     Route::delete('/ratings/{rating}', [\App\Http\Controllers\RatingController::class, 'destroy'])->name('ratings.destroy');
// });

require __DIR__ . '/auth.php';


// Fallback route for 404
Route::fallback(function () {
    return Inertia::render('NotFound');
});
