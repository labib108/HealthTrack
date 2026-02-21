<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Condition context: chronic conditions assigned to a user (e.g. diabetes type 1/2, hypertension).
 */
class UserCondition extends Model
{
    protected $fillable = [
        'user_id',
        'condition_type',
        'diagnosed_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'diagnosed_at' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'user_condition_id');
    }
}
