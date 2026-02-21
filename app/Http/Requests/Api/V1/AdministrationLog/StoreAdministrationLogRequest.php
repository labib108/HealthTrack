<?php

namespace App\Http\Requests\Api\V1\AdministrationLog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdministrationLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userPrescriptionIds = $this->user()->prescriptions()->pluck('id')->all();

        return [
            'prescription_id' => ['required', 'integer', Rule::in($userPrescriptionIds)],
            'taken_at' => ['required', 'date', 'before_or_equal:now'],
            'dose' => ['required', 'string', 'max:100'],
            'glucose_reading_id' => ['nullable', 'integer', 'exists:glucose_readings,id', Rule::in($this->user()->glucoseReadings()->pluck('id')->all())],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->glucose_reading_id) {
            $this->merge(['glucose_reading_id' => (int) $this->glucose_reading_id]);
        }
    }
}
