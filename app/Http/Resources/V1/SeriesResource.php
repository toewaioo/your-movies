<?php
// app/Http/Resources/V1/SeriesResource.php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeriesResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'synopsis' => $this->synopsis,
            'status' => $this->status,
            'poster_url' => $this->poster_url,
            'backdrop_url' => $this->backdrop_url,
            'is_vip' => $this->is_vip,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'episodes_count' => $this->whenLoaded('episodes', function () {
                return $this->episodes->count();
            }),
            'seasons_count' => $this->whenLoaded('episodes', function () {
                return $this->episodes->groupBy('season')->count();
            }),
            'latest_episode' => new EpisodeResource($this->whenLoaded('latestEpisode')),
            'actors' => ActorResource::collection($this->whenLoaded('actors')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'genres' => GenreResource::collection($this->whenLoaded('genres')),
            'episodes' => EpisodeResource::collection($this->whenLoaded('episodes')),
        ];
    }
}