<?php

namespace App\Models;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'severity',
        'status',
    ];

    protected $casts = [
        'severity' => IncidentSeverity::class,
        'status' => IncidentStatus::class,
    ];

    public function affectedSystems(): HasMany
    {
        return $this->hasMany(IncidentAffectedSystem::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(IncidentStatusHistory::class)->oldest('created_at');
    }

    public function isClosed(): bool
    {
        return $this->status === IncidentStatus::Closed;
    }
}
