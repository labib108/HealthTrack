<?php

namespace App\Enums\Glucose;

enum GlucoseSource: string
{
    case Manual = 'manual';
    case Cgm = 'cgm';
    case External = 'external';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Manual Entry',
            self::Cgm => 'CGM Device',
            self::External => 'External Health Data',
        };
    }
}
