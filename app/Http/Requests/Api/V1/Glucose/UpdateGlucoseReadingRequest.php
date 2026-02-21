<?php

namespace App\Http\Requests\Api\V1\Glucose;

use App\Enums\Glucose\GlucoseUnit;
use App\Enums\Glucose\MeasurementContext;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGlucoseReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // guarded by auth:sanctum; ownership checked in controller
    }

    public function rules(): array
    {
        return [
            'original_value' => ['sometimes', 'required', 'numeric', 'min:0.5', 'max:1000'],
            'unit' => ['sometimes', 'required', 'string', 'in:' . implode(',', array_column(GlucoseUnit::cases(), 'value'))],
            'measurement_context' => ['nullable', 'string', 'in:' . implode(',', array_column(MeasurementContext::cases(), 'value'))],
            'measured_at' => ['sometimes', 'required', 'date', 'before_or_equal:now'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'measured_at.before_or_equal' => 'Measured timestamp cannot be in the future.',
        ];
    }
}
