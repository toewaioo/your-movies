<?php
// app/Http/Requests/TagUpdateRequest.php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class TagUpdateRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('tags', 'slug')->ignore($this->route('tag'))
            ],
        ];
    }
}