<?php
// app/Http/Resources/V1/MovieResource.php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovieResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'synopsis' => $this->synopsis,
            'release_date' => $this->release_date?->toDateString(),
            'runtime' => $this->runtime,
            'rating' => $this->rating,
            'poster_url' => $this->poster_url,
            'backdrop_url' => $this->backdrop_url,
            //'links' => $this->links,
            'is_vip' => $this->is_vip,
            'views' => $this->views,
            'director' => $this->director,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'actors' => ActorResource::collection($this->whenLoaded('actors')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'genres' => GenreResource::collection($this->whenLoaded('genres')),
        ];
    }
}
