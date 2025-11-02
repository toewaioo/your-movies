<?php
// app/Http/Controllers/Api/V1/GenreController.php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\GenreStoreRequest;
use App\Http\Requests\GenreUpdateRequest;
use App\Http\Resources\V1\GenreCollection;
use App\Http\Resources\V1\GenreResource;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;

class GenreController extends Controller
{
    public function index(Request $request): GenreCollection
    {
        $cacheKey = 'genres:' . md5(serialize($request->all()));
        
        $genres = Cache::remember($cacheKey, 3600, function () use ($request) {
            $query = Genre::withCount(['movies', 'series'])
                ->when($request->q, function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->orderBy('name');

            $perPage = min($request->get('per_page', 20), 100);
            return $query->paginate($perPage);
        });

        return new GenreCollection($genres);
    }

    public function show(Request $request, Genre $genre): GenreResource
    {
        $loadRelations = $request->get('include', '');
        $relations = array_filter(explode(',', $loadRelations));
        
        $allowedRelations = ['movies', 'series'];
        $relationsToLoad = array_intersect($relations, $allowedRelations);
        
        if (!empty($relationsToLoad)) {
            $genre->load($relationsToLoad);
        }

        return new GenreResource($genre);
    }

    public function store(GenreStoreRequest $request): GenreResource
    {
        $this->authorize('create', Genre::class);

        $genre = Genre::create($request->validated());

        Cache::tags(['genres'])->flush();

        return new GenreResource($genre);
    }

    public function update(GenreUpdateRequest $request, Genre $genre): GenreResource
    {
        $this->authorize('update', $genre);

        $genre->update($request->validated());

        Cache::tags(['genres'])->flush();

        return new GenreResource($genre);
    }

    public function destroy(Genre $genre): JsonResponse
    {
        $this->authorize('delete', $genre);

        // Check if genre has any movies or series
        if ($genre->movies()->exists() || $genre->series()->exists()) {
            return $this->error('Cannot delete genre with associated movies or series', 422);
        }

        $genre->delete();

        Cache::tags(['genres'])->flush();

        return $this->noContent();
    }
}