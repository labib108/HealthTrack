<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['nullable', 'string', 'max:255'],

            // user can register with either email or phone
            'email'    => ['nullable', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:30', 'unique:users,phone'],

            'password' => ['required', 'string', 'min:6', 'max:255'],

            'timezone' => ['nullable', 'string', 'max:64'], // default handled in DB/model
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $email = $this->input('email');
            $phone = $this->input('phone');

            if (empty($email) && empty($phone)) {
                $validator->errors()->add('login', 'Either email or phone is required.');
            }
        });
    }
}
