<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GlucoseReadingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'original_value' => (float) $this->original_value,
            'unit' => $this->unit?->value ?? $this->unit,
            'value_mg_dl' => (float) $this->value_mg_dl,
            'measurement_context' => $this->measurement_context?->value ?? $this->measurement_context,
            'clinical_classification' => $this->clinical_classification?->value ?? $this->clinical_classification,
            'measured_at' => $this->measured_at?->toIso8601String(),
            'requires_alert' => $this->requires_alert,
            'notes' => $this->notes,
            'source' => $this->source?->value ?? $this->source,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
