<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PomodoroController;
use App\Http\Controllers\Api\StudyLogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User route
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Dashboard stats route
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/heatmap', [DashboardController::class, 'heatmap']);
    
    // Study Log routes
    Route::get('/study-logs', [StudyLogController::class, 'index']);
    Route::post('/study-logs', [StudyLogController::class, 'store']);
    Route::get('/study-logs/{id}', [StudyLogController::class, 'show']);
    Route::put('/study-logs/{id}', [StudyLogController::class, 'update']);
    Route::delete('/study-logs/{id}', [StudyLogController::class, 'destroy']);

    // Pomodoro routes
    Route::prefix('pomodoro')->group(function () {
        Route::post('/start', [PomodoroController::class, 'start']);
        Route::put('/{id}/pause', [PomodoroController::class, 'pause']);
        Route::put('/{id}/resume', [PomodoroController::class, 'resume']);
        Route::put('/{id}/complete', [PomodoroController::class, 'complete']);
        Route::put('/{id}/cancel', [PomodoroController::class, 'cancel']);
        Route::get('/active', [PomodoroController::class, 'active']);
        Route::get('/history', [PomodoroController::class, 'history']);
        Route::get('/statistics', [PomodoroController::class, 'statistics']);
        Route::post('/settings', [PomodoroController::class, 'saveSettings']);
        Route::get('/settings', [PomodoroController::class, 'getSettings']);
    });
});
