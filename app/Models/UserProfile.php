<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'profile_photo',
        'date_of_birth',
        'gender',
        'height_cm',
        'weight_kg',
        'activity_level',
        'smoking_status',
        'address_line',
        'city',
        'country',
        'emergency_contact_name',
        'emergency_contact_phone',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
