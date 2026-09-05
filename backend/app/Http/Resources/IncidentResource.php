<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncidentResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'severity' => $this->severity->value,
            'status' => $this->status->value,
            'affected_systems' => $this->affectedSystems->pluck('name')->all(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'history' => IncidentStatusHistoryResource::collection($this->whenLoaded('history')),
        ];
    }
}
