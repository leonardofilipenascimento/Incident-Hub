<?php

namespace App\Services;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IncidentService
{
    public function createIncident(array $data): Incident
    {
        return Incident::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'severity' => $data['severity'],
            'owner' => $data['owner'],
            'status' => IncidentStatus::Open,
        ]);
    }

    public function listIncidents(array $filters): Collection
    {
        $query = Incident::query()->latest('created_at');

        if (! empty($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->get();
    }

    public function updateIncidentStatus(Incident $incident, IncidentStatus $newStatus): Incident
    {
        $error = $this->validateStatusTransition($incident, $newStatus);

        if ($error !== null) {
            throw ValidationException::withMessages(['status' => [$error]]);
        }

        return DB::transaction(function () use ($incident, $newStatus) {
            $incident->history()->create([
                'previous_status' => $incident->status,
                'new_status' => $newStatus,
            ]);

            $incident->update(['status' => $newStatus]);

            return $incident->refresh()->load('history');
        });
    }

    public function canChangeStatus(Incident $incident, IncidentStatus $newStatus): bool
    {
        return $this->validateStatusTransition($incident, $newStatus) === null;
    }

    public function isCriticalIncident(Incident $incident): bool
    {
        return $incident->severity === IncidentSeverity::Critical;
    }

    public function countOpenIncidents(): int
    {
        return Incident::query()->where('status', IncidentStatus::Open)->count();
    }

    public function countUnresolvedCriticalIncidents(): int
    {
        return Incident::query()
            ->where('severity', IncidentSeverity::Critical)
            ->where('status', '!=', IncidentStatus::Resolved)
            ->count();
    }

    public function countResolvedIncidents(): int
    {
        return Incident::query()->where('status', IncidentStatus::Resolved)->count();
    }

    private function validateStatusTransition(Incident $incident, IncidentStatus $newStatus): ?string
    {
        if ($this->isCriticalIncident($incident)
            && $incident->status === IncidentStatus::Open
            && $newStatus === IncidentStatus::Resolved
        ) {
            return 'Um incidente Critical nao pode passar diretamente de Open para Resolved. E necessario passar por In Progress.';
        }

        return null;
    }
}
