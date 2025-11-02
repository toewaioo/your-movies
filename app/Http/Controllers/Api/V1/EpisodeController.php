<?php
// app/Http/Controllers/Api/V1/EpisodeController.php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\EpisodeStoreRequest;
use App\Http\Requests\EpisodeUpdateRequest;
use App\Http\Resources\V1\EpisodeCollection;
use App\Http\Resources\V1\EpisodeResource;
use App\Models\Episode;
use App\Models\Series;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
class EpisodeController extends Controller
{
    public function index(Request $request): EpisodeCollection
    {
        $cacheKey = 'episodes:' . md5(serialize($request->all()));
        
        $episodes = Cache::remember($cacheKey, 3600, function () use ($request) {
            $query = Episode::with(['series'])
                ->when($request->series_id, function ($query, $seriesId) {
                    $query->where('series_id', $seriesId);
                })
                ->when($request->season, function ($query, $season) {
                    $query->where('season', $season);
                })
                ->when($request->q, function ($query, $search) {
                    $query->where('title', 'like', "%{$search}%")
                          ->orWhere('synopsis', 'like', "%{$search}%");
                });

            // Sorting
            $sortField = 'episode_number';
            $sortDirection = 'asc';
            
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

            $allowedSorts = ['episode_number', 'season', 'release_date', 'title', 'runtime'];
            if (in_array($sortField, $allowedSorts)) {
                $query->orderBy($sortField, $sortDirection);
            }

            $perPage = min($request->get('per_page', 20), 100);
            return $query->paginate($perPage);
        });

        return new EpisodeCollection($episodes);
    }

    public function indexBySeries(Request $request, Series $series): EpisodeCollection
    {
        $cacheKey = "series:{$series->id}:episodes:" . md5(serialize($request->all()));
        
        $episodes = Cache::remember($cacheKey, 3600, function () use ($request, $series) {
            $query = $series->episodes()
                ->when($request->season, function ($query, $season) {
                    $query->where('season', $season);
                })
                ->orderBy('season')
                ->orderBy('episode_number');

            $perPage = min($request->get('per_page', 20), 100);
            return $query->paginate($perPage);
        });

        return new EpisodeCollection($episodes);
    }

    public function show(Request $request, Episode $episode): EpisodeResource
    {
        $episode->load(['series']);

        return new EpisodeResource($episode);
    }

    public function store(EpisodeStoreRequest $request): EpisodeResource|JsonResponse
    {
        $this->authorize('create', Episode::class);

        // Check for unique episode in series/season
        $existingEpisode = Episode::where('series_id', $request->series_id)
            ->where('season', $request->season)
            ->where('episode_number', $request->episode_number)
            ->first();

        if ($existingEpisode) {
            return $this->error('Episode already exists in this series and season', 422);
        }

        $episode = Episode::create($request->validated());

        Cache::tags(['episodes', 'series'])->flush();

        return new EpisodeResource($episode->load(['series']));
    }

    public function update(EpisodeUpdateRequest $request, Episode $episode): EpisodeResource|JsonResponse
    {
        $this->authorize('update', $episode);

        // Check for unique episode if series_id, season, or episode_number is being updated
        if ($request->hasAny(['series_id', 'season', 'episode_number'])) {
            $existingEpisode = Episode::where('series_id', $request->series_id ?? $episode->series_id)
                ->where('season', $request->season ?? $episode->season)
                ->where('episode_number', $request->episode_number ?? $episode->episode_number)
                ->where('id', '!=', $episode->id)
                ->first();

            if ($existingEpisode) {
                return $this->error('Episode already exists in this series and season', 422);
            }
        }

        $episode->update($request->validated());

        Cache::tags(['episodes', 'series'])->flush();

        return new EpisodeResource($episode->load(['series']));
    }

    public function destroy(Episode $episode): JsonResponse
    {
        $this->authorize('delete', $episode);

        $episode->delete();

        Cache::tags(['episodes', 'series'])->flush();

        return $this->noContent();
    }

    public function restore($id): JsonResponse
    {
        $episode = Episode::withTrashed()->findOrFail($id);
        
        $this->authorize('restore', $episode);

        $episode->restore();

        Cache::tags(['episodes', 'series'])->flush();

        return $this->success(new EpisodeResource($episode->load(['series'])), 'Episode restored successfully');
    }
}