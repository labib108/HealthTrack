<?php

namespace App\Enums\Glucose;

enum ClinicalClassification: string
{
    case SevereHypoglycemia = 'severe_hypoglycemia';
    case Hypoglycemia = 'hypoglycemia';
    case Normal = 'normal';
    case Elevated = 'elevated';
    case Hyperglycemia = 'hyperglycemia';
    case CriticalHyperglycemia = 'critical_hyperglycemia';

    public function label(): string
    {
        return match ($this) {
            self::SevereHypoglycemia => 'Severe Hypoglycemia',
            self::Hypoglycemia => 'Hypoglycemia',
            self::Normal => 'Normal',
            self::Elevated => 'Elevated',
            self::Hyperglycemia => 'Hyperglycemia',
            self::CriticalHyperglycemia => 'Critical Hyperglycemia',
        };
    }

    public function isCritical(): bool
    {
        return $this === self::SevereHypoglycemia || $this === self::CriticalHyperglycemia;
    }
}
