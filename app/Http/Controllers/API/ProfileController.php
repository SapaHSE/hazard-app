<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // GET /api/profile
    public function getProfile()
    {
        return response()->json([
            'status' => 'success',
            'data'   => new UserResource(Auth::user()),
        ]);
    }

    // POST /api/profile
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'name'       => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'position'   => 'nullable|string|max:255',
            'avatar'     => 'nullable|image|max:2048',
        ]);

        if ($request->filled('name'))       $user->name       = $request->name;
        if ($request->filled('department')) $user->department = $request->department;
        if ($request->filled('position'))   $user->position   = $request->position;

        if ($request->hasFile('avatar')) {
            if ($user->avatar_url) {
                Storage::disk('public')->delete($user->avatar_url);
            }
            $user->avatar_url = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Profile berhasil diupdate',
            'data'    => new UserResource($user),
        ]);
    }

    // POST /api/profile/change-password
    public function changePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed', // butuh new_password_confirmation
        ]);

        // Cek password lama
        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Password saat ini tidak sesuai',
            ], 422);
        }

        // Pastikan password baru berbeda dari yang lama
        if (Hash::check($request->new_password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Password baru tidak boleh sama dengan password saat ini',
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        // Hapus semua token lama, paksa login ulang di semua device
        $user->tokens()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Password berhasil diubah. Silakan login kembali.',
        ]);
    }
}