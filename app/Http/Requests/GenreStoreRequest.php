<?php
// app/Http/Requests/GenreStoreRequest.php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class GenreStoreRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:genres,name',
            'slug' => 'nullable|string|max:255|unique:genres,slug',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->slug && $this->name) {
            $this->merge([
                'slug' => Str::slug($this->name)
            ]);
        }
    }
}
