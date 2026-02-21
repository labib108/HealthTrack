<?php

namespace App\RuleEngine\Contracts;

/**
 * Stateless rule engine: given context and applicable rules, runs evaluations
 * and returns results. Persistence and event dispatch are done by the caller.
 */
interface RuleEngineInterface
{
    /**
     * Run all applicable rules for the given context (e.g. rules tied to user's conditions).
     *
     * @param  array<int, RuleDefinitionInterface>  $rules
     * @return array<int, array{rule_id: string, version: int, results: array}>
     */
    public function evaluate(RuleContext $context, array $rules): array;
}
