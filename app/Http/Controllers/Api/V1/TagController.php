<?php
// app/Http/Controllers/Api/V1/TagController.php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\TagStoreRequest;
use App\Http\Requests\TagUpdateRequest;
use App\Http\Resources\V1\TagCollection;
use App\Http\Resources\V1\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;

class TagController extends Controller
{
    public function index(Request $request): TagCollection
    {
        $cacheKey = 'tags:' . md5(serialize($request->all()));
        
        $tags = Cache::remember($cacheKey, 3600, function () use ($request) {
            $query = Tag::withCount(['movies', 'series'])
                ->when($request->q, function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->orderBy('name');

            $perPage = min($request->get('per_page', 20), 100);
            return $query->paginate($perPage);
        });

        return new TagCollection($tags);
    }

    public function show(Request $request, Tag $tag): TagResource
    {
        $loadRelations = $request->get('include', '');
        $relations = array_filter(explode(',', $loadRelations));
        
        $allowedRelations = ['movies', 'series'];
        $relationsToLoad = array_intersect($relations, $allowedRelations);
        
        if (!empty($relationsToLoad)) {
            $tag->load($relationsToLoad);
        }

        return new TagResource($tag);
    }

    public function store(TagStoreRequest $request): TagResource
    {
        $this->authorize('create', Tag::class);

        $tag = Tag::create($request->validated());

        Cache::tags(['tags'])->flush();

        return new TagResource($tag);
    }

    public function update(TagUpdateRequest $request, Tag $tag): TagResource
    {
        $this->authorize('update', $tag);

        $tag->update($request->validated());

        Cache::tags(['tags'])->flush();

        return new TagResource($tag);
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $this->authorize('delete', $tag);

        // Check if tag has any movies or series
        if ($tag->movies()->exists() || $tag->series()->exists()) {
            return $this->error('Cannot delete tag with associated movies or series', 422);
        }

        $tag->delete();

        Cache::tags(['tags'])->flush();

        return $this->noContent();
    }
}