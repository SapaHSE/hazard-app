<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReportResource;
use App\Models\Notification;
use App\Models\Report;
use App\Models\ReportPhoto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    // GET /api/reports
    public function index(Request $request)
    {
        $query = Report::with(['user', 'photos'])->latest();

        if ($request->filled('type'))     $query->where('type', $request->type);
        if ($request->filled('severity')) $query->where('severity', $request->severity);
        if ($request->filled('status'))   $query->where('status', $request->status);

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%")
                  ->orWhere('location', 'like', "%{$keyword}%");
            });
        }

        if ($request->sort === 'oldest') $query->oldest();

        $perPage = (int) $request->get('per_page', 10);
        $reports = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'meta'   => [
                'total'        => $reports->total(),
                'per_page'     => $reports->perPage(),
                'current_page' => $reports->currentPage(),
                'last_page'    => $reports->lastPage(),
                'has_more'     => $reports->hasMorePages(),
            ],
            'data' => ReportResource::collection($reports->items()),
        ]);
    }

    // POST /api/reports
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'type'        => 'required|in:hazard,inspection',
            'severity'    => 'required|in:low,medium,high',
            'location'    => 'nullable|string|max:255',
            'photos'      => 'nullable|array',
            'photos.*'    => 'image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $report = Report::create([
            'user_id'     => Auth::id(),
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'type'        => $validated['type'],
            'severity'    => $validated['severity'],
            'location'    => $validated['location'] ?? null,
            'status'      => 'open',
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                ReportPhoto::create([
                    'report_id' => $report->id,
                    'photo_url' => $photo->store('report-photos', 'public'),
                ]);
            }
        }

        $this->notifyAdmins($report);
        $report->load(['user', 'photos']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Laporan berhasil dibuat',
            'data'    => new ReportResource($report),
        ], 201);
    }

    // GET /api/reports/{id}
    public function show($id)
    {
        $report = Report::with(['user', 'photos'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => new ReportResource($report),
        ]);
    }

    // PATCH /api/reports/{id}/status
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,closed',
        ]);

        $report = Report::findOrFail($id);
        $report->update(['status' => $request->status]);

        Notification::create([
            'user_id'         => $report->user_id,
            'title'           => 'Status Laporan Diperbarui',
            'body'            => "Laporan \"{$report->title}\" diperbarui menjadi " . ucfirst(str_replace('_', ' ', $request->status)) . ".",
            'type'            => 'report',
            'notifiable_id'   => $report->id,
            'notifiable_type' => Report::class,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Status laporan diperbarui',
            'data'    => new ReportResource($report->load(['user', 'photos'])),
        ]);
    }

    // DELETE /api/reports/{id}
    // Hanya bisa hapus laporan milik sendiri, kecuali admin bisa hapus semua
    public function destroy($id)
    {
        $report = Report::with('photos')->findOrFail($id);
        $user   = Auth::user();

        // Cek izin — hanya pelapor atau admin yang bisa hapus
        if ($report->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki izin menghapus laporan ini',
            ], 403);
        }

        // Hapus foto dari storage
        foreach ($report->photos as $photo) {
            Storage::disk('public')->delete($photo->photo_url);
        }

        $report->delete(); // photos ikut terhapus karena cascade

        return response()->json([
            'status'  => 'success',
            'message' => 'Laporan berhasil dihapus',
        ]);
    }

    private function notifyAdmins(Report $report): void
    {
        $admins        = User::whereIn('role', ['admin', 'supervisor'])->get();
        $typeLabel     = $report->type === 'hazard' ? 'Hazard' : 'Inspeksi';
        $severityLabel = match ($report->severity) {
            'high'   => 'P1 - High',
            'medium' => 'P2 - Medium',
            default  => 'P3 - Low',
        };

        foreach ($admins as $admin) {
            Notification::create([
                'user_id'         => $admin->id,
                'title'           => "Laporan {$typeLabel} Baru",
                'body'            => "{$report->user->name} melaporkan \"{$report->title}\" [{$severityLabel}] di {$report->location}.",
                'type'            => 'report',
                'notifiable_id'   => $report->id,
                'notifiable_type' => Report::class,
            ]);
        }
    }
}