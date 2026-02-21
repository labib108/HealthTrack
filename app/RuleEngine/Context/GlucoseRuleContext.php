<?php

namespace App\RuleEngine\Context;

use App\RuleEngine\Contracts\RuleContext;

/**
 * Context built from user-scoped data for rule evaluation.
 * Built by listener from DB/cache; aggregates can be filled by Analytics.
 */
class GlucoseRuleContext implements RuleContext
{
    public function __construct(
        private readonly int $userId,
        private readonly ?array $latestGlucoseReading,
        private readonly array $recentGlucoseReadings,
        private readonly array $activePrescriptions,
        private readonly array $recentAdministrationLogs,
        private readonly array $aggregates,
        private readonly array $activeConditions,
    ) {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getLatestGlucoseReading(): ?array
    {
        return $this->latestGlucoseReading;
    }

    public function getRecentGlucoseReadings(): array
    {
        return $this->recentGlucoseReadings;
    }

    public function getActivePrescriptions(): array
    {
        return $this->activePrescriptions;
    }

    public function getRecentAdministrationLogs(): array
    {
        return $this->recentAdministrationLogs;
    }

    public function getAggregates(): array
    {
        return $this->aggregates;
    }

    public function getActiveConditions(): array
    {
        return $this->activeConditions;
    }
}
