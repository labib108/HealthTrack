<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DrugResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'form' => $this->form,
            'unit' => $this->unit,
            'therapeutic_class' => $this->therapeutic_class,
            'insulin_type' => $this->insulin_type,
            'interaction_group' => $this->interaction_group,
        ];
    }
}
