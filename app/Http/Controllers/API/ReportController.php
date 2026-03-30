<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;

class ReportController extends Controller
{
    // 🔹 GET /api/reports
    public function index()
    {
        $reports = Report::with('user')->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $reports
        ]);
    }

    // 🔹 POST /api/reports
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'location' => 'nullable',
            'photo' => 'nullable',
        ]);

        $validated['user_id'] = Auth::id();

        $report = Report::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Report berhasil dibuat',
            'data' => $report
        ]);
    }

    // 🔹 GET /api/reports/{id}
    public function show($id)
    {
        $report = Report::with('user')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $report
        ]);
    }
}