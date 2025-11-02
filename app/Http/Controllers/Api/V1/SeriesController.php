<?php
// app/Http/Controllers/Api/V1/SeriesController.php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\SeriesStoreRequest;
use App\Http\Requests\SeriesUpdateRequest;
use App\Http\Resources\V1\SeriesCollection;
use App\Http\Resources\V1\SeriesResource;
use App\Models\Series;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
class SeriesController extends Controller
{
    public function index(Request $request): SeriesCollection
    {
        $cacheKey = 'series:' . md5(serialize($request->all()));
        
        $series = Cache::remember($cacheKey, 3600, function () use ($request) {
            $query = Series::with(['actors', 'tags', 'genres', 'episodes'])
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
                ->when($request->status, function ($query, $status) {
                    $query->byStatus($status);
                })
                ->when($request->has('is_vip'), function ($query) use ($request) {
                    $query->vip($request->boolean('is_vip'));
                });

            // Sorting
            $sortField = 'created_at';
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

            $allowedSorts = ['title', 'status', 'created_at', 'updated_at'];
            if (in_array($sortField, $allowedSorts)) {
                $query->orderBy($sortField, $sortDirection);
            }

            $perPage = min($request->get('per_page', 20), 100);
            return $query->paginate($perPage);
        });

        return new SeriesCollection($series);
    }

    public function show(Request $request, Series $series): SeriesResource
    {
        $series->load(['actors', 'tags', 'genres', 'episodes']);

        return new SeriesResource($series);
    }

    public function store(SeriesStoreRequest $request): SeriesResource
    {
        $this->authorize('create', Series::class);

        $series = DB::transaction(function () use ($request) {
            $series = Series::create($request->validated());

            // Sync actors with character names
            if ($request->has('actors')) {
                $actorData = [];
                foreach ($request->actors as $actor) {
                    $actorData[$actor['id']] = ['character_name' => $actor['character_name']];
                }
                $series->actors()->sync($actorData);
            }

            // Sync tags and genres
            if ($request->has('tags')) {
                $series->tags()->sync($request->tags);
            }

            if ($request->has('genres')) {
                $series->genres()->sync($request->genres);
            }

            return $series->load(['actors', 'tags', 'genres', 'episodes']);
        });

        Cache::tags(['series'])->flush();

        return new SeriesResource($series);
    }

    public function update(SeriesUpdateRequest $request, Series $series): SeriesResource
    {
        $this->authorize('update', $series);

        $series = DB::transaction(function () use ($request, $series) {
            $series->update($request->validated());

            // Sync actors if provided
            if ($request->has('actors')) {
                $actorData = [];
                foreach ($request->actors as $actor) {
                    $actorData[$actor['id']] = ['character_name' => $actor['character_name']];
                }
                $series->actors()->sync($actorData);
            }

            // Sync tags and genres if provided
            if ($request->has('tags')) {
                $series->tags()->sync($request->tags);
            }

            if ($request->has('genres')) {
                $series->genres()->sync($request->genres);
            }

            return $series->load(['actors', 'tags', 'genres', 'episodes']);
        });

        Cache::tags(['series'])->flush();

        return new SeriesResource($series);
    }

    public function destroy(Series $series): JsonResponse
    {
        $this->authorize('delete', $series);

        $series->delete();

        Cache::tags(['series'])->flush();

        return $this->noContent();
    }

    public function restore($id): JsonResponse
    {
        $series = Series::withTrashed()->findOrFail($id);
        
        $this->authorize('restore', $series);

        $series->restore();

        Cache::tags(['series'])->flush();

        return $this->success(new SeriesResource($series->load(['actors', 'tags', 'genres', 'episodes'])), 'Series restored successfully');
    }

    public function bulk(Request $request): JsonResponse
    {
        $this->authorize('bulk', Series::class);

        $request->validate([
            'series' => 'required|array',
            'series.*.title' => 'required|string|max:255',
            'series.*.slug' => 'nullable|string|max:255',
            'series.*.status' => 'required|in:ongoing,ended,upcoming',
        ]);

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($request->series as $seriesData) {
            if (isset($seriesData['id'])) {
                $series = Series::find($seriesData['id']);
                if ($series) {
                    $series->update($seriesData);
                    $updatedCount++;
                }
            } else {
                Series::create($seriesData);
                $createdCount++;
            }
        }

        Cache::tags(['series'])->flush();

        return $this->success([
            'created' => $createdCount,
            'updated' => $updatedCount,
        ], "Bulk operation completed: {$createdCount} created, {$updatedCount} updated");
    }
}