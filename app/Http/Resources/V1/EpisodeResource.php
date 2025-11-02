<?php
// app/Http/Resources/V1/EpisodeResource.php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EpisodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'series_id' => $this->series_id,
            'season' => $this->season,
            'episode_number' => $this->episode_number,
            'title' => $this->title,
            'synopsis' => $this->synopsis,
            'runtime' => $this->runtime,
            'release_date' => $this->release_date?->toDateString(),
            'links' => $this->links,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'series' => new SeriesResource($this->whenLoaded('series')),
        ];
    }
}