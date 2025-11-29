<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;

class MovieController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user(),
            'status' => session('status'),
        ]);
    }

    /**
     * Display a listing of movies.
     */
    public function index(): Response
    {
        $movies = Movie::where('visibility_status', 'public')
            ->where('status', 'released')
            ->orderBy('release_date', 'desc')
            ->paginate(9);
      

        return Inertia::render('Movies/Index', [
            'movies' => $movies,
        ]);
    }

    /**
     * Display the movie details page
     */
    public function show(string $slug): Response
    {
        $query = Movie::where('slug', $slug)
            ->with([
                'genres',
                'persons.person',
                'ratings' => function ($query) {
                    $query->latest()->limit(100);
                }
            ]);

        if (Auth::check()) {
            $query->with([
                'watchLinks' => function ($query) {
                    $query->active()->orderBy('priority', 'desc');
                },
                'downloadLinks' => function ($query) {
                    $query->active()->orderBy('priority', 'desc');
                }
            ]);
        }

        $movie = $query->firstOrFail();

        // Increment view count
        $movie->incrementViewCount();

        // Get related movies based on shared genres
        $relatedMovies = Movie::whereHas('genres', function ($query) use ($movie) {
            $query->whereIn('genres.id', $movie->genres->pluck('id'));
        })
            ->where('id', '!=', $movie->id)
            ->where('visibility_status', 'public')
            ->where('status', 'released')
            ->with(['genres'])
            ->inRandomOrder()
            ->limit(6)
            ->get();

        // Separate actors, directors, and writers
        $actors = $movie->persons->where('role_type', 'actor')->values();
        $directors = $movie->persons->where('role_type', 'director')->values();
        $writers = $movie->persons->where('role_type', 'writer')->values();

        // Group watch links by quality (only if loaded)
        $watchLinksByQuality = $movie->relationLoaded('watchLinks') ? $movie->watchLinks->groupBy('quality') : [];

        // Group download links by quality (only if loaded)
        $downloadLinksByQuality = $movie->relationLoaded('downloadLinks') ? $movie->downloadLinks->groupBy('quality') : [];

        // Calculate rating distribution
        $ratingDistribution = $movie->ratings
            ->groupBy('rating')
            ->map(fn($group) => $group->count())
            ->toArray();

        // Get user's rating if authenticated
        $userRating = null;
        if (Auth::check()) {
            $userRating = $movie->ratings()
                ->where('user_id', Auth::id())
                ->first();
        }

        return Inertia::render('MovieDetails', [
            'movie' => [
                'id' => $movie->id,
                'title' => $movie->title,
                'original_title' => $movie->original_title,
                'slug' => $movie->slug,
                'description' => $movie->description,
                'release_date' => $movie->release_date?->format('Y-m-d'),
                'release_year' => $movie->release_date?->year,
                'runtime' => $movie->runtime,
                'formatted_runtime' => $movie->formatted_runtime,
                'language' => $movie->language,
                'country' => $movie->country,
                'imdb_id' => $movie->imdb_id,
                'budget' => $movie->budget,
                'revenue' => $movie->revenue,
                'trailer_url' => $movie->trailer_url,
                'poster_url' => $movie->poster_url,
                'banner_url' => $movie->banner_url,
                'rating_average' => (float) $movie->rating_average,
                'rating_count' => $movie->rating_count,
                'age_rating' => $movie->age_rating,
                'is_vip_only' => $movie->is_vip_only,
                'status' => $movie->status,
                'view_count' => $movie->view_count,
                'genres' => $movie->genres,
                'actors' => $actors,
                'directors' => $directors,
                'writers' => $writers,
                'reviews' => $movie->reviews,
            ],
            'watchLinksByQuality' => $watchLinksByQuality,
            'downloadLinksByQuality' => $downloadLinksByQuality,
            'relatedMovies' => $relatedMovies,
            'ratingDistribution' => $ratingDistribution,
            'userRating' => $userRating,
            'isVip' => false,
            'seo' => [
                'title' => $movie->title,
                'description' => substr($movie->description, 0, 160),
                'keywords' => $movie->genres->pluck('name')->join(', ') . ', ' . $movie->title,
                'url' => route('movies.show', $movie->slug),
                'image' => $movie->poster_url,
                'type' => 'video.movie',
                'structuredData' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'Movie',
                    'name' => $movie->title,
                    'alternativeHeadline' => $movie->original_title,
                    'description' => $movie->description,
                    'image' => $movie->poster_url,
                    'datePublished' => $movie->release_date?->format('Y-m-d'),
                    'genre' => $movie->genres->pluck('name')->toArray(),
                    'director' => $directors->map(fn($d) => [
                        '@type' => 'Person',
                        'name' => $d->person?->name
                    ])->filter(fn($d) => $d['name'])->values()->toArray(),
                    'actor' => $actors->map(fn($a) => [
                        '@type' => 'Person',
                        'name' => $a->person?->name
                    ])->filter(fn($a) => $a['name'])->values()->toArray(),
                    'aggregateRating' => $movie->rating_count > 0 ? [
                        '@type' => 'AggregateRating',
                        'ratingValue' => $movie->rating_average,
                        'reviewCount' => $movie->rating_count,
                        'bestRating' => 10,
                        'worstRating' => 1
                    ] : null,
                    'duration' => $movie->runtime ? 'PT' . $movie->runtime . 'M' : null,
                    'inLanguage' => $movie->language,
                    'countryOfOrigin' => $movie->country,
                    'trailer' => $movie->trailer_url ? [
                        '@type' => 'VideoObject',
                        'name' => $movie->title . ' Trailer',
                        'url' => $movie->trailer_url
                    ] : null
                ]
            ]
        ]);
    }
}
