<?php
// app/Http/Requests/ApiRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class ApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Convert empty strings to null
        $input = $this->all();
        foreach ($input as $key => $value) {
            if (is_string($value) && $value === '') {
                $input[$key] = null;
            }
        }
        $this->replace($input);
    }
}