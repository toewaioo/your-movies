<?php
// app/Http/Requests/EpisodeStoreRequest.php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class EpisodeStoreRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'series_id' => 'required|exists:series,id',
            'season' => 'required|integer|min:1',
            'episode_number' => 'required|integer|min:1',
            'title' => 'required|string|max:255',
            'synopsis' => 'nullable|string',
            'runtime' => 'nullable|integer|min:1',
            'release_date' => 'nullable|date',
            'links' => 'nullable|array',
            'links.*.quality' => 'required_with:links|string',
            'links.*.name' => 'required_with:links|string',
            'links.*.platform' => 'required_with:links|string',
            'links.*.link' => 'required_with:links|url',
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