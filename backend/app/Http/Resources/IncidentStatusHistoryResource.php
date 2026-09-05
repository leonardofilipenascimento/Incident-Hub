<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncidentStatusHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'incident_id' => $this->incident_id,
            'previous_status' => $this->previous_status->value,
            'new_status' => $this->new_status->value,
            'created_at' => $this->created_at,
        ];
    }
}
