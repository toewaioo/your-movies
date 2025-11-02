<?php
// app/Http/Requests/EpisodeUpdateRequest.php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class EpisodeUpdateRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'series_id' => 'sometimes|exists:series,id',
            'season' => 'sometimes|integer|min:1',
            'episode_number' => 'sometimes|integer|min:1',
            'title' => 'sometimes|string|max:255',
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
}