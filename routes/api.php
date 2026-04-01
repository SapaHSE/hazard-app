<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\NewsController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\NotificationController;

// ── Public Routes ─────────────────────────────────────────────────────────────

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::get('/news',      [NewsController::class, 'index']);
Route::get('/news/{id}', [NewsController::class, 'show']);

// ── Protected Routes ──────────────────────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile
    Route::get('/profile',                  [ProfileController::class, 'getProfile']);
    Route::post('/profile',                 [ProfileController::class, 'updateProfile']);
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);

    // Reports
    Route::get('/reports',               [ReportController::class, 'index']);
    Route::post('/reports',              [ReportController::class, 'store']);
    Route::get('/reports/{id}',          [ReportController::class, 'show']);
    Route::delete('/reports/{id}',       [ReportController::class, 'destroy']);
    Route::patch('/reports/{id}/status', [ReportController::class, 'updateStatus'])
        ->middleware('role:admin,supervisor');

    // Notifikasi / Inbox
    Route::get('/notifications',             [NotificationController::class, 'index']);
    Route::get('/notifications/{id}',        [NotificationController::class, 'show']);
    Route::patch('/notifications/read-all',  [NotificationController::class, 'markAllAsRead']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{id}',     [NotificationController::class, 'destroy']);

});