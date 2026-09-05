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
            'title' => 'Lentidao no relatorio de vendas',
            'description' => 'Relatorio mensal de vendas demorando mais de 30s para carregar.',
            'severity' => IncidentSeverity::Low->value,
            'affected_systems' => ['reporting-service'],
        ]);

        $inProgressLowSeverity = $incidentService->createIncident([
            'title' => 'Erro cosmetico no dashboard interno',
            'description' => 'Icone de status aparece quebrado no dashboard interno de operacoes.',
            'severity' => IncidentSeverity::Low->value,
            'affected_systems' => ['internal-dashboard'],
        ]);
        $incidentService->updateIncidentStatus($inProgressLowSeverity, IncidentStatus::InProgress, null);

        $inProgressMediumSeverity = $incidentService->createIncident([
            'title' => 'Latencia elevada na API de catalogo',
            'description' => 'API de catalogo de produtos com tempo de resposta acima de 2s em 15% das requisicoes.',
            'severity' => IncidentSeverity::Medium->value,
            'affected_systems' => ['catalog-api'],
        ]);
        $incidentService->updateIncidentStatus($inProgressMediumSeverity, IncidentStatus::InProgress, null);

        $resolvedHighSeverity = $incidentService->createIncident([
            'title' => 'Falha intermitente no envio de e-mails',
            'description' => 'Notificacoes por e-mail nao sao entregues para cerca de 10% dos usuarios.',
            'severity' => IncidentSeverity::High->value,
            'affected_systems' => ['notification-service', 'email-provider'],
        ]);
        $incidentService->updateIncidentStatus($resolvedHighSeverity, IncidentStatus::InProgress, null);
        $incidentService->updateIncidentStatus(
            $resolvedHighSeverity,
            IncidentStatus::Resolved,
            'Fila de envio reprocessada e provedor de e-mail estabilizado.'
        );

        $closedCriticalSeverity = $incidentService->createIncident([
            'title' => 'Falha no gateway de pagamento',
            'description' => 'Gateway de pagamento retornando erro 500 em 30% das transacoes de checkout.',
            'severity' => IncidentSeverity::Critical->value,
            'affected_systems' => ['payment-gateway', 'checkout-api'],
        ]);
        $incidentService->updateIncidentStatus($closedCriticalSeverity, IncidentStatus::InProgress, null);
        $incidentService->updateIncidentStatus(
            $closedCriticalSeverity,
            IncidentStatus::Resolved,
            'Causa raiz identificada: certificado expirado no provedor de pagamento. Certificado renovado.'
        );
        $incidentService->updateIncidentStatus(
            $closedCriticalSeverity,
            IncidentStatus::Closed,
            'Incidente encerrado apos 24h de monitoramento sem recorrencia.'
        );
    }
}
