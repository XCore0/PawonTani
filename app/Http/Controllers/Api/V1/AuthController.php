<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\PenggunaResource;
use App\Models\Pengguna;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\AuditLogger;
use App\Services\AccountErasureService;

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
        AuditLogger::log('auth.mobile_login', 'Anggota berhasil masuk ke aplikasi mobile.', 'Pengguna', $pengguna->id_pengguna, $pengguna->id_kelompok, $request);

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
     * Penghapusan akun mandiri oleh anggota melalui aplikasi Flutter.
     * Record teknis dipertahankan dalam bentuk anonim untuk menjaga foreign key audit.
     */
    public function deleteAccount(Request $request, AccountErasureService $erasure): JsonResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
            'confirmation' => ['required', 'in:HAPUS AKUN'],
        ], [
            'confirmation.in' => 'Ketik HAPUS AKUN untuk mengonfirmasi penghapusan.',
        ]);

        $user = $request->user();
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Verifikasi password gagal.',
            ], 422);
        }
        if ($user->role !== 'Anggota') {
            return response()->json([
                'success' => false,
                'message' => 'Penghapusan mandiri hanya tersedia untuk akun Anggota.',
            ], 403);
        }

        $erasure->erase($user, $request, 'Penghapusan mandiri melalui aplikasi mobile.');

        return response()->json([
            'success' => true,
            'message' => 'Akun dan data pribadi berhasil dihapus. Anda telah keluar dari semua sesi.',
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
     * Update data profil anggota yang sedang login.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        /** @var \App\Models\Pengguna $user */
        $user = $request->user();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:50', 'alpha_dash', 'unique:pengguna,username,' . $user->id_pengguna . ',id_pengguna'],
            'email' => ['nullable', 'email', 'max:255', 'unique:pengguna,email,' . $user->id_pengguna . ',id_pengguna'],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'foto_profil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'password_baru' => ['nullable', 'string', 'min:6'],
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'username.unique' => 'Username telah digunakan akun lain.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda hubung (-), dan garis bawah (_).',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh akun lain.',
            'foto_profil.image' => 'Berkas foto profil harus berupa gambar.',
            'foto_profil.max' => 'Ukuran foto profil maksimal 2 MB.',
            'password_baru.min' => 'Kata sandi baru minimal 6 karakter.',
        ]);

        $user->nama = $validated['nama'];
        if (!empty($validated['username'])) {
            $user->username = $validated['username'];
        }
        if (array_key_exists('email', $validated)) {
            $user->email = $validated['email'];
        }
        if (array_key_exists('no_telepon', $validated)) {
            $user->no_telepon = $validated['no_telepon'];
        }
        if (array_key_exists('alamat', $validated)) {
            $user->alamat = $validated['alamat'];
        }
        if (!empty($validated['password_baru'])) {
            $user->password = Hash::make($validated['password_baru']);
        }

        // Upload foto profil jika ada
        if ($request->hasFile('foto_profil')) {
            try {
                $cloudinary = app(\App\Services\CloudinaryService::class);
                $uploadedUrl = $cloudinary->upload(
                    $request->file('foto_profil')->getRealPath(),
                    'pawontani/profil'
                );
                if ($uploadedUrl) {
                    $user->foto_profil = $uploadedUrl;
                }
            } catch (\Exception $e) {
                // Ignore upload failure or log
            }
        }

        $user->save();
        $user->load(['kelompokTani', 'anggota']);

        AuditLogger::log('auth.mobile_update_profile', 'Anggota memperbarui profil biodata.', 'Pengguna', $user->id_pengguna, $user->id_kelompok, $request);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data' => new PenggunaResource($user),
        ]);
    }

    /**
     * Revokasi token aktif (logout).
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        AuditLogger::log('auth.mobile_logout', 'Anggota keluar dari aplikasi mobile.', 'Pengguna', $user->id_pengguna, $user->id_kelompok, $request);
        $token = $user->currentAccessToken();
        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil. Sesi mobile telah diakhiri.',
        ]);
    }
}
