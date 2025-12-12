<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TaskApiController;
use App\Http\Controllers\Api\ReportApiController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Task API endpoints
    Route::apiResource('tasks', TaskApiController::class)->names([
        'index' => 'api.tasks.index',
        'store' => 'api.tasks.store',
        'show' => 'api.tasks.show',
        'update' => 'api.tasks.update',
        'destroy' => 'api.tasks.destroy',
    ]);
    Route::patch('tasks/{task}/reschedule', [TaskApiController::class, 'reschedule'])->name('api.tasks.reschedule');

    // Report API endpoints
    Route::get('workers/{worker}/report', [ReportApiController::class, 'workerPerformance']);
});
