<?php

namespace Database\Factories;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Incident>
 */
class IncidentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'severity' => IncidentSeverity::Medium,
            'owner' => $this->faker->firstName(),
            'status' => IncidentStatus::Open,
        ];
    }

    public function withSeverity(IncidentSeverity $severity): static
    {
        return $this->state(fn () => ['severity' => $severity]);
    }

    public function withStatus(IncidentStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
