<?php
// app/Http/Resources/V1/GenreResource.php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GenreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'movies_count' => $this->whenCounted('movies'),
            'series_count' => $this->whenCounted('series'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'movies' => MovieResource::collection($this->whenLoaded('movies')),
            'series' => SeriesResource::collection($this->whenLoaded('series')),
        ];
    }
}