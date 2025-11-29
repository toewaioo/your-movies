<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Series;
use App\Models\Genre;
use App\Models\Person;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        // Fetch featured content (5 movies and 5 series)
        $featuredMovies = Movie::where('status', 'released')
            ->whereNotNull('banner_url')
            ->inRandomOrder()
            ->limit(5)
            ->get()
            ->map(function ($movie) {
                $movie->type = 'movie';
                return $movie;
            });

        $featuredSeries = Series::where('status', '!=', 'upcoming')
            ->whereNotNull('banner_url')
            ->inRandomOrder()
            ->limit(5)
            ->get()
            ->map(function ($series) {
                $series->type = 'series';
                return $series;
            });

        $featured = $featuredMovies->concat($featuredSeries)->shuffle()->values();

        // Fetch latest movies
        $latestMovies = Movie::where('status', 'released')
            ->orderBy('created_at', 'desc')
            ->take(12)
            ->get();

        // Fetch latest series
        $latestSeries = Series::where('status', '!=', 'upcoming')
            ->orderBy('created_at', 'desc')
            ->take(12)
            ->get();

        // Fetch genres with counts (movies + series)
        $genres = Genre::whereHas('movies')
            ->orWhereHas('series')
            ->withCount(['movies', 'series'])
            ->orderBy('name')
            ->limit(12)
            ->get()
            ->map(function ($genre) {
                $genre->total_count = $genre->movies_count + $genre->series_count;
                return $genre;
            });

        // Fetch popular actors (those with most appearances)
        $actors = Person::actors()
            ->whereHas('roles')
            ->withCount('roles')
            ->orderBy('roles_count', 'desc')
            ->limit(12)
            ->get();

        return Inertia::render('Home', [
            'featured' => $featured,
            'latestMovies' => $latestMovies,
            'latestSeries' => $latestSeries,
            'genres' => $genres,
            'actors' => $actors,
            
        ]);
    }
    public function search(Request $request): Response
    {
        $query = $request->input('q');

        if (!$query) {
            return Inertia::render('Search', [
                'results' => [],
                'query' => '',
                'seo' => [
                    'title' => 'Search',
                    'description' => 'Search for movies and series',
                ]
            ]);
        }

        $movies = Movie::where('title', 'like', "%{$query}%")
            ->where('status', 'released')
            ->get()
            ->map(function ($movie) {
                $movie->type = 'movie';
                return $movie;
            });

        $series = Series::where('title', 'like', "%{$query}%")
            ->where('status', '!=', 'upcoming')
            ->get()
            ->map(function ($series) {
                $series->type = 'series';
                return $series;
            });

        $results = $movies->concat($series)->values();

        return Inertia::render('Search', [
            'results' => $results,
            'query' => $query,
            'seo' => [
                'title' => "Search results for '{$query}'",
                'description' => "Search results for '{$query}' on Cineverse",
            ]
        ]);
    }
}
