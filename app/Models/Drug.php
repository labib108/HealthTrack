<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Medication master data: drug definitions (shared, not per-user).
 */
class Drug extends Model
{
    protected $fillable = [
        'name',
        'form',
        'unit',
        'therapeutic_class',
        'insulin_type',
        'interaction_group',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }
}
