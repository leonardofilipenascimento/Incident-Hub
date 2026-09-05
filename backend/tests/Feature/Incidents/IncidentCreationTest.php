<?php

namespace Tests\Feature\Incidents;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IncidentCreationTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Payment API instability',
            'description' => 'Payment API returning intermittent errors during checkout.',
            'severity' => 'Critical',
            'owner' => 'Ana',
        ], $overrides);
    }

    #[Test]
    public function it_creates_an_incident_with_open_status(): void
    {
        $response = $this->postJson('/api/incidents', $this->validPayload());

        $response->assertCreated();
        $response->assertJson([
            'title' => 'Payment API instability',
            'description' => 'Payment API returning intermittent errors during checkout.',
            'severity' => 'Critical',
            'owner' => 'Ana',
            'status' => 'Open',
        ]);
        $response->assertJsonStructure(['id', 'created_at', 'updated_at']);

        $this->assertDatabaseHas('incidents', [
            'title' => 'Payment API instability',
            'owner' => 'Ana',
            'status' => 'Open',
        ]);
    }

    #[Test]
    public function it_ignores_client_provided_status_and_forces_open(): void
    {
        $response = $this->postJson('/api/incidents', $this->validPayload(['status' => 'Resolved']));

        $response->assertCreated();
        $response->assertJsonPath('status', 'Open');
    }

    #[Test]
    public function it_requires_title_when_creating_an_incident(): void
    {
        $response = $this->postJson('/api/incidents', $this->validPayload(['title' => '']));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['title']);
    }

    #[Test]
    public function it_requires_title_with_at_least_five_characters(): void
    {
        $response = $this->postJson('/api/incidents', $this->validPayload(['title' => 'Bug']));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['title']);
    }

    #[Test]
    public function it_requires_description_with_at_least_ten_characters(): void
    {
        $response = $this->postJson('/api/incidents', $this->validPayload(['description' => 'curto']));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['description']);
    }

    #[Test]
    public function it_requires_a_known_severity_value(): void
    {
        $response = $this->postJson('/api/incidents', $this->validPayload(['severity' => 'Unknown']));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['severity']);
    }

    #[Test]
    public function it_requires_owner_when_creating_an_incident(): void
    {
        $response = $this->postJson('/api/incidents', $this->validPayload(['owner' => '']));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['owner']);
    }
}
