<?php

namespace App\Http\Controllers;

use App\Services\IncidentService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(private readonly IncidentService $incidentService) {}

    public function show(): JsonResponse
    {
        return response()->json([
            'open_incidents' => $this->incidentService->countOpenIncidents(),
            'unresolved_critical_incidents' => $this->incidentService->countUnresolvedCriticalIncidents(),
            'resolved_incidents' => $this->incidentService->countResolvedIncidents(),
        ]);
    }
}
