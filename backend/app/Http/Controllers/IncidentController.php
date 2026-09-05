<?php

namespace App\Http\Controllers;

use App\Enums\IncidentStatus;
use App\Http\Requests\StoreIncidentRequest;
use App\Http\Requests\UpdateIncidentStatusRequest;
use App\Http\Resources\IncidentResource;
use App\Models\Incident;
use App\Services\IncidentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IncidentController extends Controller
{
    public function __construct(private readonly IncidentService $incidentService) {}

    public function store(StoreIncidentRequest $request): JsonResponse
    {
        $incident = $this->incidentService->createIncident($request->validated());

        return IncidentResource::make($incident)->response()->setStatusCode(201);
    }

    public function index(Request $request): JsonResponse
    {
        $incidents = $this->incidentService->listIncidents(
            $request->only(['severity', 'status'])
        );

        return response()->json([
            'data' => IncidentResource::collection($incidents),
        ]);
    }

    public function show(Incident $incident): JsonResponse
    {
        $incident->load('history');

        return IncidentResource::make($incident)->response();
    }

    public function updateStatus(UpdateIncidentStatusRequest $request, Incident $incident): JsonResponse
    {
        $updated = $this->incidentService->updateIncidentStatus(
            $incident,
            IncidentStatus::from($request->validated('status'))
        );

        return IncidentResource::make($updated)->response();
    }
}
