<?php
// app/Http/Requests/MovieStoreRequest.php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class MovieStoreRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:movies,slug',
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

    public function messages(): array
    {
        return [
            'links.*.quality.required_with' => 'Each link must have a quality',
            'links.*.name.required_with' => 'Each link must have a name',
            'links.*.platform.required_with' => 'Each link must have a platform',
            'links.*.link.required_with' => 'Each link must have a URL',
            'links.*.link.url' => 'Each link must be a valid URL',
        ];
    }
}
