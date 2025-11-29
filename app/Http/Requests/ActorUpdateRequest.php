<?php
// app/Http/Requests/ActorUpdateRequest.php

namespace App\Http\Requests;

class ActorUpdateRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'bio' => 'nullable|string',
            'profile_url' => 'nullable|url|max:500',
        ];
    }
}