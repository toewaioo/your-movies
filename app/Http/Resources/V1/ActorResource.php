<?php
// app/Http/Resources/V1/ActorResource.php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'bio' => $this->bio,
            'profile_url' => $this->profile_url,
            'character_name' => $this->whenPivotLoaded('movie_actor', function () {
                return $this->pivot->character_name;
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}