<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\EquipmentResource;
use App\Models\Equipment;
use App\Models\QrScan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EquipmentController extends Controller
{
    // GET /api/equipment
    public function index()
    {
        $equipment = Equipment::with('user')->latest()->get();

        return response()->json([
            'status' => 'success',
            'data'   => EquipmentResource::collection($equipment),
        ]);
    }

    // GET /api/equipment/scan?qr_code=BBE-APAR-2024-001234
    public function scan(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $equipment = Equipment::with('user')
            ->where('qr_code', $request->qr_code)
            ->first();

        if (! $equipment) {
            return response()->json([
                'status'  => 'error',
                'message' => 'QR Code tidak ditemukan',
            ], 404);
        }

        QrScan::create([
            'equipment_id' => $equipment->id,
            'user_id'      => Auth::id(),
            'scanned_at'   => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'data'   => new EquipmentResource($equipment),
        ]);
    }
}