<?php
// app/Http/Controllers/Api/V1/MovieController.php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\MovieStoreRequest;
use App\Http\Requests\MovieUpdateRequest;
use App\Http\Resources\V1\MovieCollection;
use App\Http\Resources\V1\MovieResource;
use App\Models\Movie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
class MovieController extends Controller
{
    public function index(Request $request): MovieCollection
    {
        $cacheKey = 'movies:' . md5(serialize($request->all()));
        
        $movies = Cache::remember($cacheKey, 3600, function () use ($request) {
            $query = Movie::with(['actors', 'tags', 'genres'])
                ->when($request->q, function ($query, $search) {
                    $query->search($search);
                })
                ->when($request->genre, function ($query, $genre) {
                    $query->whereHas('genres', function ($q) use ($genre) {
                        $q->where('slug', $genre);
                    });
                })
                ->when($request->tag, function ($query, $tag) {
                    $query->whereHas('tags', function ($q) use ($tag) {
                        $q->where('slug', $tag);
                    });
                })
                ->when($request->actor, function ($query, $actor) {
                    $query->whereHas('actors', function ($q) use ($actor) {
                        $q->where('name', 'like', "%{$actor}%");
                    });
                })
                ->when($request->release_year, function ($query, $year) {
                    $query->byYear($year);
                })
                ->when($request->has('is_vip'), function ($query) use ($request) {
                    $query->vip($request->boolean('is_vip'));
                })
                ->when($request->platform, function ($query, $platform) {
                    $query->where('links', 'like', "%\"platform\":\"{$platform}\"%");
                });

            // Sorting
            $sortField = 'release_date';
            $sortDirection = 'desc';
            
            if ($request->sort) {
                $sortParts = explode('|', $request->sort);
                foreach ($sortParts as $part) {
                    if (str_starts_with($part, '-')) {
                        $sortField = substr($part, 1);
                        $sortDirection = 'desc';
                    } else {
                        $sortField = $part;
                        $sortDirection = 'asc';
                    }
                }
            }

            $allowedSorts = ['release_date', 'views', 'rating', 'title', 'created_at'];
            if (in_array($sortField, $allowedSorts)) {
                $query->orderBy($sortField, $sortDirection);
            }

            // Pagination
            $perPage = min($request->get('per_page', 20), 100);
            
            return $query->paginate($perPage);
        });

        return new MovieCollection($movies);
    }

    public function show(Request $request, Movie $movie): MovieResource
    {
        $movie->load(['actors', 'tags', 'genres']);
        
        // Increment views in background
        dispatch(function () use ($movie) {
            $movie->incrementViews();
        });

        return new MovieResource($movie);
    }

    public function store(MovieStoreRequest $request): MovieResource
    {
        $this->authorize('create', Movie::class);

        $movie = DB::transaction(function () use ($request) {
            $movie = Movie::create($request->validated());

            // Sync actors with character names
            if ($request->has('actors')) {
                $actorData = [];
                foreach ($request->actors as $actor) {
                    $actorData[$actor['id']] = ['character_name' => $actor['character_name']];
                }
                $movie->actors()->sync($actorData);
            }

            // Sync tags and genres
            if ($request->has('tags')) {
                $movie->tags()->sync($request->tags);
            }

            if ($request->has('genres')) {
                $movie->genres()->sync($request->genres);
            }

            return $movie->load(['actors', 'tags', 'genres']);
        });

        // Clear cache
        Cache::tags(['movies'])->flush();

        return new MovieResource($movie);
    }

    public function update(MovieUpdateRequest $request, Movie $movie): MovieResource
    {
        $this->authorize('update', $movie);

        $movie = DB::transaction(function () use ($request, $movie) {
            $movie->update($request->validated());

            // Sync actors if provided
            if ($request->has('actors')) {
                $actorData = [];
                foreach ($request->actors as $actor) {
                    $actorData[$actor['id']] = ['character_name' => $actor['character_name']];
                }
                $movie->actors()->sync($actorData);
            }

            // Sync tags and genres if provided
            if ($request->has('tags')) {
                $movie->tags()->sync($request->tags);
            }

            if ($request->has('genres')) {
                $movie->genres()->sync($request->genres);
            }

            return $movie->load(['actors', 'tags', 'genres']);
        });

        // Clear cache
        Cache::tags(['movies'])->flush();

        return new MovieResource($movie);
    }

    public function destroy(Movie $movie): JsonResponse
    {
        $this->authorize('delete', $movie);

        $movie->delete();

        // Clear cache
        Cache::tags(['movies'])->flush();

        return $this->noContent();
    }

    public function restore($id): JsonResponse
    {
        $movie = Movie::withTrashed()->findOrFail($id);
        
        $this->authorize('restore', $movie);

        $movie->restore();

        // Clear cache
        Cache::tags(['movies'])->flush();

        return $this->success(new MovieResource($movie->load(['actors', 'tags', 'genres'])), 'Movie restored successfully');
    }

    public function bulk(Request $request): JsonResponse
    {
        $this->authorize('bulk', Movie::class);

        $request->validate([
            'movies' => 'required|array',
            'movies.*.title' => 'required|string|max:255',
            'movies.*.slug' => 'nullable|string|max:255',
            'movies.*.release_date' => 'nullable|date',
        ]);

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($request->movies as $movieData) {
            if (isset($movieData['id'])) {
                // Update existing movie
                $movie = Movie::find($movieData['id']);
                if ($movie) {
                    $movie->update($movieData);
                    $updatedCount++;
                }
            } else {
                // Create new movie
                Movie::create($movieData);
                $createdCount++;
            }
        }

        // Clear cache
        Cache::tags(['movies'])->flush();

        return $this->success([
            'created' => $createdCount,
            'updated' => $updatedCount,
        ], "Bulk operation completed: {$createdCount} created, {$updatedCount} updated");
    }

    public function export(Request $request): JsonResponse
    {
        $this->authorize('export', Movie::class);

        $movies = Movie::with(['genres', 'tags', 'actors'])
            ->when($request->format === 'csv', function ($query) {
                // Prepare for CSV export
            })
            ->get();

        // In a real application, you would generate and return a file
        // For now, we'll return the data as JSON
        return $this->success(
            MovieResource::collection($movies),
            'Export data retrieved successfully'
        );
    }
}