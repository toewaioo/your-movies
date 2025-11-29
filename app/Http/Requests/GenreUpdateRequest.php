<?php
// app/Http/Requests/GenreUpdateRequest.php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class GenreUpdateRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('genres', 'slug')->ignore($this->route('genre'))
            ],
        ];
    }
}