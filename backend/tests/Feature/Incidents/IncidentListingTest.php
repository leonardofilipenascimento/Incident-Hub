<?php

namespace Tests\Feature\Incidents;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IncidentListingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_lists_incidents_ordered_by_created_at_descending(): void
    {
        $oldest = Incident::factory()->create(['created_at' => now()->subDays(2)]);
        $newest = Incident::factory()->create(['created_at' => now()]);
        $middle = Incident::factory()->create(['created_at' => now()->subDay()]);

        $response = $this->getJson('/api/incidents');

        $response->assertOk();
        $response->assertJsonPath('data.0.id', $newest->id);
        $response->assertJsonPath('data.1.id', $middle->id);
        $response->assertJsonPath('data.2.id', $oldest->id);
    }

    #[Test]
    public function it_lists_the_owner_of_each_incident(): void
    {
        Incident::factory()->create(['owner' => 'Ana']);

        $response = $this->getJson('/api/incidents');

        $response->assertOk();
        $response->assertJsonPath('data.0.owner', 'Ana');
    }

    #[Test]
    public function it_filters_incidents_by_severity(): void
    {
        Incident::factory()->withSeverity(IncidentSeverity::Critical)->create();
        Incident::factory()->withSeverity(IncidentSeverity::Low)->create();

        $response = $this->getJson('/api/incidents?severity=Critical');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.severity', 'Critical');
    }

    #[Test]
    public function it_filters_incidents_by_status(): void
    {
        Incident::factory()->withStatus(IncidentStatus::Open)->create();
        Incident::factory()->withStatus(IncidentStatus::InProgress)->create();

        $response = $this->getJson('/api/incidents?status=In Progress');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.status', 'In Progress');
    }
}
