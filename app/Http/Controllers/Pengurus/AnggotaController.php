<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AnggotaController extends Controller
{
    public function index(Request $request)
    {
        $pengurus = $this->currentPengurus();
        abort_unless($pengurus?->id_kelompok, 403, 'Pengurus belum memiliki kelompok tani.');

        $anggota = Pengguna::with(['kelompokTani', 'anggota'])
            ->where('role', 'Anggota')
            ->where('id_kelompok', $pengurus->id_kelompok)
            ->orderByDesc('created_at')
            ->get();

        $kelompok = $pengurus->kelompokTani;

        return view('Pengurus.Content.Anggota', compact('anggota', 'kelompok', 'pengurus'));
    }

    public function store(Request $request)
    {
        $pengurus = $this->currentPengurus();
        abort_unless($pengurus?->id_kelompok, 403, 'Pengurus belum memiliki kelompok tani.');

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', 'regex:/^[\pL\s.\'-]+$/u'],
            'nik' => ['nullable', 'digits:16', 'unique:pengguna,nik'],
            'username' => 'required|string|max:100|alpha_dash|unique:pengguna,username',
            'password' => 'required|string|min:6',
            'email' => 'nullable|email|max:255|unique:pengguna,email',
            'no_telepon' => ['nullable', 'regex:/^[0-9]+$/', 'max:15'],
            'alamat' => 'nullable|string',
            'status' => 'required|in:Aktif,Tidak Aktif',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        DB::transaction(function () use ($request, $validated, $pengurus) {
            $fotoPath = null;
            if ($request->hasFile('foto_profil')) {
                $fotoPath = $request->file('foto_profil')->store('foto-profil', 'public');
            }

            $pengguna = Pengguna::create([
                'nama' => $validated['nama'],
                'nik' => $validated['nik'] ?? null,
                'username' => $validated['username'],
                'password' => Hash::make($validated['password']),
                'email' => $validated['email'] ?? null,
                'no_telepon' => $validated['no_telepon'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
                'foto_profil' => $fotoPath,
                'role' => 'Anggota',
                'id_kelompok' => $pengurus->id_kelompok,
                'status' => $validated['status'],
            ]);

            Anggota::create([
                'id_pengguna' => $pengguna->id_pengguna,
                'status_keanggotaan' => $validated['status'],
            ]);
        });

        return redirect()->route('pengurus.anggota')
            ->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function update(Request $request, string $id_pengguna)
    {
        $pengurus = $this->currentPengurus();
        abort_unless($pengurus?->id_kelompok, 403, 'Pengurus belum memiliki kelompok tani.');

        $anggota = $this->scopedAnggota($pengurus, $id_pengguna);
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', 'regex:/^[\pL\s.\'-]+$/u'],
            'nik' => ['nullable', 'digits:16', Rule::unique('pengguna', 'nik')->ignore($anggota->id_pengguna, 'id_pengguna')],
            'username' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('pengguna', 'username')->ignore($anggota->id_pengguna, 'id_pengguna')],
            'password' => 'nullable|string|min:6',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('pengguna', 'email')->ignore($anggota->id_pengguna, 'id_pengguna')],
            'no_telepon' => ['nullable', 'regex:/^[0-9]+$/', 'max:15'],
            'alamat' => 'nullable|string',
            'status' => 'required|in:Aktif,Tidak Aktif',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        DB::transaction(function () use ($request, $validated, $anggota) {
            $anggota->fill([
                'nama' => $validated['nama'],
                'nik' => $validated['nik'] ?? null,
                'username' => strtolower($validated['username']),
                'email' => $validated['email'] ?? null,
                'no_telepon' => $validated['no_telepon'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
                'status' => $validated['status'],
            ]);

            if (!empty($validated['password'])) {
                $anggota->password = Hash::make($validated['password']);
            }
            if ($request->hasFile('foto_profil')) {
                $anggota->foto_profil = $this->storePhoto($request);
            }
            $anggota->save();
            $anggota->anggota()->update(['status_keanggotaan' => $validated['status']]);
        });

        return redirect()->route('pengurus.anggota')->with('success', 'Data anggota berhasil diperbarui.');
    }

    public function destroy(string $id_pengguna)
    {
        $pengurus = $this->currentPengurus();
        abort_unless($pengurus?->id_kelompok, 403, 'Pengurus belum memiliki kelompok tani.');
        $anggota = $this->scopedAnggota($pengurus, $id_pengguna);
        $nama = $anggota->nama;

        if ($anggota->foto_profil && file_exists(public_path($anggota->foto_profil))) {
            @unlink(public_path($anggota->foto_profil));
        }
        $anggota->delete();

        return redirect()->route('pengurus.anggota')->with('success', "Anggota {$nama} berhasil dihapus.");
    }

    private function currentPengurus(): ?Pengguna
    {
        $idPengguna = session('pengurus_id') ?? auth()->id();
        $query = Pengguna::where('role', 'Pengurus');

        return $idPengguna
            ? $query->where('id_pengguna', $idPengguna)->first()
            : $query->orderBy('created_at')->first();
    }

    private function scopedAnggota(Pengguna $pengurus, string $idPengguna): Pengguna
    {
        return Pengguna::where('role', 'Anggota')
            ->where('id_kelompok', $pengurus->id_kelompok)
            ->where('id_pengguna', $idPengguna)
            ->firstOrFail();
    }

    private function storePhoto(Request $request): string
    {
        $file = $request->file('foto_profil');
        $filename = 'profil_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/profil'), $filename);

        return 'uploads/profil/' . $filename;
    }
}
