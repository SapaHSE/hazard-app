<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\NewsController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\EquipmentController;

// ── Public Routes ─────────────────────────────────────────────────────────────

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::get('/news',      [NewsController::class, 'index']);
Route::get('/news/{id}', [NewsController::class, 'show']);

// ── Protected Routes (wajib login) ───────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile
    Route::get('/profile',  [ProfileController::class, 'getProfile']);
    Route::post('/profile', [ProfileController::class, 'updateProfile']);

    // Reports
    Route::get('/reports',               [ReportController::class, 'index']);
    Route::post('/reports',              [ReportController::class, 'store']);
    Route::get('/reports/{id}',          [ReportController::class, 'show']);
    Route::patch('/reports/{id}/status', [ReportController::class, 'updateStatus'])
        ->middleware('role:admin,supervisor');

    // Equipment & QR Scan
    Route::get('/equipment',      [EquipmentController::class, 'index']);
    Route::get('/equipment/scan', [EquipmentController::class, 'scan']);

});