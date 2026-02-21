<?php

namespace App\Listeners\Rules;

use App\Events\Rules\MedicationSuggestionGenerated;
use App\Models\MedicationSuggestion;
use Illuminate\Contracts\Queue\ShouldQueue;

class PersistMedicationSuggestionListener implements ShouldQueue
{
    public function handle(MedicationSuggestionGenerated $event): void
    {
        MedicationSuggestion::create([
            'user_id' => $event->userId,
            'rule_id' => $event->ruleId,
            'rule_version' => $event->ruleVersion,
            'payload' => $event->suggestionPayload,
            'glucose_reading_id' => $event->inputSnapshot['reading_id'] ?? null,
            'evaluated_at' => $event->evaluatedAt,
        ]);
    }
}
