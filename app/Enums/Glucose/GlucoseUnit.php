<?php

namespace App\Enums\Glucose;

enum GlucoseUnit: string
{
    case MgDl = 'mg_dl';
    case MmolL = 'mmol_l';

    public function label(): string
    {
        return match ($this) {
            self::MgDl => 'mg/dL',
            self::MmolL => 'mmol/L',
        };
    }
}
