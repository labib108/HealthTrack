<?php

namespace App\Enums;

enum ConditionType: string
{
    case DiabetesType1 = 'diabetes_type_1';
    case DiabetesType2 = 'diabetes_type_2';
    case Hypertension = 'hypertension';
    case Hyperlipidemia = 'hyperlipidemia';

    public function label(): string
    {
        return match ($this) {
            self::DiabetesType1 => 'Diabetes Type 1',
            self::DiabetesType2 => 'Diabetes Type 2',
            self::Hypertension => 'Hypertension',
            self::Hyperlipidemia => 'Hyperlipidemia',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
