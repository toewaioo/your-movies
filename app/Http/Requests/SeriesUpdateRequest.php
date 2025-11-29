<?php
// app/Http/Requests/SeriesUpdateRequest.php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class SeriesUpdateRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('series', 'slug')->ignore($this->route('series'))
            ],
            'synopsis' => 'nullable|string',
            'status' => 'sometimes|in:ongoing,ended,upcoming',
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
