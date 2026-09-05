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
    public function critical_incident_cannot_move_directly_from_open_to_resolved(): void
    {
        $incident = Incident::factory()
            ->withSeverity(IncidentSeverity::Critical)
            ->withStatus(IncidentStatus::Open)
            ->create();

        $response = $this->patchJson("/api/incidents/{$incident->id}/status", [
            'status' => 'Resolved',
            'comment' => 'Mitigado diretamente',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonFragment([
            'status' => ['Incidentes criticos devem passar por In Progress antes de serem resolvidos.'],
        ]);
    }

    #[Test]
    public function incident_cannot_be_closed_without_passing_through_resolved(): void
    {
        $incident = Incident::factory()->withStatus(IncidentStatus::InProgress)->create();

        $response = $this->patchJson("/api/incidents/{$incident->id}/status", [
            'status' => 'Closed',
            'comment' => 'Fechando direto',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonFragment([
            'status' => ['Incidentes so podem ser fechados a partir do status Resolved.'],
        ]);
    }

    #[Test]
    public function closed_incident_cannot_have_its_status_changed(): void
    {
        $incident = Incident::factory()->withStatus(IncidentStatus::Closed)->create();

        $response = $this->patchJson("/api/incidents/{$incident->id}/status", [
            'status' => 'In Progress',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonFragment([
            'status' => ['Incidentes fechados nao podem sofrer alteracoes.'],
        ]);
    }

    #[Test]
    public function it_requires_comment_when_resolving_an_incident(): void
    {
        $incident = Incident::factory()->withStatus(IncidentStatus::InProgress)->create();

        $response = $this->patchJson("/api/incidents/{$incident->id}/status", [
            'status' => 'Resolved',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonFragment([
            'comment' => ['Comentario e obrigatorio ao transicionar para Resolved.'],
        ]);
    }

    #[Test]
    public function it_requires_comment_when_closing_an_incident(): void
    {
        $incident = Incident::factory()->withStatus(IncidentStatus::Resolved)->create();

        $response = $this->patchJson("/api/incidents/{$incident->id}/status", [
            'status' => 'Closed',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonFragment([
            'comment' => ['Comentario e obrigatorio ao transicionar para Closed.'],
        ]);
    }

    #[Test]
    public function it_records_status_history_when_status_changes_successfully(): void
    {
        $incident = Incident::factory()->withStatus(IncidentStatus::InProgress)->create();

        $this->patchJson("/api/incidents/{$incident->id}/status", [
            'status' => 'Resolved',
            'comment' => 'Causa raiz corrigida e validada em producao',
        ])->assertOk();

        $this->assertDatabaseHas('incident_status_histories', [
            'incident_id' => $incident->id,
            'previous_status' => 'In Progress',
            'new_status' => 'Resolved',
            'comment' => 'Causa raiz corrigida e validada em producao',
        ]);
    }
}
