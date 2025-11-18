<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Movie;
use App\Models\Series;
use App\Models\Genre;
use App\Models\Actor;
use Inertia\Inertia as InertiaRender;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\EncryptPageController;
use App\Http\Controllers\StreamController;

use App\Http\Controllers\DownloadController;
use Illuminate\Http\Request;
Route::get('/download/{token}', [DownloadController::class, 'download']);

Route::get('/encrypt', function (Request $request) {
    $validPassword = "kairizy"; // <<< change your password here

    if ($request->key !== $validPassword) {
        abort(403, "Access Denied: Invalid key");
    }

    return view('encrypt', [
        'pageKey' => $request->key
    ]);
})->name('encrypt.page');
// Route::get('/encrypt', [EncryptPageController::class, 'index']);
Route::post('/encrypt', [EncryptPageController::class, 'generate']);
Route::get('/stream/{token}', [StreamController::class, 'stream']);


Route::get('/', function () {

    $features = [
        'movies' => Movie::where('is_vip', true)->take(10)->get(),
        'series' => Series::where('is_vip', true)->take(10)->get(),
    ];
    // Debug: dump features to log
    return Inertia::render('Public/HomePage', [
        'movies' => Movie::latest()->take(10)->get(),
        'series' => Series::latest()->take(10)->get(),
        'features' => $features,
    ]);
});
Route::get('/welcome', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});
Route::get('/movies', function () {
    $movies = Movie::latest()->paginate(20);
    return Inertia::render('Public/MoviesPage', ['movies' => $movies]);
});

Route::get('/series', function () {
    $series = Series::latest()->paginate(20);
    return Inertia::render('Public/SeriesPage', ['series' => $series]);
});
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Public detail pages for movies and series (Inertia + React pages)
Route::get('/movies/{movie}', function (Movie $movie) {
    $movie->load(['genres', 'actors']);
    return Inertia::render('Public/MovieDetail', [
        'movie' => $movie,
    ]);
})->name('movies.show');

Route::get('/series/{series}', function (Series $series) {
    $series->load(['genres', 'actors', 'episodes']);
    // Group episodes by season for frontend
    $seasons = $series->seasons;
    return Inertia::render('Public/SeriesDetail', [
        'series' => $series,
        'seasons' => $seasons,
    ]);
})->name('series.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

// Fallback route for 404
Route::fallback(function () {
    return Inertia::render('NotFound');
});
