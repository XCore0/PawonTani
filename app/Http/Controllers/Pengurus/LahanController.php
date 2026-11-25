<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pengurus\VerifikasiLahanRequest;
use App\Models\Komoditas;
use App\Services\LahanService;
use App\Services\AuditLogger;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LahanController extends Controller
{
    public function __construct(
        protected LahanService $lahanService
    ) {}

    /**
     * Menampilkan daftar lahan terpaginasi milik anggota dalam kelompok tani Pengurus.
     */
    public function index(Request $request): View
    {
        /** @var \App\Models\Pengguna $pengurus */
        $pengurus = $request->user();

        // Keamanan: Pastikan aktor adalah Pengurus aktif yang memiliki kelompok tani
        abort_unless(
            $pengurus && $pengurus->status === 'Aktif' && $pengurus->role === 'Pengurus' && !empty($pengurus->id_kelompok),
            403,
            'Akses ditolak. Anda harus menjadi Pengurus aktif yang terdaftar dalam kelompok tani.'
        );

        // 1. Query dasar dengan scoping berbasis kelompok tani Pengurus (IDOR prevention)
        $query = $this->lahanService->getLahanQueryForUser($pengurus);

        // 2. Kalkulasi metrik ringkasan KHUSUS untuk kelompok Pengurus tersebut (bukan data global)
        $metricsBase = clone $query;
        $stats = [
            'total' => (clone $metricsBase)->count(),
            'menunggu_verifikasi' => (clone $metricsBase)->where('status_verifikasi', 'menunggu_verifikasi')->count(),
            'terverifikasi' => (clone $metricsBase)->where('status_verifikasi', 'terverifikasi')->count(),
            'perlu_perbaikan' => (clone $metricsBase)->where('status_verifikasi', 'perlu_perbaikan')->count(),
            'pengajuan_siklus' => (clone $metricsBase)->whereHas('siklusBerjalan', function ($q) {
                $q->whereIn('siklus_tanam.status', ['menunggu_selesai', 'menunggu_gagal']);
            })->count(),
        ];

        // 3. Eager load relasi yang diperlukan untuk efisiensi tabel (anti N+1 query)
        $query->with([
            'pengguna:id_pengguna,nama,no_telepon',
            'komoditas:id_komoditas,nama_komoditas,kategori',
            'kelompokTani:id_kelompok,nama_kelompok',
            'siklusBerjalan',
        ]);

        // 4. Filter status verifikasi
        if ($status = $request->query('status_verifikasi') ?? $request->query('status')) {
            $query->where('status_verifikasi', $status);
        }

        // 5. Filter berdasarkan komoditas
        if ($komoditasId = $request->query('id_komoditas')) {
            $query->where('id_komoditas', $komoditasId);
        }

        // 6. Pencarian teks fleksibel pada nama lahan, alamat lahan, atau nama anggota pemilik
        if ($search = $request->query('q')) {
            $likeOperator = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(function ($q) use ($search, $likeOperator) {
                $q->where('nama_lahan', $likeOperator, "%{$search}%")
                  ->orWhere('alamat_lahan', $likeOperator, "%{$search}%")
                  ->orWhereHas('pengguna', function ($userQuery) use ($search, $likeOperator) {
                      $userQuery->where('nama', $likeOperator, "%{$search}%");
                  });
            });
        }

        // 7. Paginasi hasil
        $lahans = $query->orderByDesc('created_at')->paginate(10)->withQueryString();
        $kelompok = $pengurus->kelompokTani;
        $daftarKomoditas = Komoditas::orderBy('nama_komoditas')->get(['id_komoditas', 'nama_komoditas']);

        return view('Pengurus.Content.Lahan.Index', compact(
            'lahans',
            'pengurus',
            'kelompok',
            'stats',
            'daftarKomoditas'
        ));
    }

    /**
     * Menampilkan detail lengkap sebuah lahan beserta riwayat verifikasinya.
     */
    public function show(Request $request, string $lahan): View
    {
        /** @var \App\Models\Pengguna $pengurus */
        $pengurus = $request->user();

        // Keamanan: Pastikan aktor adalah Pengurus aktif
        abort_unless(
            $pengurus && $pengurus->status === 'Aktif' && $pengurus->role === 'Pengurus' && !empty($pengurus->id_kelompok),
            403,
            'Akses ditolak.'
        );

        // Mengambil lahan menggunakan findLahanForUser (otomatis 404 jika di luar kelompok Pengurus)
        $lahanModel = $this->lahanService->findLahanForUser($pengurus, $lahan)
            ->load([
                'pengguna:id_pengguna,nama,nik,no_telepon,alamat,foto_profil',
                'komoditas',
                'kelompokTani',
                'verifikator:id_pengguna,nama,jabatan',
                'riwayatVerifikasi.pengguna:id_pengguna,nama,role,jabatan',
                'aktivitas.pengguna:id_pengguna,nama,foto_profil',
                'dokumenSppt',
                'siklusBerjalan.komoditas',
                'siklusBerjalan.pengguna:id_pengguna,nama',
                'siklusBerjalan.riwayatPengakhiran.pengguna:id_pengguna,nama,role,jabatan',
            ]);

        // Evaluasi kewenangan Ketua aktif
        $isKetua = ($pengurus->role === 'Pengurus'
            && $pengurus->jabatan === 'Ketua'
            && $pengurus->status === 'Aktif'
            && !empty($pengurus->id_kelompok)
            && $pengurus->id_kelompok === $lahanModel->id_kelompok);

        return view('Pengurus.Content.Lahan.Show', [
            'lahan' => $lahanModel,
            'isKetua' => $isKetua,
            'pengurus' => $pengurus,
        ]);
    }

    /**
     * Memproses aksi verifikasi lahan oleh Ketua Kelompok Tani.
     */
    public function verifikasi(VerifikasiLahanRequest $request, string $lahan): RedirectResponse
    {
        /** @var \App\Models\Pengguna $pengurus */
        $pengurus = $request->user();
        $aksi = $request->validated('aksi');
        $catatan = $request->validated('catatan');

        try {
            // Seluruh transisi status didelegasikan ke LahanService (tidak mengubah status langsung di Controller)
            $this->lahanService->verifyLahan($pengurus, $lahan, $aksi, $catatan);
            AuditLogger::log('lahan.verifikasi', 'Pengurus melakukan tindakan verifikasi lahan: ' . $aksi . '.', 'Lahan', $lahan, $pengurus->id_kelompok, $request);

            $messages = [
                'setujui' => 'Lahan berhasil disetujui dan status telah diverifikasi.',
                'perlu_perbaikan' => 'Permintaan perbaikan lahan berhasil dikirim ke anggota.',
                'cabut_verifikasi' => 'Status verifikasi lahan berhasil dicabut.',
            ];
            $flashMessage = $messages[$aksi] ?? 'Status verifikasi lahan berhasil diperbarui.';

            return redirect()->route('pengurus.lahan.show', $lahan)
                ->with('success', $flashMessage);
        } catch (DomainException $e) {
            return redirect()->back()
                ->withErrors(['aksi' => $e->getMessage()])
                ->withInput();
        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        }
    }

    /**
     * Memproses verifikasi pengakhiran siklus tanam oleh Ketua Kelompok Tani.
     */
    public function verifikasiSiklus(Request $request, string $lahan, string $siklus): RedirectResponse
    {
        /** @var \App\Models\Pengguna $pengurus */
        $pengurus = $request->user();

        // Keamanan: Hanya Ketua aktif kelompok yang berhak
        abort_unless(
            $pengurus && $pengurus->status === 'Aktif' && $pengurus->role === 'Pengurus' && $pengurus->jabatan === 'Ketua' && !empty($pengurus->id_kelompok),
            403,
            'Akses ditolak. Hanya Ketua kelompok yang berhak memverifikasi siklus tanam.'
        );

        $keputusan = $request->input('keputusan') ?? $request->input('status_verifikasi');

        $request->validate([
            'keputusan' => ['required_without:status_verifikasi', 'string', 'in:disetujui,ditolak'],
            'status_verifikasi' => ['required_without:keputusan', 'string', 'in:disetujui,ditolak'],
            'catatan' => [$keputusan === 'ditolak' ? 'required' : 'nullable', 'string', 'max:1000'],
        ], [
            'keputusan.required_without' => 'Keputusan verifikasi wajib dipilih.',
            'keputusan.in' => 'Keputusan verifikasi harus disetujui atau ditolak.',
            'status_verifikasi.required_without' => 'Keputusan verifikasi wajib dipilih.',
            'status_verifikasi.in' => 'Keputusan verifikasi harus disetujui atau ditolak.',
            'catatan.required' => 'Catatan alasan penolakan pengakhiran wajib diisi.',
            'catatan.max' => 'Catatan maksimal 1000 karakter.',
        ]);

        $catatan = $request->input('catatan');

        try {
            $siklusTanamService = app(\App\Services\SiklusTanamService::class);
            $siklusTanamService->verifikasiPengakhiran($pengurus, $siklus, [
                'keputusan' => $keputusan,
                'status_verifikasi' => $keputusan,
                'catatan' => $catatan,
            ]);

            AuditLogger::log(
                'siklus_tanam.verifikasi',
                'Ketua memverifikasi pengakhiran siklus tanam: ' . $keputusan . '.',
                'SiklusTanam',
                $siklus,
                $pengurus->id_kelompok,
                $request
            );

            $pesan = $keputusan === 'disetujui'
                ? 'Pengajuan pengakhiran siklus tanam berhasil disetujui.'
                : 'Pengajuan pengakhiran siklus tanam telah ditolak. Siklus kembali aktif.';

            return redirect()->route('pengurus.lahan.show', $lahan)->with('success', $pesan);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('pengurus.lahan.show', $lahan)->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            return redirect()->route('pengurus.lahan.show', $lahan)->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
}

