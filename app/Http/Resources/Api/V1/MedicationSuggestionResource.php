<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicationSuggestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rule_id' => $this->rule_id,
            'rule_version' => $this->rule_version,
            'payload' => $this->payload,
            'glucose_reading_id' => $this->glucose_reading_id,
            'glucose_reading' => $this->whenLoaded('glucoseReading', fn () => new GlucoseReadingResource($this->glucoseReading)),
            'evaluated_at' => $this->evaluated_at?->toIso8601String(),
            'acknowledged' => $this->acknowledged,
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
