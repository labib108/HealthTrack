<?php

namespace App\Events\Rules;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Published when a rule flags uncontrolled condition (e.g. 7-day avg glucose > target).
 * Consumers: alerts, physician dashboard, audit.
 */
class UncontrolledConditionFlagged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly string $conditionType,
        public readonly string $ruleId,
        public readonly int $ruleVersion,
        public readonly array $summary,
        public readonly string $evaluatedAt
    ) {
    }
}
