<?php

namespace App\Models;

use App\Enums\Glucose\ClinicalClassification;
use App\Enums\Glucose\GlucoseSource;
use App\Enums\Glucose\GlucoseUnit;
use App\Enums\Glucose\MeasurementContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class GlucoseReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'original_value',
        'unit',
        'value_mg_dl',
        'measurement_context',
        'clinical_classification',
        'measured_at',
        'requires_alert',
        'notes',
        'source',
    ];

    protected $casts = [
        'original_value' => 'decimal:2',
        'value_mg_dl' => 'decimal:2',
        'measured_at' => 'datetime',
        'requires_alert' => 'boolean',
        'unit' => GlucoseUnit::class,
        'measurement_context' => MeasurementContext::class,
        'clinical_classification' => ClinicalClassification::class,
        'source' => GlucoseSource::class,
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

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOrderedByMeasuredAt(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->orderBy('measured_at', $direction);
    }

    public function scopeMeasuredBetween(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('measured_at', [$from, $to]);
    }

    public function scopeRequiresAlert(Builder $query): Builder
    {
        return $query->where('requires_alert', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Audit-Friendly Helpers
    |--------------------------------------------------------------------------
    */

    public function isCritical(): bool
    {
        return $this->clinical_classification?->isCritical() ?? false;
    }
}
