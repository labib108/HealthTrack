<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only log of medication actually taken (when, dose, optional link to glucose reading).
 */
class AdministrationLog extends Model
{
    protected $fillable = [
        'user_id',
        'prescription_id',
        'taken_at',
        'dose',
        'glucose_reading_id',
        'notes',
    ];

    protected $casts = [
        'taken_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function glucoseReading(): BelongsTo
    {
        return $this->belongsTo(GlucoseReading::class);
    }
}
