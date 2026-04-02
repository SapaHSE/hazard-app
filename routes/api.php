<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\InspectionController;
use App\Http\Controllers\API\NewsController;
use App\Http\Controllers\API\AnnouncementController;
use App\Http\Controllers\API\QrAssetController;
use App\Http\Controllers\API\InboxController;

// ── Public Routes ─────────────────────────────────────────────────────────────

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// News & Announcements can be read without login
Route::get('/news',      [NewsController::class, 'index']);
Route::get('/news/{id}', [NewsController::class, 'show']);

// ── Protected Routes (all roles) ──────────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {

    // ── Auth ─────────────────────────────────────────────────────────────────
    Route::post('/logout', [AuthController::class, 'logout']);

    // ── Profile ───────────────────────────────────────────────────────────────
    Route::get('/profile',                  [ProfileController::class, 'getProfile']);
    Route::post('/profile',                 [ProfileController::class, 'updateProfile']);
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);

    // ── Reports (Hazard) ──────────────────────────────────────────────────────
    // GET    /api/reports              → list all reports (filter, search, paginate)
    // POST   /api/reports              → create new report
    // GET    /api/reports/{id}         → detail + auto mark as read
    // DELETE /api/reports/{id}         → delete own report (admin can delete all)
    // PATCH  /api/reports/{id}/status  → update status (admin/supervisor only)
    Route::get('/reports',               [ReportController::class, 'index']);
    Route::post('/reports',              [ReportController::class, 'store']);
    Route::get('/reports/{id}',          [ReportController::class, 'show']);
    Route::delete('/reports/{id}',       [ReportController::class, 'destroy']);
    Route::patch('/reports/{id}/status', [ReportController::class, 'updateStatus'])
        ->middleware('role:admin,supervisor');

    // ── Inspections ───────────────────────────────────────────────────────────
    // GET    /api/inspections           → list (filter, search, paginate)
    // POST   /api/inspections           → create + checklist items
    // GET    /api/inspections/{id}      → detail with checklist
    // DELETE /api/inspections/{id}      → delete own inspection
    Route::get('/inspections',        [InspectionController::class, 'index']);
    Route::post('/inspections',       [InspectionController::class, 'store']);
    Route::get('/inspections/{id}',   [InspectionController::class, 'show']);
    Route::delete('/inspections/{id}',[InspectionController::class, 'destroy']);

    // ── Announcements (Inbox) ─────────────────────────────────────────────────
    // GET    /api/announcements          → list + unread_count
    // GET    /api/announcements/{id}     → detail + auto mark as read
    // POST   /api/announcements          → create (admin/supervisor only)
    // DELETE /api/announcements/{id}     → deactivate (admin only)
    // PATCH  /api/announcements/read-all → mark all as read
    Route::get('/announcements',              [AnnouncementController::class, 'index']);
    Route::get('/announcements/{id}',         [AnnouncementController::class, 'show']);
    Route::patch('/announcements/read-all',   [AnnouncementController::class, 'markAllAsRead']);
    Route::post('/announcements',             [AnnouncementController::class, 'store'])
        ->middleware('role:admin,supervisor');
    Route::delete('/announcements/{id}',      [AnnouncementController::class, 'destroy'])
        ->middleware('role:admin');
            // Inbox — gabungan reports + announcements
    Route::get('/inbox',           [InboxController::class, 'index']);
    Route::post('/inbox/read',     [InboxController::class, 'markAsRead']);
    Route::post('/inbox/read-all', [InboxController::class, 'markAllAsRead']);

    // ── News (admin/supervisor manage) ────────────────────────────────────────
    Route::post('/news',        [NewsController::class, 'store'])
        ->middleware('role:admin,supervisor');
    Route::delete('/news/{id}', [NewsController::class, 'destroy'])
        ->middleware('role:admin');

    // ── QR Assets ─────────────────────────────────────────────────────────────
    // GET /api/qr-assets              → list all assets
    // GET /api/qr-assets/scan         → scan by qr_code (?qr_code=BBE-APAR-...)
    Route::get('/qr-assets',       [QrAssetController::class, 'index']);
    Route::get('/qr-assets/scan',  [QrAssetController::class, 'scan']);

    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
});

});