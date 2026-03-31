<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use App\Models\ReportPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    // GET /api/reports  (?type=hazard &severity=high &status=open)
    public function index(Request $request)
    {
        $query = Report::with(['user', 'photos'])->latest();

        if ($request->filled('type'))     $query->where('type', $request->type);
        if ($request->filled('severity')) $query->where('severity', $request->severity);
        if ($request->filled('status'))   $query->where('status', $request->status);

        return response()->json([
            'status' => 'success',
            'data'   => ReportResource::collection($query->get()),
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
            'photos.*'    => 'image|max:2048',
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

        return response()->json([
            'status'  => 'success',
            'message' => 'Status laporan diperbarui',
            'data'    => new ReportResource($report->load(['user', 'photos'])),
        ]);
    }
}