<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationSuggestion extends Model
{
    protected $fillable = [
        'user_id',
        'rule_id',
        'rule_version',
        'payload',
        'glucose_reading_id',
        'evaluated_at',
        'acknowledged',
    ];

    protected $casts = [
        'payload' => 'array',
        'evaluated_at' => 'datetime',
        'acknowledged' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function glucoseReading(): BelongsTo
    {
        return $this->belongsTo(GlucoseReading::class);
    }
}
