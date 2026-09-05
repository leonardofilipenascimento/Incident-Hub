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
        return DB::transaction(function () use ($data) {
            $incident = Incident::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'severity' => $data['severity'],
                'status' => IncidentStatus::Open,
            ]);

            foreach ($data['affected_systems'] as $systemName) {
                $incident->affectedSystems()->create(['name' => $systemName]);
            }

            return $incident->load('affectedSystems');
        });
    }

    public function listIncidents(array $filters): Collection
    {
        $query = Incident::query()->with('affectedSystems')->latest('created_at');

        if (! empty($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($inner) use ($search) {
                $inner->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    public function updateIncidentStatus(Incident $incident, IncidentStatus $newStatus, ?string $comment): Incident
    {
        $error = $this->validateStatusTransition($incident, $newStatus);

        if ($error !== null) {
            throw ValidationException::withMessages(['status' => [$error]]);
        }

        return DB::transaction(function () use ($incident, $newStatus, $comment) {
            $incident->history()->create([
                'previous_status' => $incident->status,
                'new_status' => $newStatus,
                'comment' => $comment,
            ]);

            $incident->update(['status' => $newStatus]);

            return $incident->refresh()->load(['affectedSystems', 'history']);
        });
    }

    public function updateIncidentSeverity(Incident $incident, IncidentSeverity $newSeverity, ?string $comment): Incident
    {
        if ($incident->isClosed()) {
            throw ValidationException::withMessages([
                'severity' => ['Incidentes fechados nao podem sofrer alteracoes.'],
            ]);
        }

        return DB::transaction(function () use ($incident, $newSeverity, $comment) {
            $incident->history()->create([
                'previous_severity' => $incident->severity,
                'new_severity' => $newSeverity,
                'comment' => $comment,
            ]);

            $incident->update(['severity' => $newSeverity]);

            return $incident->refresh()->load(['affectedSystems', 'history']);
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

    private function validateStatusTransition(Incident $incident, IncidentStatus $newStatus): ?string
    {
        if ($incident->isClosed()) {
            return 'Incidentes fechados nao podem sofrer alteracoes.';
        }

        if ($this->isCriticalIncident($incident)
            && $incident->status === IncidentStatus::Open
            && $newStatus === IncidentStatus::Resolved
        ) {
            return 'Incidentes criticos devem passar por In Progress antes de serem resolvidos.';
        }

        if ($newStatus === IncidentStatus::Closed && $incident->status !== IncidentStatus::Resolved) {
            return 'Incidentes so podem ser fechados a partir do status Resolved.';
        }

        return null;
    }
}
