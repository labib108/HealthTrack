<?php

namespace App\RuleEngine\Contracts;

/**
 * A single versioned rule. Implementations are stateless; version and config
 * are carried by the definition record, not by the class.
 */
interface RuleDefinitionInterface
{
    public function getRuleId(): string;

    public function getVersion(): int;

    /** Human-readable name for logs and UI */
    public function getName(): string;

    /**
     * Evaluate the rule. Returns zero or more suggestion/flag results.
     * Does not persist; caller persists and dispatches events.
     *
     * @return array<int, array{type: 'suggestion'|'flag', payload: array}>
     */
    public function evaluate(RuleContext $context): array;
}
