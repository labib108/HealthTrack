<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrescriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'drug_id' => $this->drug_id,
            'drug' => $this->whenLoaded('drug', fn () => new DrugResource($this->drug)),
            'user_condition_id' => $this->user_condition_id,
            'condition' => $this->whenLoaded('userCondition', fn () => new UserConditionResource($this->userCondition)),
            'dosage' => $this->dosage,
            'schedule' => $this->schedule,
            'rule_id' => $this->rule_id,
            'started_at' => optional($this->started_at)->toDateString(),
            'ended_at' => optional($this->ended_at)->toDateString(),
            'status' => $this->status ?? 'active',
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
