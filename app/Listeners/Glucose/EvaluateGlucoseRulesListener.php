<?php

namespace App\Listeners\Glucose;

use App\Events\Glucose\GlucoseReadingRecorded;
use App\Events\Rules\MedicationSuggestionGenerated;
use App\Events\Rules\UncontrolledConditionFlagged;
use App\Models\GlucoseReading;
use App\RuleEngine\Context\GlucoseRuleContext;
use App\RuleEngine\Contracts\RuleDefinitionInterface;
use App\RuleEngine\Contracts\RuleEngineInterface;
use App\RuleEngine\Rules\SlidingScaleInsulinRule;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Event;

/**
 * Listens to GlucoseReadingRecorded, builds user-scoped context, runs applicable
 * rules, persists evaluation results, and dispatches suggestion/flag events.
 * Decoupled from controllers and persistence details.
 */
class EvaluateGlucoseRulesListener implements ShouldQueue
{
    public function __construct(
        private readonly RuleEngineInterface $ruleEngine
    ) {
    }

    public function handle(GlucoseReadingRecorded $event): void
    {
        $reading = $event->reading;
        $user = $reading->user;
        if (! $user) {
            return;
        }

        $context = $this->buildContext($reading);

        // In production: load applicable rules from rule_definitions (by user conditions, plan)
        $rules = $this->getApplicableRules($user->id);

        $evaluationOutput = $this->ruleEngine->evaluate($context, $rules);

        foreach ($evaluationOutput as $run) {
            // Persist rule_evaluation_results (rule_id, version, input_snapshot, output, evaluated_at)
            // $this->persistEvaluation($user->id, $run);

            foreach ($run['results'] ?? [] as $result) {
                if (($result['type'] ?? '') === 'suggestion') {
                    Event::dispatch(new MedicationSuggestionGenerated(
                        userId: $user->id,
                        ruleId: $run['rule_id'],
                        ruleVersion: $run['version'],
                        suggestionPayload: $result['payload'] ?? [],
                        inputSnapshot: ['reading_id' => $reading->id, 'value_mg_dl' => $reading->value_mg_dl],
                        evaluatedAt: now()->toIso8601String()
                    ));
                }
                if (($result['type'] ?? '') === 'flag') {
                    Event::dispatch(new UncontrolledConditionFlagged(
                        userId: $user->id,
                        conditionType: $result['payload']['condition_type'] ?? 'diabetes',
                        ruleId: $run['rule_id'],
                        ruleVersion: $run['version'],
                        summary: $result['payload'] ?? [],
                        evaluatedAt: now()->toIso8601String()
                    ));
                }
            }
        }
    }

    private function buildContext(GlucoseReading $reading): GlucoseRuleContext
    {
        $user = $reading->user;
        $latest = [
            'id' => $reading->id,
            'value_mg_dl' => $reading->value_mg_dl,
            'measured_at' => $reading->measured_at?->toIso8601String(),
            'clinical_classification' => $reading->clinical_classification?->value,
        ];

        $recent = GlucoseReading::query()
            ->forUser($user->id)
            ->orderedByMeasuredAt()
            ->limit(50)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'value_mg_dl' => $r->value_mg_dl,
                'measured_at' => $r->measured_at?->toIso8601String(),
            ])
            ->all();

        return new GlucoseRuleContext(
            userId: $user->id,
            latestGlucoseReading: $latest,
            recentGlucoseReadings: $recent,
            activePrescriptions: [], // TODO: load from prescriptions
            recentAdministrationLogs: [], // TODO: load from administration_logs
            aggregates: [], // TODO: from Analytics (7-day avg, TIR)
            activeConditions: []  // TODO: load from user_conditions
        );
    }

    /** @return RuleDefinitionInterface[] */
    private function getApplicableRules(int $userId): array
    {
        // TODO: load from rule_definitions + user conditions/treatment plan
        return [
            new SlidingScaleInsulinRule('sliding_scale_insulin', 1, 'Sliding scale insulin', []),
        ];
    }
}
