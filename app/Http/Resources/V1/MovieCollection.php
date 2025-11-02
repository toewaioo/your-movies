<?php
// app/Http/Resources/V1/MovieCollection.php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MovieCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => MovieResource::collection($this->collection),
            'links' => [
                'self' => 'link-value',
            ],
            'meta' => [
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
            ],
        ];
    }
}