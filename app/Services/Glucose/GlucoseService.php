<?php

namespace App\Services\Glucose;

use App\Enums\Glucose\ClinicalClassification;
use App\Enums\Glucose\GlucoseSource;
use App\Enums\Glucose\GlucoseUnit;
use App\Enums\Glucose\MeasurementContext;
use App\Models\GlucoseReading;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GlucoseService
{
    /** Conversion factor: 1 mmol/L = 18.0182 mg/dL */
    private const MMOLL_TO_MGDL = 18.0182;

    /** Biologically plausible bounds (mg/dL) */
    private const MIN_MG_DL = 10;
    private const MAX_MG_DL = 600;

    /** mmol/L bounds (approximately) */
    private const MIN_MMOL_L = 0.6;
    private const MAX_MMOL_L = 33.3;

    /**
     * Normalize value from any unit to mg/dL
     */
    public function normalizeToMgDl(float $value, GlucoseUnit $unit): float
    {
        return match ($unit) {
            GlucoseUnit::MgDl => round($value, 2),
            GlucoseUnit::MmolL => round($value * self::MMOLL_TO_MGDL, 2),
        };
    }

    /**
     * Validate biologically plausible range (in mg/dL)
     */
    public function validateBiologicallyPossible(float $valueMgDl): void
    {
        if ($valueMgDl < self::MIN_MG_DL || $valueMgDl > self::MAX_MG_DL) {
            throw ValidationException::withMessages([
                'value' => ['Blood glucose value is outside biologically plausible range (10–600 mg/dL / 0.6–33.3 mmol/L).'],
            ]);
        }
    }

    /**
     * Context-aware clinical classification per ADA guidelines
     */
    public function classify(float $valueMgDl, MeasurementContext $context): ClinicalClassification
    {
        if ($valueMgDl < 54) {
            return ClinicalClassification::SevereHypoglycemia;
        }
        if ($valueMgDl < 70) {
            return ClinicalClassification::Hypoglycemia;
        }

        $normalHigh = $this->getNormalHighForContext($context);
        if ($valueMgDl <= $normalHigh) {
            return ClinicalClassification::Normal;
        }
        if ($valueMgDl <= 250) {
            return ClinicalClassification::Elevated;
        }
        if ($valueMgDl <= 400) {
            return ClinicalClassification::Hyperglycemia;
        }

        return ClinicalClassification::CriticalHyperglycemia;
    }

    private function getNormalHighForContext(MeasurementContext $context): float
    {
        return match ($context) {
            MeasurementContext::Fasting => 99,
            MeasurementContext::BeforeMeal => 99,
            MeasurementContext::AfterMeal => 180,
            MeasurementContext::Bedtime => 140,
            MeasurementContext::Random => 180,
        };
    }

    /**
     * Determine if reading requires alert (severe hypo or critical hyper)
     */
    public function requiresAlert(ClinicalClassification $classification): bool
    {
        return $classification->isCritical();
    }

    /**
     * Process and create a glucose reading with full business logic
     */
    public function createReading(User $user, array $data): GlucoseReading
    {
        $unit = isset($data['unit'])
            ? GlucoseUnit::from($data['unit'])
            : GlucoseUnit::MgDl;

        $valueMgDl = $this->normalizeToMgDl(
            (float) $data['original_value'],
            $unit
        );

        $this->validateBiologicallyPossible($valueMgDl);

        $context = isset($data['measurement_context'])
            ? MeasurementContext::from($data['measurement_context'])
            : MeasurementContext::Random;

        $classification = $this->classify($valueMgDl, $context);
        $requiresAlert = $this->requiresAlert($classification);

        $reading = DB::transaction(function () use ($user, $data, $valueMgDl, $unit, $context, $classification, $requiresAlert) {
            return GlucoseReading::create([
                'user_id' => $user->id,
                'original_value' => $data['original_value'],
                'unit' => $unit,
                'value_mg_dl' => $valueMgDl,
                'measurement_context' => $context,
                'clinical_classification' => $classification,
                'measured_at' => $data['measured_at'],
                'requires_alert' => $requiresAlert,
                'notes' => $data['notes'] ?? null,
                'source' => isset($data['source']) ? GlucoseSource::from($data['source']) : GlucoseSource::Manual,
            ]);
        });

        return $reading;
    }

    /**
     * Update an existing reading (user-scoped)
     */
    public function updateReading(GlucoseReading $reading, array $data): GlucoseReading
    {
        $unit = isset($data['unit'])
            ? GlucoseUnit::from($data['unit'])
            : $reading->unit;

        $originalValue = $data['original_value'] ?? $reading->original_value;
        $valueMgDl = $this->normalizeToMgDl((float) $originalValue, $unit);

        $this->validateBiologicallyPossible($valueMgDl);

        $context = isset($data['measurement_context'])
            ? MeasurementContext::from($data['measurement_context'])
            : $reading->measurement_context;

        $classification = $this->classify($valueMgDl, $context);
        $requiresAlert = $this->requiresAlert($classification);

        DB::transaction(function () use ($reading, $data, $originalValue, $valueMgDl, $unit, $context, $classification, $requiresAlert) {
            $reading->update([
                'original_value' => $originalValue,
                'unit' => $unit,
                'value_mg_dl' => $valueMgDl,
                'measurement_context' => $context,
                'clinical_classification' => $classification,
                'measured_at' => $data['measured_at'] ?? $reading->measured_at,
                'requires_alert' => $requiresAlert,
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $reading->notes,
            ]);
        });

        return $reading->fresh();
    }

    /**
     * Get readings for a user, ordered by measured_at
     */
    public function getReadingsForUser(User $user, ?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = GlucoseReading::query()
            ->forUser($user->id)
            ->orderedByMeasuredAt();

        if ($from && $to) {
            $query->measuredBetween($from, $to);
        }

        return $query->get();
    }

    /**
     * Future extension hooks (placeholders for aggregation, TIR, HbA1c, etc.)
     */
    // public function getDailyAggregates(User $user, Carbon $date): array { ... }
    // public function getTimeInRange(User $user, Carbon $from, Carbon $to): array { ... }
    // public function estimateHba1c(User $user, Carbon $from, Carbon $to): ?float { ... }
}
