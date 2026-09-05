<?php

namespace Tests\Feature\Incidents;

use App\Models\Incident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IncidentDetailTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_shows_an_incident_with_its_history(): void
    {
        $incident = Incident::factory()->create();
        $incident->history()->create([
            'previous_status' => 'Open',
            'new_status' => 'In Progress',
        ]);

        $response = $this->getJson("/api/incidents/{$incident->id}");

        $response->assertOk();
        $response->assertJsonPath('id', $incident->id);
        $response->assertJsonCount(1, 'history');
        $response->assertJsonPath('history.0.new_status', 'In Progress');
    }

    #[Test]
    public function it_returns_404_when_incident_does_not_exist(): void
    {
        $response = $this->getJson('/api/incidents/999999');

        $response->assertNotFound();
        $response->assertJson(['message' => 'Incidente nao encontrado.']);
    }
}
