<?php
// app/Http/Requests/ActorStoreRequest.php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ActorStoreRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'profile_url' => 'nullable|url|max:500',
        ];
    }
}
