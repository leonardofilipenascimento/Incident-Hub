<?php

namespace Tests\Feature\Incidents;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IncidentStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_allows_open_to_in_progress_transition(): void
    {
        $incident = Incident::factory()->withStatus(IncidentStatus::Open)->create();

        $response = $this->patchJson("/api/incidents/{$incident->id}/status", [
            'status' => 'In Progress',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'In Progress');
    }

    #[Test]
    public function it_allows_in_progress_to_resolved_transition(): void
    {
        $incident = Incident::factory()->withStatus(IncidentStatus::InProgress)->create();

        $response = $this->patchJson("/api/incidents/{$incident->id}/status", [
            'status' => 'Resolved',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'Resolved');
    }

    #[Test]
    public function critical_incident_cannot_move_directly_from_open_to_resolved(): void
    {
        $incident = Incident::factory()
            ->withSeverity(IncidentSeverity::Critical)
            ->withStatus(IncidentStatus::Open)
            ->create();

        $response = $this->patchJson("/api/incidents/{$incident->id}/status", [
            'status' => 'Resolved',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonFragment([
            'status' => ['Um incidente Critical nao pode passar diretamente de Open para Resolved. E necessario passar por In Progress.'],
        ]);

        $this->assertDatabaseHas('incidents', [
            'id' => $incident->id,
            'status' => 'Open',
        ]);
    }

    #[Test]
    public function critical_incident_can_be_resolved_after_passing_through_in_progress(): void
    {
        $incident = Incident::factory()
            ->withSeverity(IncidentSeverity::Critical)
            ->withStatus(IncidentStatus::InProgress)
            ->create();

        $response = $this->patchJson("/api/incidents/{$incident->id}/status", [
            'status' => 'Resolved',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'Resolved');
    }

    #[Test]
    public function it_rejects_an_unknown_status_value(): void
    {
        $incident = Incident::factory()->withStatus(IncidentStatus::Open)->create();

        $response = $this->patchJson("/api/incidents/{$incident->id}/status", [
            'status' => 'Closed',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['status']);
    }

    #[Test]
    public function it_records_status_history_when_status_changes_successfully(): void
    {
        $incident = Incident::factory()->withStatus(IncidentStatus::InProgress)->create();

        $this->patchJson("/api/incidents/{$incident->id}/status", [
            'status' => 'Resolved',
        ])->assertOk();

        $this->assertDatabaseHas('incident_status_histories', [
            'incident_id' => $incident->id,
            'previous_status' => 'In Progress',
            'new_status' => 'Resolved',
        ]);
    }

    #[Test]
    public function it_returns_404_when_updating_status_of_a_nonexistent_incident(): void
    {
        $response = $this->patchJson('/api/incidents/999999/status', [
            'status' => 'In Progress',
        ]);

        $response->assertNotFound();
    }
}
