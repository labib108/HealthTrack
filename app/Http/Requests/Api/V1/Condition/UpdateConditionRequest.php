<?php

namespace App\Http\Requests\Api\V1\Condition;

use App\Enums\ConditionType;
use Illuminate\Foundation\Http\FormRequest;

class UpdateConditionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'condition_type' => ['sometimes', 'required', 'string', 'in:' . implode(',', ConditionType::values())],
            'diagnosed_at' => ['nullable', 'date', 'before_or_equal:today'],
            'status' => ['nullable', 'string', 'in:active,inactive,resolved'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
