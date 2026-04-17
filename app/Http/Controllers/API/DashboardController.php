<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\HazardReport;
use App\Models\InspectionReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get aggregate statistics for Hazard and Inspection reports
     */
    public function statistics(Request $request)
    {
        // ── 1. Hazard Statistics ─────────────────────────────────────────────
        $hQuery = HazardReport::query();
        
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $hQuery->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        $hStats = [
            'total'       => (clone $hQuery)->count(),
            'open'        => (clone $hQuery)->where('status', 'open')->count(),
            'in_progress' => (clone $hQuery)->where('status', 'in_progress')->count(),
            'closed'      => (clone $hQuery)->where('status', 'closed')->count(),
            'severity' => [
                'critical' => (clone $hQuery)->where('severity', 'critical')->count(),
                'high'     => (clone $hQuery)->where('severity', 'high')->count(),
                'low'      => (clone $hQuery)->where('severity', 'low')->count(),
            ]
        ];

        // ── 2. Inspection Statistics ─────────────────────────────────────────
        $iQuery = InspectionReport::query();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $iQuery->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        $iStats = [
            'total'       => (clone $iQuery)->count(),
            'open'        => (clone $iQuery)->where('status', 'open')->count(),
            'in_progress' => (clone $iQuery)->where('status', 'in_progress')->count(),
            'closed'      => (clone $iQuery)->where('status', 'closed')->count(),
            'results' => [
                'compliant'         => (clone $iQuery)->where('result', 'compliant')->count(),
                'non_compliant'     => (clone $iQuery)->where('result', 'non_compliant')->count(),
                'needs_follow_up'   => (clone $iQuery)->where('result', 'needs_follow_up')->count(),
            ]
        ];

        return response()->json([
            'status' => 'success',
            'data'   => [
                'summary' => [
                    'total_reports' => $hStats['total'] + $iStats['total'],
                    'total_hazard'  => $hStats['total'],
                    'total_inspection' => $iStats['total'],
                ],
                'hazard'     => $hStats,
                'inspection' => $iStats,
            ]
        ]);
    }
}
