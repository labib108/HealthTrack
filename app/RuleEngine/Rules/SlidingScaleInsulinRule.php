<?php

namespace App\RuleEngine\Rules;

use App\RuleEngine\Contracts\RuleContext;
use App\RuleEngine\Contracts\RuleDefinitionInterface;

/**
 * Example versioned rule: sliding scale insulin suggestion based on latest glucose.
 * Config (thresholds, doses) would come from rule_definitions.payload in production.
 */
class SlidingScaleInsulinRule implements RuleDefinitionInterface
{
    public function __construct(
        private readonly string $ruleId,
        private readonly int $version,
        private readonly string $name,
        private readonly array $config = []
    ) {
    }

    public function getRuleId(): string
    {
        return $this->ruleId;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function evaluate(RuleContext $context): array
    {
        $reading = $context->getLatestGlucoseReading();
        if (! $reading || empty($reading['value_mg_dl'])) {
            return [];
        }

        $valueMgDl = (float) $reading['value_mg_dl'];
        $scale = $this->config['scale'] ?? [
            ['max' => 150, 'units' => 0],
            ['max' => 200, 'units' => 2],
            ['max' => 250, 'units' => 4],
            ['max' => 300, 'units' => 6],
            ['max' => 999, 'units' => 8],
        ];

        $units = 0;
        foreach ($scale as $tier) {
            if ($valueMgDl <= $tier['max']) {
                $units = $tier['units'];
                break;
            }
        }

        if ($units === 0) {
            return [];
        }

        return [
            [
                'type' => 'suggestion',
                'payload' => [
                    'medication_type' => 'bolus_insulin',
                    'units' => $units,
                    'reason' => 'Sliding scale based on glucose ' . $valueMgDl . ' mg/dL',
                    'glucose_reading_id' => $reading['id'] ?? null,
                ],
            ],
        ];
    }
}
