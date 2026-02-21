<?php

namespace App\RuleEngine;

use App\RuleEngine\Contracts\RuleContext;
use App\RuleEngine\Contracts\RuleDefinitionInterface;
use App\RuleEngine\Contracts\RuleEngineInterface;

/**
 * Stateless, modular rule engine. Runs versioned rules against context;
 * does not persist or dispatch events.
 */
class RuleEngine implements RuleEngineInterface
{
    public function evaluate(RuleContext $context, array $rules): array
    {
        $output = [];

        foreach ($rules as $rule) {
            if (! $rule instanceof RuleDefinitionInterface) {
                continue;
            }

            try {
                $results = $rule->evaluate($context);
                $output[] = [
                    'rule_id' => $rule->getRuleId(),
                    'version' => $rule->getVersion(),
                    'name' => $rule->getName(),
                    'results' => $results,
                ];
            } catch (\Throwable $e) {
                // Log and skip; one rule failure must not break others
                report($e);
            }
        }

        return $output;
    }
}
