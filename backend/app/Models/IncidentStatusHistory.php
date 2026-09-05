<?php

namespace App\Models;

use App\Enums\IncidentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentStatusHistory extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'incident_id',
        'previous_status',
        'new_status',
    ];

    protected $casts = [
        'previous_status' => IncidentStatus::class,
        'new_status' => IncidentStatus::class,
    ];

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }
}
