<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KelompokTani;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PengurusController extends Controller
{
    /**
     * Display a listing of Pengurus (Role: Pengurus).
     */
    public function index(Request $request)
    {
        $query = Pengguna::with('kelompokTani')->where('role', 'Pengurus');

        $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search, $operator) {
                $q->where('nama', $operator, "%{$search}%")
                  ->orWhere('nik', $operator, "%{$search}%")
                  ->orWhere('username', $operator, "%{$search}%")
                  ->orWhere('email', $operator, "%{$search}%")
                  ->orWhere('no_telepon', $operator, "%{$search}%")
                  ->orWhere('alamat', $operator, "%{$search}%")
                  ->orWhere('id_pengguna', $operator, "%{$search}%")
                  ->orWhere('jabatan', $operator, "%{$search}%")
                  ->orWhereHas('kelompokTani', function ($kq) use ($search, $operator) {
                      $kq->where('nama_kelompok', $operator, "%{$search}%");
                  });
            });
        }

        // Filter by Jabatan
        if ($request->filled('jabatan')) {
            $query->where('jabatan', $request->input('jabatan'));
        }

        // Filter by Kelompok Tani
        if ($request->filled('id_kelompok')) {
            $query->where('id_kelompok', $request->input('id_kelompok'));
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $pengurusList = $query->orderBy('created_at', 'desc')->get();
        $totalPengurus = Pengguna::where('role', 'Pengurus')->count();
        $aktifCount = Pengguna::where('role', 'Pengurus')->where('status', 'Aktif')->count();
        $tidakAktifCount = Pengguna::where('role', 'Pengurus')->where('status', 'Tidak Aktif')->count();
        $ketuaCount = Pengguna::where('role', 'Pengurus')->where('jabatan', 'Ketua')->count();

        // Get active Kelompok Tani for dropdown selections
        $kelompokList = KelompokTani::where('status', 'Aktif')->orderBy('nama_kelompok')->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $pengurusList,
                'total' => $totalPengurus,
            ]);
        }

        return view('Admin.Content.Pengurus', compact(
            'pengurusList',
            'kelompokList',
            'totalPengurus',
            'aktifCount',
            'tidakAktifCount',
            'ketuaCount'
        ));
    }

    /**
     * Store a newly created Pengurus in storage.
     * Note: Only Pengurus can be created from this module.
     */
    public function store(Request $request)
    {
        $namaRegex = '/^[\pL\s.\'-]+$/u';

        // Lowercase username before validation for case-insensitive unique check
        $request->merge(['username' => strtolower($request->input('username', ''))]);

        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:100',
                'regex:' . $namaRegex,
            ],
            'jabatan' => [
                'required',
                'string',
                'max:30',
                function ($attribute, $value, $fail) use ($request) {
                    $idKelompok = $request->input('id_kelompok');
                    $count = Pengguna::where('jabatan', $value)
                        ->where('id_kelompok', $idKelompok)
                        ->where('role', 'Pengurus')
                        ->count();
                    $max = in_array($value, ['Ketua', 'Sekretaris', 'Bendahara']) ? 1 : 3;
                    if ($count >= $max) {
                        $fail("Jabatan {$value} pada kelompok ini sudah terisi (maksimal {$max} orang).");
                    }
                },
            ],
            'id_kelompok' => 'required|string|exists:kelompok_tani,id_kelompok',
            'nik' => 'nullable|string|max:16|unique:pengguna,nik',
            'username' => 'required|string|max:50|alpha_dash|unique:pengguna,username',
            'password' => 'required|string|min:6',
            'email' => 'nullable|email|max:255|unique:pengguna,email',
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'status' => 'required|in:Aktif,Tidak Aktif',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'nama.required' => 'Nama lengkap pengurus wajib diisi.',
            'nama.regex' => "Nama hanya boleh berisi huruf, spasi, titik (.), strip (-), dan tanda petik (').",
            'jabatan.required' => 'Jabatan pengurus (Ketua, Sekretaris, dll) wajib dipilih.',
            'id_kelompok.required' => 'Kelompok tani asal / binaan wajib dipilih.',
            'id_kelompok.exists' => 'Kelompok tani yang dipilih tidak valid atau tidak ditemukan.',
            'nik.unique' => 'NIK ini sudah terdaftar dalam sistem.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan, silakan pilih username lain.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, strip (-), dan garis bawah (_).',
            'password.required' => 'Password login wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email ini sudah terdaftar.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status harus bernilai Aktif atau Tidak Aktif.',
            'foto_profil.image' => 'File foto profil harus berupa gambar.',
            'foto_profil.max' => 'Ukuran foto profil maksimal 2MB.',
        ]);

        // Handle profile photo upload if provided
        $fotoPath = null;
        if ($request->hasFile('foto_profil')) {
            $file = $request->file('foto_profil');
            $filename = 'profil_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/profil'), $filename);
            $fotoPath = 'uploads/profil/' . $filename;
        }

        // Auto-generate random ID: PGR-AngkaRandom
        $idPengguna = Pengguna::generateIdPengguna();

        $pengguna = Pengguna::create([
            'id_pengguna' => $idPengguna,
            'nama' => $validated['nama'],
            'jabatan' => $validated['jabatan'],
            'id_kelompok' => $validated['id_kelompok'],
            'nik' => $validated['nik'] ?? null,
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'email' => $validated['email'] ?? null,
            'no_telepon' => $validated['no_telepon'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'foto_profil' => $fotoPath,
            'role' => 'Pengurus', // Enforce role: Pengurus only
            'status' => $validated['status'],
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Pengurus {$pengguna->nama} ({$pengguna->jabatan}) berhasil ditambahkan ke kelompok tani!",
                'data' => $pengguna->load('kelompokTani'),
            ], 201);
        }

        return redirect()->route('admin.pengurus')
            ->with('success', "Pengurus {$pengguna->nama} ({$pengguna->jabatan}) berhasil ditambahkan!");
    }

    /**
     * Update the specified Pengurus in storage.
     */
    public function update(Request $request, string $id_pengguna)
    {
        $pengurus = Pengguna::where('role', 'Pengurus')->where('id_pengguna', $id_pengguna)->firstOrFail();

        $namaRegex = '/^[\pL\s.\'-]+$/u';

        // Lowercase username before validation for case-insensitive unique check
        $request->merge(['username' => strtolower($request->input('username', ''))]);

        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:100',
                'regex:' . $namaRegex,
            ],
            'jabatan' => [
                'required',
                'string',
                'max:30',
                function ($attribute, $value, $fail) use ($request, $pengurus) {
                    $idKelompok = $request->input('id_kelompok');

                    // If this pengurus already holds this jabatan, allow (they keep their slot)
                    if ($pengurus->jabatan === $value && $pengurus->id_kelompok === $idKelompok) {
                        return;
                    }

                    $count = Pengguna::where('jabatan', $value)
                        ->where('id_kelompok', $idKelompok)
                        ->where('role', 'Pengurus')
                        ->count();
                    $max = in_array($value, ['Ketua', 'Sekretaris', 'Bendahara']) ? 1 : 3;
                    if ($count >= $max) {
                        $fail("Jabatan {$value} pada kelompok ini sudah terisi (maksimal {$max} orang).");
                    }
                },
            ],
            'id_kelompok' => 'required|string|exists:kelompok_tani,id_kelompok',
            'nik' => [
                'nullable',
                'string',
                'max:16',
                Rule::unique('pengguna', 'nik')->ignore($pengurus->id_pengguna, 'id_pengguna'),
            ],
            'username' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('pengguna', 'username')->ignore($pengurus->id_pengguna, 'id_pengguna'),
            ],
            'password' => 'nullable|string|min:6',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('pengguna', 'email')->ignore($pengurus->id_pengguna, 'id_pengguna'),
            ],
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'status' => 'required|in:Aktif,Tidak Aktif',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'nama.required' => 'Nama lengkap pengurus wajib diisi.',
            'nama.regex' => "Nama hanya boleh berisi huruf, spasi, titik (.), strip (-), dan tanda petik (').",
            'jabatan.required' => 'Jabatan pengurus wajib dipilih.',
            'id_kelompok.required' => 'Kelompok tani asal wajib dipilih.',
            'id_kelompok.exists' => 'Kelompok tani tidak valid.',
            'nik.unique' => 'NIK ini sudah terdaftar oleh pengguna lain.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan oleh pengguna lain.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, strip, dan underscore.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh pengguna lain.',
            'status.required' => 'Status wajib dipilih.',
            'foto_profil.image' => 'File foto profil harus berupa gambar.',
            'foto_profil.max' => 'Ukuran foto profil maksimal 2MB.',
        ]);

        $dataToUpdate = [
            'nama' => $validated['nama'],
            'jabatan' => $validated['jabatan'],
            'id_kelompok' => $validated['id_kelompok'],
            'nik' => $validated['nik'] ?? null,
            'username' => $validated['username'],
            'email' => $validated['email'] ?? null,
            'no_telepon' => $validated['no_telepon'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'status' => $validated['status'],
        ];

        // Update password if provided
        if (!empty($validated['password'])) {
            $dataToUpdate['password'] = Hash::make($validated['password']);
        }

        // Handle profile photo upload
        if ($request->hasFile('foto_profil')) {
            // Delete previous photo if exists
            if ($pengurus->foto_profil && file_exists(public_path($pengurus->foto_profil))) {
                @unlink(public_path($pengurus->foto_profil));
            }

            $file = $request->file('foto_profil');
            $filename = 'profil_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/profil'), $filename);
            $dataToUpdate['foto_profil'] = 'uploads/profil/' . $filename;
        }

        $pengurus->update($dataToUpdate);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Data pengurus {$pengurus->nama} berhasil diperbarui!",
                'data' => $pengurus->load('kelompokTani'),
            ]);
        }

        return redirect()->route('admin.pengurus')
            ->with('success', "Data pengurus {$pengurus->nama} ({$pengurus->id_pengguna}) berhasil diperbarui!");
    }

    /**
     * Remove the specified Pengurus from storage.
     */
    public function destroy(Request $request, string $id_pengguna)
    {
        $pengurus = Pengguna::where('role', 'Pengurus')->where('id_pengguna', $id_pengguna)->firstOrFail();
        $deletedName = $pengurus->nama;
        $deletedId = $pengurus->id_pengguna;

        // Delete photo if exists
        if ($pengurus->foto_profil && file_exists(public_path($pengurus->foto_profil))) {
            @unlink(public_path($pengurus->foto_profil));
        }

        $pengurus->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Pengurus {$deletedName} ({$deletedId}) berhasil dihapus!",
            ]);
        }

        return redirect()->route('admin.pengurus')
            ->with('success', "Pengurus {$deletedName} ({$deletedId}) berhasil dihapus!");
    }

    /**
     * Check username availability via AJAX.
     * Query params: username (required), exclude_id (optional, for edit mode)
     */
    public function checkUsername(Request $request)
    {
        $username = strtolower($request->query('username', ''));
        $excludeId = $request->query('exclude_id');

        if ($username === '') {
            return response()->json(['available' => true]);
        }

        $query = Pengguna::where('username', $username);

        if ($excludeId) {
            $query->where('id_pengguna', '!=', $excludeId);
        }

        return response()->json(['available' => !$query->exists()]);
    }
}
