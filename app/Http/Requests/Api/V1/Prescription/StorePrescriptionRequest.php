<?php

namespace App\Http\Requests\Api\V1\Prescription;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userConditionIds = $this->user()->userConditions()->pluck('id')->all();

        return [
            'drug_id' => ['required', 'integer', 'exists:drugs,id'],
            'user_condition_id' => [
                'nullable',
                'integer',
                'exists:user_conditions,id',
                Rule::in($userConditionIds),
            ],
            'dosage' => ['required', 'string', 'max:100'],
            'schedule' => ['nullable', 'string', 'max:255'],
            'rule_id' => ['nullable', 'string', 'max:64'],
            'started_at' => ['required', 'date', 'before_or_equal:today'],
            'ended_at' => ['nullable', 'date', 'after:started_at'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->user_condition_id) {
            $this->merge([
                'user_condition_id' => (int) $this->user_condition_id,
            ]);
        }
    }
}
