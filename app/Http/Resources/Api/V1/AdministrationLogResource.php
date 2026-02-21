<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdministrationLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'prescription_id' => $this->prescription_id,
            'prescription' => $this->whenLoaded('prescription', fn () => new PrescriptionResource($this->prescription)),
            'taken_at' => $this->taken_at?->toIso8601String(),
            'dose' => $this->dose,
            'glucose_reading_id' => $this->glucose_reading_id,
            'glucose_reading' => $this->whenLoaded('glucoseReading', fn () => new GlucoseReadingResource($this->glucoseReading)),
            'notes' => $this->notes,
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
