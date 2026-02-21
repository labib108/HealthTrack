<?php

namespace App\Http\Requests\Api\V1\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // guarded by auth:sanctum at route level
    }

    public function rules(): array
    {
        return [
            'first_name'              => ['nullable', 'string', 'max:100'],
            'last_name'               => ['nullable', 'string', 'max:100'],
            'profile_photo'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'date_of_birth'           => ['nullable', 'date', 'before:today'],
            'gender'                  => ['nullable', 'string', 'in:male,female,other'],
            'height_cm'               => ['nullable', 'integer', 'min:50', 'max:300'],
            'weight_kg'               => ['nullable', 'integer', 'min:10', 'max:500'],
            'activity_level'          => ['nullable', 'string', 'in:sedentary,light,moderate,active'],
            'smoking_status'          => ['nullable', 'string', 'in:non_smoker,former,current'],
            'address_line'            => ['nullable', 'string', 'max:255'],
            'city'                    => ['nullable', 'string', 'max:100'],
            'country'                 => ['nullable', 'string', 'max:100'],
            'emergency_contact_name'  => ['nullable', 'string', 'max:100'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
        ];
    }
}
