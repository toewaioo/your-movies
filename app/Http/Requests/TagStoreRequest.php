<?php
// app/Http/Requests/TagStoreRequest.php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
class TagStoreRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:tags,name',
            'slug' => 'nullable|string|max:255|unique:tags,slug',
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