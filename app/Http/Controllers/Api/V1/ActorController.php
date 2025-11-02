<?php
// app/Http/Controllers/Api/V1/ActorController.php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\ActorStoreRequest;
use App\Http\Requests\ActorUpdateRequest;
use App\Http\Resources\V1\ActorCollection;
use App\Http\Resources\V1\ActorResource;
use App\Models\Actor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
class ActorController extends Controller
{
    public function index(Request $request): ActorCollection
    {
        $cacheKey = 'actors:' . md5(serialize($request->all()));
        
        $actors = Cache::remember($cacheKey, 3600, function () use ($request) {
            $query = Actor::withCount(['movies', 'series'])
                ->when($request->q, function ($query, $search) {
                    $query->search($search);
                })
                ->when($request->sort, function ($query, $sort) {
                    if ($sort === 'movies_count') {
                        $query->orderBy('movies_count', 'desc');
                    } elseif ($sort === 'series_count') {
                        $query->orderBy('series_count', 'desc');
                    } else {
                        $query->orderBy('name', 'asc');
                    }
                }, function ($query) {
                    $query->orderBy('name', 'asc');
                });

            $perPage = min($request->get('per_page', 20), 100);
            return $query->paginate($perPage);
        });

        return new ActorCollection($actors);
    }

    public function show(Request $request, Actor $actor): ActorResource
    {
        $actor->load(['movies', 'series']);

        return new ActorResource($actor);
    }

    public function store(ActorStoreRequest $request): ActorResource
    {
        $this->authorize('create', Actor::class);

        $actor = Actor::create($request->validated());

        Cache::tags(['actors'])->flush();

        return new ActorResource($actor);
    }

    public function update(ActorUpdateRequest $request, Actor $actor): ActorResource
    {
        $this->authorize('update', $actor);

        $actor->update($request->validated());

        Cache::tags(['actors'])->flush();

        return new ActorResource($actor);
    }

    public function destroy(Actor $actor): JsonResponse
    {
        $this->authorize('delete', $actor);

        // Check if actor has any movies or series
        if ($actor->movies()->exists() || $actor->series()->exists()) {
            return $this->error('Cannot delete actor with associated movies or series', 422);
        }

        $actor->delete();

        Cache::tags(['actors'])->flush();

        return $this->noContent();
    }
}