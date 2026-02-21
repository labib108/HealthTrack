<?php

namespace App\Enums\Glucose;

enum MeasurementContext: string
{
    case Fasting = 'fasting';
    case BeforeMeal = 'before_meal';
    case AfterMeal = 'after_meal';
    case Random = 'random';
    case Bedtime = 'bedtime';

    public function label(): string
    {
        return match ($this) {
            self::Fasting => 'Fasting',
            self::BeforeMeal => 'Before Meal',
            self::AfterMeal => 'After Meal',
            self::Random => 'Random',
            self::Bedtime => 'Bedtime',
        };
    }
}
