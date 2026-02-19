<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // single field to keep mobile UX simple
            'login'    => ['required', 'string', 'max:255'], // email or phone
            'password' => ['required', 'string', 'max:255'],
        ];
    }
}
