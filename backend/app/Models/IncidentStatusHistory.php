<?php

namespace App\Models;

use App\Enums\IncidentSeverity;
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
        'previous_severity',
        'new_severity',
        'comment',
    ];

    protected $casts = [
        'previous_status' => IncidentStatus::class,
        'new_status' => IncidentStatus::class,
        'previous_severity' => IncidentSeverity::class,
        'new_severity' => IncidentSeverity::class,
    ];

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }
}
