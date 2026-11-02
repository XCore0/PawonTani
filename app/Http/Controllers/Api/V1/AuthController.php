<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\PenggunaResource;
use App\Models\Pengguna;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Otentikasi anggota dan penerbitan Bearer Token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $identifier = $request->input('identifier');
        $password = $request->input('password');

        // Cari pengguna berdasarkan username, email, atau nomor telepon
        $pengguna = Pengguna::with(['kelompokTani', 'anggota'])
            ->where(function ($query) use ($identifier) {
                $query->where('username', $identifier)
                    ->orWhere('email', $identifier)
                    ->orWhere('no_telepon', $identifier);
            })
            ->first();

        // Validasi keberadaan akun dan password
        if (!$pengguna || !Hash::check($password, $pengguna->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username/email atau password salah.',
            ], 401);
        }

        // Validasi keaktifan akun
        if ($pengguna->status !== 'Aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak aktif. Silakan hubungi pengurus kelompok tani.',
            ], 403);
        }

        // Pembatasan hak akses: aplikasi mobile khusus untuk role Anggota
        if ($pengguna->role !== 'Anggota') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Aplikasi mobile ini hanya diperuntukkan bagi Anggota kelompok tani.',
            ], 403);
        }

        // Terbitkan Sanctum plain text token
        $token = $pengguna->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => new PenggunaResource($pengguna),
            ],
        ]);
    }

    /**
     * Ambil data profil anggota yang sedang login.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var \App\Models\Pengguna $user */
        $user = $request->user()->load(['kelompokTani', 'anggota']);

        return response()->json([
            'success' => true,
            'message' => 'Data profil berhasil diambil.',
            'data' => new PenggunaResource($user),
        ]);
    }

    /**
     * Revokasi token aktif (logout).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil. Sesi mobile telah diakhiri.',
        ]);
    }
}
