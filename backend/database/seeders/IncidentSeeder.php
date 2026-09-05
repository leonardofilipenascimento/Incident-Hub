<?php

namespace Database\Seeders;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Services\IncidentService;
use Illuminate\Database\Seeder;

class IncidentSeeder extends Seeder
{
    public function run(IncidentService $incidentService): void
    {
        $incidentService->createIncident([
            'title' => 'Payment API instability',
            'description' => 'Payment API returning intermittent errors during checkout.',
            'severity' => IncidentSeverity::Critical->value,
            'owner' => 'Ana',
        ]);

        $reconciliationDelay = $incidentService->createIncident([
            'title' => 'Reconciliation delay',
            'description' => 'Daily financial reconciliation job running several hours behind schedule.',
            'severity' => IncidentSeverity::High->value,
            'owner' => 'Bruno',
        ]);
        $incidentService->updateIncidentStatus($reconciliationDelay, IncidentStatus::InProgress);

        $incorrectNotification = $incidentService->createIncident([
            'title' => 'Incorrect customer notification',
            'description' => 'Customers received a notification with the wrong order status.',
            'severity' => IncidentSeverity::Medium->value,
            'owner' => 'Carla',
        ]);
        $incidentService->updateIncidentStatus($incorrectNotification, IncidentStatus::InProgress);
        $incidentService->updateIncidentStatus($incorrectNotification, IncidentStatus::Resolved);
    }
}
