<?php

namespace App\Events\Rules;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Published when the rule engine produces a medication adjustment suggestion
 * (e.g. sliding scale insulin dose). Consumers: notifications, audit, UI.
 */
class MedicationSuggestionGenerated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly string $ruleId,
        public readonly int $ruleVersion,
        public readonly array $suggestionPayload,
        public readonly array $inputSnapshot,
        public readonly string $evaluatedAt
    ) {
    }
}
