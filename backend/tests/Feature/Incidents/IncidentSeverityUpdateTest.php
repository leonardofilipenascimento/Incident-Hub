<?php

namespace Tests\Feature\Incidents;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IncidentSeverityUpdateTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_updates_the_severity_of_an_incident(): void
    {
        $incident = Incident::factory()->withSeverity(IncidentSeverity::Medium)->create();

        $response = $this->patchJson("/api/incidents/{$incident->id}/severity", [
            'severity' => 'High',
        ]);

        $response->assertOk();
        $response->assertJsonPath('severity', 'High');
    }

    #[Test]
    public function it_requires_a_known_severity_value(): void
    {
        $incident = Incident::factory()->create();

        $response = $this->patchJson("/api/incidents/{$incident->id}/severity", [
            'severity' => 'Unknown',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['severity']);
    }

    #[Test]
    public function closed_incident_cannot_have_its_severity_changed(): void
    {
        $incident = Incident::factory()
            ->withStatus(IncidentStatus::Closed)
            ->withSeverity(IncidentSeverity::Low)
            ->create();

        $response = $this->patchJson("/api/incidents/{$incident->id}/severity", [
            'severity' => 'High',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonFragment([
            'severity' => ['Incidentes fechados nao podem sofrer alteracoes.'],
        ]);
    }

    #[Test]
    public function it_records_severity_history_when_severity_changes_successfully(): void
    {
        $incident = Incident::factory()->withSeverity(IncidentSeverity::Medium)->create();

        $this->patchJson("/api/incidents/{$incident->id}/severity", [
            'severity' => 'High',
        ])->assertOk();

        $this->assertDatabaseHas('incident_status_histories', [
            'incident_id' => $incident->id,
            'previous_severity' => 'Medium',
            'new_severity' => 'High',
            'previous_status' => null,
            'new_status' => null,
        ]);
    }
}
