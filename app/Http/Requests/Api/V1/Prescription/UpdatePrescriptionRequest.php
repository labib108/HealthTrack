<?php

namespace App\Http\Requests\Api\V1\Prescription;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'drug_id' => ['sometimes', 'required', 'integer', 'exists:drugs,id'],
            'user_condition_id' => [
                'nullable',
                'integer',
                'exists:user_conditions,id',
                Rule::in($this->user()->userConditions()->pluck('id')->all()),
            ],
            'dosage' => ['sometimes', 'required', 'string', 'max:100'],
            'schedule' => ['nullable', 'string', 'max:255'],
            'rule_id' => ['nullable', 'string', 'max:64'],
            'started_at' => ['sometimes', 'required', 'date'],
            'ended_at' => ['nullable', 'date', 'after:started_at'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('user_condition_id') && $this->user_condition_id !== null && $this->user_condition_id !== '') {
            $this->merge([
                'user_condition_id' => (int) $this->user_condition_id,
            ]);
        }
    }
}
