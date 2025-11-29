<?php
// app/Http/Requests/MovieUpdateRequest.php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class MovieUpdateRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('movies', 'slug')->ignore($this->route('movie'))
            ],
            'synopsis' => 'nullable|string',
            'release_date' => 'nullable|date',
            'runtime' => 'nullable|integer|min:1',
            'rating' => 'nullable|numeric|between:0,10',
            'poster_url' => 'nullable|url|max:500',
            'backdrop_url' => 'nullable|url|max:500',
            'links' => 'nullable|array',
            'links.*.quality' => 'required_with:links|string',
            'links.*.name' => 'required_with:links|string',
            'links.*.platform' => 'required_with:links|string',
            'links.*.link' => 'required_with:links|url',
            'is_vip' => 'boolean',
            'director' => 'nullable|string|max:255',
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