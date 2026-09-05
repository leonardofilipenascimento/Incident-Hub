<?php

use App\Http\Controllers\IncidentController;
use Illuminate\Support\Facades\Route;

Route::prefix('incidents')->group(function () {
    Route::post('/', [IncidentController::class, 'store']);
    Route::get('/', [IncidentController::class, 'index']);
    Route::get('/{incident}', [IncidentController::class, 'show']);
    Route::patch('/{incident}/status', [IncidentController::class, 'updateStatus']);
    Route::patch('/{incident}/severity', [IncidentController::class, 'updateSeverity']);
});
