<?php
// app/Http/Requests/SeriesStoreRequest.php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class SeriesStoreRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:series,slug',
            'synopsis' => 'nullable|string',
            'status' => 'required|in:ongoing,ended,upcoming',
            'poster_url' => 'nullable|url|max:500',
            'backdrop_url' => 'nullable|url|max:500',
            'is_vip' => 'boolean',
            'actors' => 'nullable|array',
            'actors.*.id' => 'required|exists:actors,id',
            'actors.*.character_name' => 'required|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'genres' => 'nullable|array',
            'genres.*' => 'exists:genres,id',
        ];
    }
}
