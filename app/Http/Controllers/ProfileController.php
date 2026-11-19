<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use App\Services\AuditLogger;
use App\Services\CloudinaryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        /** @var Pengguna $user */
        $user = Auth::user();
        $user->load('kelompokTani');

        // Layout dinamis sesuai role
        $layout = match ($user->role) {
            'Admin', 'PPL' => 'Admin.Layout._layout',
            default => 'Pengurus.Layout._layout',
        };

        return view('Profile', compact('user', 'layout'));
    }

    public function update(Request $request, CloudinaryService $cloudinary): RedirectResponse
    {
        /** @var Pengguna $user */
        $user = Auth::user();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nik' => [
                'nullable',
                'string',
                'size:16',
                Rule::unique('pengguna', 'nik')->ignore($user->id_pengguna, 'id_pengguna'),
            ],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'alpha_dash',
                Rule::unique('pengguna', 'username')->ignore($user->id_pengguna, 'id_pengguna'),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('pengguna', 'email')->ignore($user->id_pengguna, 'id_pengguna'),
            ],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'foto_profil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'password_saat_ini' => ['nullable', 'required_with:password_baru', 'current_password'],
            'password_baru' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'nik.size' => 'Nomor Induk Kependudukan (NIK) harus berjumlah 16 digit.',
            'nik.unique' => 'NIK ini telah digunakan oleh akun lain.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username telah digunakan.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda hubung (-), dan garis bawah (_).',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Email telah digunakan oleh akun lain.',
            'foto_profil.image' => 'Berkas foto profil harus berupa gambar.',
            'foto_profil.max' => 'Ukuran foto profil maksimal 2 MB.',
            'password_saat_ini.required_with' => 'Kata sandi saat ini harus diisi untuk mengubah kata sandi.',
            'password_saat_ini.current_password' => 'Kata sandi saat ini tidak cocok.',
            'password_baru.min' => 'Kata sandi baru minimal 6 karakter.',
            'password_baru.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $updateData = [
            'nama' => $validated['nama'],
            'username' => $validated['username'],
            'email' => $validated['email'] ?? null,
            'no_telepon' => $validated['no_telepon'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
        ];

        if (array_key_exists('nik', $validated)) {
            $updateData['nik'] = $validated['nik'];
        }

        // Upload foto profil jika ada
        if ($request->hasFile('foto_profil')) {
            $uploadedUrl = $cloudinary->upload(
                $request->file('foto_profil')->getRealPath(),
                'pawontani/profil'
            );

            if ($uploadedUrl) {
                $updateData['foto_profil'] = $uploadedUrl;
            }
        }

        // Update password jika diisi
        if (!empty($validated['password_baru'])) {
            $updateData['password'] = Hash::make($validated['password_baru']);
        }

        $user->update($updateData);

        AuditLogger::log(
            'pengguna.update_profil',
            'Pengguna memperbarui informasi profil biodata mandiri.',
            'Pengguna',
            $user->id_pengguna,
            $user->id_kelompok,
            $request
        );

        return back()->with('success', 'Biodata profil berhasil diperbarui.');
    }
}
