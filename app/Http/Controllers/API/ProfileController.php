<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // 🔥 tambahkan ini

class ProfileController extends Controller
{
    // 🔹 GET PROFILE (data user login)
    public function getProfile()
    {
        return response()->json([
            'status' => 'success',
            'data' => Auth::user()
        ]);
    }

    // 🔹 UPDATE PROFILE
    public function updateProfile(Request $request)
    {
        /** @var User $user */ // 🔥 bantu IDE
        $user = Auth::user();

        // 🔥 validasi (biar lebih aman)
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email'
        ]);

        // 🔥 update manual (aman)
        if ($request->has('name')) {
            $user->name = $request->name;
            
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        $user->save(); // ✅ ini sebenarnya valid di Laravel

        return response()->json([
            'status' => 'success',
            'message' => 'Profile berhasil diupdate',
            'data' => $user
        ]);
    }
}