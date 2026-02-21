<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'first_name'               => $this->first_name,
            'last_name'                => $this->last_name,
            'profile_photo_url'        => $this->profile_photo
                                            ? asset('assets/images/profile/' . $this->profile_photo)
                                            : null,
            'date_of_birth'            => optional($this->date_of_birth)->toDateString(),
            'gender'                   => $this->gender,
            'height_cm'                => $this->height_cm,
            'weight_kg'                => $this->weight_kg,
            'activity_level'           => $this->activity_level,
            'smoking_status'           => $this->smoking_status,
            'address_line'             => $this->address_line,
            'city'                     => $this->city,
            'country'                  => $this->country,
            'emergency_contact_name'   => $this->emergency_contact_name,
            'emergency_contact_phone'  => $this->emergency_contact_phone,
            'updated_at'               => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
