<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLicense;
use App\Models\UserCertification;
use App\Models\RegistrationLog;
use App\Mail\RegistrationRejectedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ApprovalController extends Controller
{
    /**
     * Get all pending approvals (Users, Licenses, Certifications)
     */
    public function getPendingApprovals()
    {
        // 1. Pending Users
        $users = User::where('registration_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($u) {
                return [
                    'id'             => $u->id,
                    'type'           => 'register_user',
                    'status'         => 'pending',
                    'title'          => 'Registrasi Akun Baru',
                    'subtitle'       => 'Mengajukan pembuatan akun SapaHSE',
                    'requester_name' => $u->full_name,
                    'department'     => $u->department,
                    'company'        => $u->company,
                    'created_at'     => $u->created_at->toIso8601String(),
                    'metadata'       => [
                        'email'       => $u->personal_email,
                        'employee_id' => $u->employee_id,
                        'position'    => $u->position,
                        'alamat'      => $u->alamat,
                    ],
                ];
            });

        // 2. Pending Licenses
        $licenses = UserLicense::with('user')
            ->where('is_verified', false)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($l) {
                return [
                    'id'             => $l->id,
                    'type'           => 'license',
                    'status'         => 'pending',
                    'title'          => $l->name,
                    'subtitle'       => 'Pengajuan verifikasi lisensi baru',
                    'requester_name' => $l->user->full_name ?? 'Unknown',
                    'department'     => $l->user->department ?? '-',
                    'company'        => $l->user->company ?? '-',
                    'created_at'     => $l->created_at->toIso8601String(),
                    'metadata'       => [
                        'license_number' => $l->license_number,
                        'issuer'         => $l->issuer,
                        'expired_at'     => $l->expired_at,
                        'file_url'       => $l->file_path ? \asset('storage/' . $l->file_path) : null,
                    ],
                ];
            });

        // 3. Pending Certifications
        $certs = UserCertification::with('user')
            ->where('is_verified', false)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($c) {
                return [
                    'id'             => $c->id,
                    'type'           => 'certification',
                    'status'         => 'pending',
                    'title'          => $c->name,
                    'subtitle'       => 'Pengajuan verifikasi sertifikat baru',
                    'requester_name' => $c->user->full_name ?? 'Unknown',
                    'department'     => $c->user->department ?? '-',
                    'company'        => $c->user->company ?? '-',
                    'created_at'     => $c->created_at->toIso8601String(),
                    'metadata'       => [
                        'cert_number' => $c->certification_number,
                        'issuer'      => $c->issuer,
                        'expired_at'  => $c->expired_at,
                        'file_url'    => $c->file_path ? \asset('storage/' . $c->file_path) : null,
                    ],
                ];
            });

        // Combine all and sort by date
        $all = $users->concat($licenses)->concat($certs)->sortByDesc('created_at')->values();

        return \response()->json([
            'status' => 'success',
            'data'   => $all
        ]);
    }

    /**
     * Approve a request
     */
    public function approve(Request $request)
    {
        $request->validate([
            'id'   => 'required',
            'type' => 'required|in:register_user,license,certification',
        ]);

        $id = $request->id;
        $type = $request->type;

        DB::beginTransaction();
        try {
            switch ($type) {
                case 'register_user':
                    $user = User::findOrFail($id);
                    $user->registration_status = 'approved';
                    $user->is_active = true;
                    $user->save();
                    break;

                case 'license':
                    $license = UserLicense::findOrFail($id);
                    $license->is_verified = true;
                    $license->save();
                    break;

                case 'certification':
                    $cert = UserCertification::findOrFail($id);
                    $cert->is_verified = true;
                    $cert->save();
                    break;
            }

            DB::commit();
            return \response()->json([
                'status'  => 'success',
                'message' => 'Permintaan berhasil disetujui.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return \response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyetujui: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a request
     */
    public function reject(Request $request)
    {
        $request->validate([
            'id'     => 'required',
            'type'   => 'required|in:register_user,license,certification',
            'reason' => 'nullable|string|max:255',
        ]);

        $id = $request->id;
        $type = $request->type;
        $reason = $request->reason ?? 'Dokumen tidak valid atau tidak lengkap.';

        DB::beginTransaction();
        try {
            switch ($type) {
                case 'register_user':
                    $user = User::findOrFail($id);
                    
                    // Create registration log entry
                    RegistrationLog::create([
                        'full_name'        => $user->full_name,
                        'employee_id'      => $user->employee_id,
                        'personal_email'   => $user->personal_email,
                        'phone_number'     => $user->phone_number,
                        'company'          => $user->company,
                        'department'       => $user->department,
                        'rejection_reason' => $reason,
                        'rejected_at'      => now(),
                    ]);

                    // Send rejection email
                    try {
                        Mail::to($user->personal_email)->send(new RegistrationRejectedMail($user->full_name, $reason));
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Failed to send rejection email: " . $e->getMessage());
                    }

                    $user->delete();
                    break;

                case 'license':
                    $license = UserLicense::findOrFail($id);
                    $license->delete(); 
                    break;

                case 'certification':
                    $cert = UserCertification::findOrFail($id);
                    $cert->delete(); 
                    break;
            }

            DB::commit();
            return \response()->json([
                'status'  => 'success',
                'message' => 'Permintaan berhasil ditolak.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return \response()->json([
                'status'  => 'error',
                'message' => 'Gagal menolak: ' . $e->getMessage()
            ], 500);
        }
    }
}
