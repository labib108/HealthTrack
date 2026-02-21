<?php

namespace App\RuleEngine\Contracts;

/**
 * Immutable context passed into the rule engine for evaluation.
 * Built by listeners from user-scoped data (readings, prescriptions, logs, aggregates).
 */
interface RuleContext
{
    public function getUserId(): int;

    /** Latest glucose reading (e.g. the one that triggered evaluation) */
    public function getLatestGlucoseReading(): ?array;

    /** Recent glucose readings for trend/aggregate rules (e.g. last 7 days) */
    public function getRecentGlucoseReadings(): array;

    /** Active prescriptions for the user (and optionally filtered by condition) */
    public function getActivePrescriptions(): array;

    /** Recent administration logs (e.g. last 24–48 hours) */
    public function getRecentAdministrationLogs(): array;

    /** Precomputed aggregates (7-day avg, time-in-range, etc.) when available */
    public function getAggregates(): array;

    /** User's active conditions (e.g. diabetes type 1, hypertension) */
    public function getActiveConditions(): array;
}
