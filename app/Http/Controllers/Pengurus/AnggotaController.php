<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Models\AktivitasTanam;
use App\Models\Anggota;
use App\Models\Lahan;
use App\Models\Pengguna;
use App\Services\AuditLogger;
use App\Services\DocumentStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use RuntimeException;

class AnggotaController extends Controller
{
    public function __construct(private readonly DocumentStorageService $documents) {}

    public function index(Request $request)
    {
        $pengurus = $this->currentPengurus();
        abort_unless($pengurus?->id_kelompok, 403, 'Pengurus belum memiliki kelompok tani.');

        $kelompok = $pengurus->kelompokTani;
        abort_unless($kelompok && $kelompok->status === 'Aktif', 403, 'Kelompok tani Anda tidak aktif. Hubungi administrator.');

        $isArchive = $request->boolean('arsip');
        $anggotaQuery = Pengguna::with(['kelompokTani', 'anggota', 'dokumenIdentitas'])
            ->where('role', 'Anggota')
            ->where('id_kelompok', $pengurus->id_kelompok);

        if ($isArchive) {
            $anggotaQuery->whereHas('anggota', fn ($q) => $q->where('status_keanggotaan', 'Keluar'));
        } else {
            $anggotaQuery->whereHas('anggota', fn ($q) => $q->where('status_keanggotaan', '!=', 'Keluar'));
        }

        $anggota = $anggotaQuery->orderByDesc('created_at')->get();
        $jumlahAktif = Pengguna::where('role', 'Anggota')->where('id_kelompok', $pengurus->id_kelompok)
            ->whereHas('anggota', fn ($q) => $q->where('status_keanggotaan', 'Aktif'))->count();
        $jumlahArsip = Pengguna::where('role', 'Anggota')->where('id_kelompok', $pengurus->id_kelompok)
            ->whereHas('anggota', fn ($q) => $q->where('status_keanggotaan', 'Keluar'))->count();

        return view('Pengurus.Content.Anggota', compact('anggota', 'kelompok', 'pengurus', 'isArchive', 'jumlahAktif', 'jumlahArsip'));
    }

    /**
     * Mencari akun anggota yang pernah keluar agar dapat dimasukkan kembali
     * ke kelompok Pengurus tanpa membuat akun atau mengunggah dokumen ulang.
     */
    public function searchFormerMembers(Request $request)
    {
        $pengurus = $this->currentPengurus();
        abort_unless($pengurus->id_kelompok && $pengurus->kelompokTani?->status === 'Aktif', 403, 'Kelompok tani Anda tidak aktif.');

        $search = trim((string) $request->query('q', ''));
        if (strlen($search) < 2) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $operator = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
        $members = Pengguna::query()
            ->where('role', 'Anggota')
            ->where('status', '!=', 'Dihapus')
            ->whereHas('anggota', fn ($query) => $query->where('status_keanggotaan', 'Keluar'))
            ->where('nama', $operator, '%' . $search . '%')
            ->with('kelompokTani:id_kelompok,nama_kelompok')
            ->orderBy('nama')
            ->limit(15)
            ->get(['id_pengguna', 'nama', 'nik', 'no_telepon', 'id_kelompok']);

        return response()->json([
            'success' => true,
            'data' => $members->map(fn (Pengguna $member) => [
                'id_pengguna' => $member->id_pengguna,
                'nama' => $member->nama,
                'nik' => $member->nik,
                'no_telepon' => $member->no_telepon,
                'kelompok_sebelumnya' => $member->kelompokTani?->nama_kelompok ?? 'Belum terdaftar',
            ])->values(),
        ]);
    }

    /**
     * Mengaktifkan kembali anggota lama pada kelompok Pengurus yang sedang login.
     * Akun, dokumen identitas, SPPT, lahan, dan riwayat aktivitas tetap dipakai.
     */
    public function restoreFormerMember(Request $request, string $id_pengguna)
    {
        $pengurus = $this->currentPengurus();
        abort_unless($pengurus->id_kelompok && $pengurus->kelompokTani?->status === 'Aktif', 403, 'Kelompok tani Anda tidak aktif.');

        $previousGroupName = null;
        $memberName = null;

        DB::transaction(function () use ($pengurus, $id_pengguna, &$previousGroupName, &$memberName) {
            $member = Pengguna::query()
                ->where('role', 'Anggota')
                ->whereKey($id_pengguna)
                ->lockForUpdate()
                ->firstOrFail();

            abort_if($member->status === 'Dihapus', 422, 'Akun anggota sudah dihapus dan tidak dapat diaktifkan kembali.');

            $membership = Anggota::where('id_pengguna', $member->id_pengguna)
                ->lockForUpdate()
                ->firstOrFail();
            abort_unless($membership->status_keanggotaan === 'Keluar', 422, 'Hanya anggota berstatus Keluar yang dapat dimasukkan kembali.');

            $previousGroupName = $member->kelompokTani?->nama_kelompok;
            $memberName = $member->nama;

            // Kembalikan akun dan keanggotaan ke kelompok Pengurus saat ini.
            $member->forceFill([
                'id_kelompok' => $pengurus->id_kelompok,
                'status' => 'Aktif',
            ])->save();

            $membership->update([
                'status_keanggotaan' => 'Aktif',
                'tanggal_keluar' => null,
                'alasan_keluar' => null,
                'dikeluarkan_oleh' => null,
            ]);

            // Data lahan dan aktivitas mengikuti kelompok baru agar terlihat kembali
            // pada halaman Pengurus dan tetap dapat diakses melalui aplikasi mobile.
            Lahan::where('id_pengguna', $member->id_pengguna)
                ->update(['id_kelompok' => $pengurus->id_kelompok]);
            AktivitasTanam::where('id_pengguna', $member->id_pengguna)
                ->update(['id_kelompok' => $pengurus->id_kelompok]);
        });

        AuditLogger::log(
            'anggota.dimasukkan_kembali',
            'Pengurus memasukkan kembali anggota ' . ($memberName ?? $id_pengguna) . ' ke kelompok ' . $pengurus->kelompokTani->nama_kelompok . '. Kelompok sebelumnya: ' . ($previousGroupName ?? '-'),
            'Pengguna',
            $id_pengguna,
            $pengurus->id_kelompok,
            $request
        );

        return redirect()->route('pengurus.anggota')->with('success', ($memberName ?? 'Anggota') . ' berhasil dimasukkan kembali ke kelompok. Dokumen dan data sebelumnya tetap digunakan.');
    }

    public function store(Request $request)
    {
        $pengurus = $this->currentPengurus();
        abort_unless($pengurus?->id_kelompok, 403, 'Pengurus belum memiliki kelompok tani.');
        abort_unless($pengurus->kelompokTani?->status === 'Aktif', 403, 'Kelompok tani Anda tidak aktif. Hubungi administrator.');

        $request->merge(['username' => strtolower($request->input('username', ''))]);
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'regex:/^[\pL\s.\'-]+$/u'],
            'nik' => ['nullable', 'digits:16', 'unique:pengguna,nik'],
            'username' => 'required|string|max:50|alpha_dash|unique:pengguna,username',
            'password' => 'required|string|min:8',
            'email' => 'nullable|email|max:255|unique:pengguna,email',
            'no_telepon' => ['nullable', 'regex:/^[0-9]+$/', 'max:20'],
            'alamat' => 'nullable|string',
            'status' => 'required|in:Aktif,Tidak Aktif',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'dokumen_ktp' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg', 'max:5120'],
            'dokumen_kk' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg', 'max:5120'],
        ], $this->documentValidationMessages());

        $fotoPath = null;
        if ($request->hasFile('foto_profil')) {
            $fotoPath = $this->storePhoto($request);
        }

        $member = DB::transaction(function () use ($validated, $pengurus, $fotoPath) {
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

            return $pengguna;
        });

        try {
            if ($request->hasFile('dokumen_ktp')) {
                $this->documents->storeIdentity($member, 'KTP', $request->file('dokumen_ktp'), $pengurus);
            }
            if ($request->hasFile('dokumen_kk')) {
                $this->documents->storeIdentity($member, 'KK', $request->file('dokumen_kk'), $pengurus);
            }
        } catch (RuntimeException $exception) {
            report($exception);
            return redirect()->route('pengurus.anggota')->with('warning', 'Data anggota berhasil dibuat, tetapi dokumen gagal disimpan. Silakan edit anggota dan unggah ulang dokumen.');
        }

        AuditLogger::log('anggota.tambah', 'Menambahkan anggota ' . $member->nama, 'Pengguna', $member->id_pengguna, $member->id_kelompok, $request);

        return redirect()->route('pengurus.anggota')->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function update(Request $request, string $id_pengguna)
    {
        $pengurus = $this->currentPengurus();
        abort_unless($pengurus?->id_kelompok && $pengurus->kelompokTani?->status === 'Aktif', 403, 'Kelompok tani Anda tidak aktif.');
        $anggota = $this->scopedAnggota($pengurus, $id_pengguna);

        $request->merge(['username' => strtolower($request->input('username', ''))]);
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'regex:/^[\pL\s.\'-]+$/u'],
            'nik' => ['nullable', 'digits:16', Rule::unique('pengguna', 'nik')->ignore($anggota->id_pengguna, 'id_pengguna')],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('pengguna', 'username')->ignore($anggota->id_pengguna, 'id_pengguna')],
            'password' => 'nullable|string|min:8',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('pengguna', 'email')->ignore($anggota->id_pengguna, 'id_pengguna')],
            'no_telepon' => ['nullable', 'regex:/^[0-9]+$/', 'max:20'],
            'alamat' => 'nullable|string',
            'status' => 'required|in:Aktif,Tidak Aktif',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'dokumen_ktp' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg', 'max:5120'],
            'dokumen_kk' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg', 'max:5120'],
        ], $this->documentValidationMessages());

        DB::transaction(function () use ($request, $validated, $anggota) {
            $anggota->fill([
                'nama' => $validated['nama'],
                'nik' => $validated['nik'] ?? null,
                'username' => $validated['username'],
                'email' => $validated['email'] ?? null,
                'no_telepon' => $validated['no_telepon'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
                'status' => $validated['status'],
            ]);

            if (!empty($validated['password'])) {
                $anggota->password = Hash::make($validated['password']);
            }
            if ($request->hasFile('foto_profil')) {
                $newPhoto = $this->storePhoto($request);
                $oldPhoto = $anggota->foto_profil;
                $anggota->foto_profil = $newPhoto;
                if ($oldPhoto) {
                    try {
                        app(\App\Services\CloudinaryService::class)->delete($oldPhoto);
                    } catch (\Throwable $exception) {
                        report($exception);
                    }
                }
            }
            $anggota->save();
            $anggota->anggota()->update(['status_keanggotaan' => $validated['status']]);
        });

        try {
            if ($request->hasFile('dokumen_ktp')) {
                $this->documents->storeIdentity($anggota, 'KTP', $request->file('dokumen_ktp'), $pengurus);
            }
            if ($request->hasFile('dokumen_kk')) {
                $this->documents->storeIdentity($anggota, 'KK', $request->file('dokumen_kk'), $pengurus);
            }
        } catch (RuntimeException $exception) {
            report($exception);
            return redirect()->route('pengurus.anggota')->with('warning', 'Data anggota diperbarui, tetapi salah satu dokumen gagal disimpan. Coba unggah ulang.');
        }

        AuditLogger::log('anggota.ubah', 'Memperbarui data anggota ' . $anggota->nama, 'Pengguna', $anggota->id_pengguna, $anggota->id_kelompok, $request);

        return redirect()->route('pengurus.anggota')->with('success', 'Data anggota berhasil diperbarui.');
    }

    /** Pengurus hanya mengeluarkan anggota dari kelompok, bukan menghapus akun. */
    public function destroy(Request $request, string $id_pengguna)
    {
        $pengurus = $this->currentPengurus();
        abort_unless($pengurus?->id_kelompok && $pengurus->kelompokTani?->status === 'Aktif', 403, 'Kelompok tani Anda tidak aktif.');
        $validated = $request->validate([
            'alasan_keluar' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $anggota = $this->scopedAnggota($pengurus, $id_pengguna);
        $name = $anggota->nama;

        DB::transaction(function () use ($anggota, $pengurus, $validated) {
            $anggota->anggota()->updateOrCreate(
                ['id_pengguna' => $anggota->id_pengguna],
                [
                    'status_keanggotaan' => 'Keluar',
                    'tanggal_keluar' => now(),
                    'alasan_keluar' => $validated['alasan_keluar'],
                    'dikeluarkan_oleh' => $pengurus->id_pengguna,
                ]
            );
        });

        AuditLogger::log('anggota.keluar', 'Mengeluarkan anggota ' . $name . ' dari kelompok. Alasan: ' . $validated['alasan_keluar'], 'Pengguna', $anggota->id_pengguna, $pengurus->id_kelompok, $request);

        return redirect()->route('pengurus.anggota')->with('success', "{$name} telah dikeluarkan dari kelompok. Akun dan riwayatnya tetap tersimpan.");
    }

    private function currentPengurus(): Pengguna
    {
        $pengurus = Auth::user();
        abort_unless(
            $pengurus instanceof Pengguna && $pengurus->role === 'Pengurus' && $pengurus->status === 'Aktif',
            403,
            'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.'
        );
        return $pengurus;
    }

    private function scopedAnggota(Pengguna $pengurus, string $idPengguna): Pengguna
    {
        return Pengguna::where('role', 'Anggota')
            ->where('id_kelompok', $pengurus->id_kelompok)
            ->where('id_pengguna', $idPengguna)
            ->whereHas('anggota', fn ($q) => $q->where('status_keanggotaan', '!=', 'Keluar'))
            ->firstOrFail();
    }

    private function storePhoto(Request $request): string
    {
        return app(\App\Services\CloudinaryService::class)->upload($request->file('foto_profil')->getRealPath(), 'pawontani/profil');
    }

    private function documentValidationMessages(): array
    {
        return [
            'dokumen_ktp.mimes' => 'Format KTP tidak didukung. Gunakan PDF atau JPG.',
            'dokumen_ktp.max' => 'Ukuran KTP maksimal 5 MB.',
            'dokumen_kk.mimes' => 'Format KK tidak didukung. Gunakan PDF atau JPG.',
            'dokumen_kk.max' => 'Ukuran KK maksimal 5 MB.',
        ];
    }
}
