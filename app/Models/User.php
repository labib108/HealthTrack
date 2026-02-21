<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'timezone',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'is_active'         => 'boolean',
    ];

    public function setPasswordAttribute($value)
    {
        if (!empty($value) && !str_starts_with($value, '$2y$')) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function glucoseReadings()
    {
        return $this->hasMany(GlucoseReading::class);
    }
}
