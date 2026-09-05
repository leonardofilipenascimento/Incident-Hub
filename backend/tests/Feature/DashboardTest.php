<?php

namespace Tests\Feature;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_summarizes_open_critical_and_resolved_incident_counts(): void
    {
        Incident::factory()->withStatus(IncidentStatus::Open)->create();
        Incident::factory()->withStatus(IncidentStatus::Open)->create();

        Incident::factory()
            ->withSeverity(IncidentSeverity::Critical)
            ->withStatus(IncidentStatus::InProgress)
            ->create();

        Incident::factory()
            ->withSeverity(IncidentSeverity::Critical)
            ->withStatus(IncidentStatus::Resolved)
            ->create();

        Incident::factory()->withStatus(IncidentStatus::Resolved)->create();
        Incident::factory()->withStatus(IncidentStatus::Resolved)->create();

        $response = $this->getJson('/api/dashboard');

        $response->assertOk();
        $response->assertExactJson([
            'open_incidents' => 2,
            'unresolved_critical_incidents' => 1,
            'resolved_incidents' => 3,
        ]);
    }
}
