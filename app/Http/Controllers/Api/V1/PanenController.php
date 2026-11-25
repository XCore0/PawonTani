<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePanenRequest;
use App\Http\Requests\Api\UpdatePanenRequest;
use App\Http\Resources\PanenResource;
use App\Models\Lahan;
use App\Models\Panen;
use App\Models\SiklusTanam;
use App\Services\AuditLogger;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PanenController extends Controller
{
    /**
     * Menampilkan daftar panen milik anggota aktif.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'Anggota' && $user->status === 'Aktif' && $user->anggota?->status_keanggotaan === 'Aktif', 403);

        $query = Panen::with(['lahan.komoditas', 'kelompokTani', 'siklusTanam.komoditas'])
            ->where('id_pengguna', $user->id_pengguna)
            ->where('id_kelompok', $user->id_kelompok);

        // Filter siklus tanam (Phase 4)
        if ($idSiklus = $request->query('id_siklus')) {
            $query->where('id_siklus', $idSiklus);
        }

        // Filter status (menunggu, disetujui, ditolak)
        if ($status = $request->query('status')) {
            $query->where('status', strtolower($status));
        }

        // Filter komoditas / crop_type
        if ($crop = $request->query('crop_type')) {
            $query->where('komoditas_nama', 'ILIKE', "%{$crop}%");
        }

        // Filter lahan
        if ($lahanId = $request->query('id_lahan')) {
            $query->where('id_lahan', $lahanId);
        }

        // Pencarian teks
        if ($search = trim((string) $request->query('q'))) {
            $like = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(function ($q) use ($search, $like) {
                $q->where('varietas', $like, "%{$search}%")
                  ->orWhere('komoditas_nama', $like, "%{$search}%")
                  ->orWhere('kode_panen', $like, "%{$search}%")
                  ->orWhere('catatan', $like, "%{$search}%")
                  ->orWhereHas('lahan', fn ($lq) => $lq->where('nama_lahan', $like, "%{$search}%"));
            });
        }

        $items = $query->orderByDesc('tanggal_panen')->orderByDesc('created_at')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar hasil panen berhasil dimuat.',
            'data' => PanenResource::collection($items),
        ]);
    }

    /**
     * Mencatat panen baru oleh anggota.
     */
    public function store(StorePanenRequest $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'Anggota' && $user->status === 'Aktif' && $user->anggota?->status_keanggotaan === 'Aktif', 403);

        $land = Lahan::where('id_lahan', $request->input('id_lahan'))
            ->where('id_pengguna', $user->id_pengguna)
            ->where('id_kelompok', $user->id_kelompok)
            ->firstOrFail();

        $siklus = SiklusTanam::with('komoditas')
            ->where('id_siklus', $request->input('id_siklus'))
            ->lockForUpdate()
            ->firstOrFail();

        // 1. Otorisasi kepemilikan siklus (BOLA / IDOR protection)
        if ($siklus->id_kelompok !== $user->id_kelompok || $siklus->id_pengguna !== $user->id_pengguna) {
            abort(403, 'Akses ditolak. Siklus tanam ini bukan milik Anda.');
        }

        // 2. Konsistensi relasi Lahan <-> Siklus
        if ($siklus->id_lahan !== $land->id_lahan) {
            return response()->json([
                'success' => false,
                'message' => 'Siklus tanam tidak sesuai dengan lahan yang dipilih.',
                'errors' => [
                    'id_siklus' => ['Siklus tanam tidak sesuai dengan lahan yang dipilih.']
                ],
            ], 422);
        }

        // 3. Validasi status siklus: hanya status 'aktif' yang boleh menerima pencatatan panen baru
        if ($siklus->isMenungguPengakhiran()) {
            return response()->json([
                'success' => false,
                'message' => 'Siklus tanam sedang dalam proses pengakhiran dan tidak dapat menerima catatan panen baru.',
                'errors' => [
                    'id_siklus' => ['Siklus tanam sedang dalam proses pengakhiran dan tidak dapat menerima catatan panen baru.']
                ],
            ], 422);
        }

        if (in_array($siklus->status, ['selesai', 'gagal', 'dibatalkan'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Siklus tanam sudah berakhir dan tidak dapat menerima catatan panen baru.',
                'errors' => [
                    'id_siklus' => ['Siklus tanam sudah berakhir dan tidak dapat menerima catatan panen baru.']
                ],
            ], 422);
        }

        if ($siklus->status !== 'aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Panen hanya dapat dicatat pada siklus tanam yang aktif.',
                'errors' => [
                    'id_siklus' => ['Panen hanya dapat dicatat pada siklus tanam yang aktif.']
                ],
            ], 422);
        }

        try {
            $photoPath = $this->storeHarvestPhoto($request);
            $photoPathsList = $this->storeHarvestPhotoList($request);
            if ($photoPath && empty($photoPathsList)) {
                $photoPathsList = [$photoPath];
            } elseif (empty($photoPath) && !empty($photoPathsList)) {
                $photoPath = $photoPathsList[0];
            }

            // Komoditas dan varietas diambil dari Siklus Tanam sebagai source of truth
            $komoditasNama = $siklus->komoditas?->nama_komoditas ?? $siklus->komoditas_nama ?? $land->komoditas?->nama_komoditas ?? 'Padi';
            $varietas = $siklus->varietas ?? $request->input('varietas', 'Ciherang');
            $musimTanam = $siklus->musim_tanam ?? $request->input('musim_tanam', 'Musim Rendeng');

            $panen = new Panen();
            $panen->id_lahan = $land->id_lahan;
            $panen->id_siklus = $siklus->id_siklus;
            $panen->id_pengguna = $user->id_pengguna;
            $panen->id_kelompok = $user->id_kelompok;
            $panen->id_komoditas = $siklus->id_komoditas;
            $panen->komoditas_nama = $komoditasNama;
            $panen->varietas = $varietas;
            $panen->jenis_panen = $request->input('jenis_panen', 'Gabah Kering Panen (GKP)');
            $panen->tanggal_panen = $request->input('tanggal_panen');
            $panen->waktu_panen = $request->input('waktu_panen', '09:00 WIB');
            $panen->musim_tanam = $musimTanam;
            $panen->berat_kg = $request->input('berat_kg');
            $panen->kualitas_grade = $request->input('kualitas_grade', 'Grade A');
            $panen->kadar_air = $request->input('kadar_air');
            $panen->persen_butir_utuh = $request->input('persen_butir_utuh');
            $panen->persen_hampa_kotoran = $request->input('persen_hampa_kotoran');
            $panen->metode_panen = $request->input('metode_panen', 'Mesin Combine Harvester');
            $panen->biaya_panen = $request->input('biaya_panen', 0);
            $panen->keterangan_tenaga_kerja = $request->input('keterangan_tenaga_kerja');
            $panen->harga_per_kg = $request->input('harga_per_kg', 0);
            $panen->catatan = $request->input('catatan');
            $panen->foto_panen = $photoPath;
            $panen->foto_panen_list = !empty($photoPathsList) ? $photoPathsList : null;
            $panen->status = 'menunggu';

            $panen->saveWithUniqueRetry();

            // Dispatch notifikasi panen baru diajukan setelah commit
            $notificationService = app(NotificationService::class);
            $notificationService->afterCommit(function () use ($notificationService, $panen) {
                $notificationService->notifyPanenDiajukan($panen, false);
            });

            AuditLogger::log(
                'panen.dibuat',
                "Mencatat hasil panen {$panen->varietas} seberat {$panen->berat_kg} kg.",
                'Panen',
                $panen->id_panen,
                $user->id_kelompok,
                $request
            );

            return response()->json([
                'success' => true,
                'message' => 'Catatan hasil panen berhasil disimpan dan menunggu verifikasi poktan.',
                'data' => new PanenResource($panen->load(['lahan', 'kelompokTani'])),
            ], 201);
        } catch (Throwable $e) {
            Log::error('Gagal mencatat panen: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat panen: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Detail satu panen.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'Anggota' && $user->status === 'Aktif' && $user->anggota?->status_keanggotaan === 'Aktif', 403);

        $panen = Panen::with(['lahan.komoditas', 'kelompokTani', 'siklusTanam.komoditas'])
            ->where('id_panen', $id)
            ->where('id_pengguna', $user->id_pengguna)
            ->where('id_kelompok', $user->id_kelompok)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'message' => 'Detail panen berhasil dimuat.',
            'data' => new PanenResource($panen),
        ]);
    }

    /**
     * Memperbarui data panen.
     */
    public function update(UpdatePanenRequest $request, string $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'Anggota' && $user->status === 'Aktif' && $user->anggota?->status_keanggotaan === 'Aktif', 403);

        $panen = Panen::where('id_panen', $id)
            ->where('id_pengguna', $user->id_pengguna)
            ->where('id_kelompok', $user->id_kelompok)
            ->firstOrFail();

        // Hanya boleh edit jika belum disetujui
        if ($panen->status === 'disetujui') {
            return response()->json([
                'success' => false,
                'message' => 'Catatan panen yang sudah disetujui tidak dapat diubah.',
            ], 422);
        }

        // Proteksi relasi siklus tanam: tidak boleh dipindahkan ke siklus lain
        if ($request->has('id_siklus') && $request->input('id_siklus') !== $panen->id_siklus) {
            return response()->json([
                'success' => false,
                'message' => 'Siklus tanam pada catatan panen ini tidak dapat diubah atau dipindahkan.',
                'errors' => [
                    'id_siklus' => ['Siklus tanam pada catatan panen ini tidak dapat diubah atau dipindahkan.']
                ],
            ], 422);
        }

        // Proteksi relasi petak lahan: tidak boleh dipindahkan ke lahan lain
        if ($request->filled('id_lahan') && $request->input('id_lahan') !== $panen->id_lahan) {
            return response()->json([
                'success' => false,
                'message' => 'Lahan pada catatan panen ini tidak dapat dipindahkan.',
                'errors' => [
                    'id_lahan' => ['Lahan pada catatan panen ini tidak dapat dipindahkan.']
                ],
            ], 422);
        }

        // Locking jika siklus tanam sedang dalam proses pengakhiran atau telah berakhir
        if ($panen->id_siklus !== null) {
            $siklus = \App\Models\SiklusTanam::find($panen->id_siklus);
            if ($siklus && ($siklus->isMenungguVerifikasiPengakhiran() || in_array($siklus->status, \App\Models\SiklusTanam::STATUSES_TERMINAL, true))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Catatan panen tidak dapat diubah karena siklus tanam sedang dalam proses pengakhiran atau telah berakhir.',
                    'errors' => [
                        'id_siklus' => ['Siklus tanam sedang dalam proses pengakhiran atau telah berakhir.']
                    ],
                ], 422);
            }
        }

        $isRevision = $panen->status === 'ditolak';

        try {
            // Foto lama yang dipertahankan dikirim klien lewat `existing_photos`.
            // Bila key ini tidak dikirim sama sekali, daftar foto dibiarkan apa adanya
            // (kompatibel dengan klien lama yang hanya mengirim foto baru).
            $fotoTersimpan = $this->daftarFotoTersimpan($panen);
            $keptOld = null;

            if ($request->has('existing_photos')) {
                $keptOld = $this->normalisasiDaftarFoto($request->input('existing_photos', []));
                // Batasi ke berkas milik catatan panen ini supaya path asing
                // tidak bisa dihapus maupun disisipkan ke daftar foto.
                $keptOld = array_values(array_intersect($keptOld, $fotoTersimpan));
            }

            $fotoBaru = $this->storeHarvestPhotoList($request);
            if ($request->hasFile('foto_panen')) {
                $fotoUtama = $this->storeHarvestPhoto($request);
                if ($fotoUtama) {
                    $fotoBaru[] = $fotoUtama;
                }
            }

            if ($keptOld !== null || !empty($fotoBaru)) {
                // Gabungkan foto lama yang dipertahankan dengan foto baru (append),
                // bukan menimpa daftar lama.
                $finalPhotos = $this->gabungkanFoto(
                    $keptOld ?? $fotoTersimpan,
                    $fotoBaru
                );

                $panen->foto_panen_list = !empty($finalPhotos) ? $finalPhotos : null;
                $panen->foto_panen = $finalPhotos[0] ?? null;
            }

            if ($isRevision) {
                $panen->status = 'menunggu';
            }

            foreach ([
                'varietas', 'jenis_panen', 'tanggal_panen', 'waktu_panen',
                'musim_tanam', 'berat_kg', 'kualitas_grade', 'kadar_air', 'persen_butir_utuh',
                'persen_hampa_kotoran', 'metode_panen', 'biaya_panen', 'keterangan_tenaga_kerja',
                'harga_per_kg', 'catatan',
            ] as $field) {
                if ($request->has($field)) {
                    $panen->{$field} = $request->input($field);
                }
            }

            $panen->save();

            // Dispatch notifikasi pengajuan ulang panen jika berupa revisi
            if ($isRevision) {
                $notificationService = app(NotificationService::class);
                $notificationService->afterCommit(function () use ($notificationService, $panen) {
                    $notificationService->notifyPanenDiajukan($panen, true);
                });
            }

            // Setelah perubahan tersimpan, hapus berkas lama yang benar-benar
            // tidak lagi dipertahankan pengguna (dihapus satu per satu di UI).
            if ($keptOld !== null || !empty($fotoBaru)) {
                $finalPhotos = is_array($panen->foto_panen_list) ? $panen->foto_panen_list : [];
                foreach (array_diff($fotoTersimpan, $finalPhotos) as $pathLama) {
                    if ($pathLama && Storage::disk('public')->exists($pathLama)) {
                        Storage::disk('public')->delete($pathLama);
                    }
                }
            }

            AuditLogger::log(
                $isRevision ? 'panen.direvisi' : 'panen.diperbarui',
                $isRevision
                    ? "Merevisi catatan panen {$panen->kode_panen} dan mengajukan ulang verifikasi."
                    : "Memperbarui catatan panen {$panen->kode_panen}.",
                'Panen',
                $panen->id_panen,
                $user->id_kelompok,
                $request
            );

            return response()->json([
                'success' => true,
                'message' => $isRevision
                    ? 'Catatan panen berhasil direvisi dan diajukan ulang untuk verifikasi.'
                    : 'Catatan panen berhasil diperbarui.',
                'data' => new PanenResource($panen->load(['lahan', 'kelompokTani', 'siklusTanam.komoditas'])),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui catatan panen: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menghapus catatan panen (soft delete).
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'Anggota' && $user->status === 'Aktif' && $user->anggota?->status_keanggotaan === 'Aktif', 403);

        $panen = Panen::where('id_panen', $id)
            ->where('id_pengguna', $user->id_pengguna)
            ->where('id_kelompok', $user->id_kelompok)
            ->firstOrFail();

        if ($panen->id_siklus !== null) {
            $siklus = \App\Models\SiklusTanam::find($panen->id_siklus);
            if ($siklus && ($siklus->isMenungguVerifikasiPengakhiran() || in_array($siklus->status, \App\Models\SiklusTanam::STATUSES_TERMINAL, true))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Catatan panen tidak dapat dihapus karena siklus tanam sedang dalam proses pengakhiran atau telah berakhir.',
                ], 422);
            }
        }

        $panen->delete();

        AuditLogger::log(
            'panen.dihapus',
            "Menghapus catatan panen {$panen->kode_panen}.",
            'Panen',
            $panen->id_panen,
            $user->id_kelompok,
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Catatan panen berhasil dihapus.',
        ]);
    }

    /**
     * Daftar foto yang tersimpan pada satu catatan panen, dalam bentuk path
     * relatif disk "public". Foto utama selalu berada di urutan pertama.
     */
    protected function daftarFotoTersimpan(Panen $panen): array
    {
        $photos = [];

        if (!empty($panen->foto_panen)) {
            $photos[] = (string) $panen->foto_panen;
        }

        if (is_array($panen->foto_panen_list)) {
            foreach ($panen->foto_panen_list as $path) {
                $path = is_string($path) ? trim($path) : '';
                if ($path !== '' && !in_array($path, $photos, true)) {
                    $photos[] = $path;
                }
            }
        }

        return $photos;
    }

    /**
     * Menormalkan `existing_photos` kiriman klien menjadi daftar path relatif.
     *
     * Klien mobile mengirim URL penuh (hasil PanenResource) sedangkan database
     * menyimpan path relatif; keduanya diterima agar pencocokan berkas benar.
     */
    protected function normalisasiDaftarFoto(mixed $input): array
    {
        if (is_string($input)) {
            $input = [$input];
        }

        if (!is_array($input)) {
            return [];
        }

        $paths = [];
        foreach ($input as $item) {
            if (!is_string($item)) {
                continue;
            }
            $path = $this->normalisasiPathFoto($item);
            if ($path !== null && !in_array($path, $paths, true)) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    protected function normalisasiPathFoto(string $value): ?string
    {
        $path = trim($value);
        if ($path === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $path)) {
            $path = (string) parse_url($path, PHP_URL_PATH);
        }

        $path = ltrim($path, '/');
        $path = preg_replace('#^(?:media|storage)/#i', '', $path) ?? '';

        if ($path === '' || str_contains($path, '..')) {
            return null;
        }

        return $path;
    }

    /**
     * Menggabungkan foto lama yang dipertahankan dengan foto baru.
     * Urutan dipertahankan (foto lama dulu) dan duplikat dibuang.
     */
    protected function gabungkanFoto(array $lama, array $baru): array
    {
        return array_values(array_unique(array_merge($lama, $baru)));
    }

    protected function storeHarvestPhoto(Request $request): ?string
    {
        if (!$request->hasFile('foto_panen')) {
            return null;
        }

        $file = $request->file('foto_panen');
        $filename = 'panen_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $this->ekstensiFoto($file);
        return $file->storeAs('panen', $filename, 'public');
    }

    /**
     * Menentukan ekstensi berkas yang aman.
     *
     * Foto dari Flutter Web (terutama hasil kamera) bisa datang tanpa ekstensi,
     * sehingga getClientOriginalExtension() mengembalikan string kosong dan
     * nama berkas berakhir dengan titik. Urutan penentuan: ekstensi asli,
     * ekstensi hasil deteksi MIME, lalu 'jpg'.
     */
    protected function ekstensiFoto(UploadedFile $file): string
    {
        $ext = strtolower((string) $file->getClientOriginalExtension());
        if ($ext === '') {
            $ext = strtolower((string) $file->extension());
        }
        return $ext !== '' ? $ext : 'jpg';
    }

    protected function storeHarvestPhotoList(Request $request): array
    {
        if (!$request->hasFile('foto_panen_list')) {
            return [];
        }

        $files = $request->file('foto_panen_list');
        if (!is_array($files)) {
            $files = [$files];
        }

        $paths = [];
        foreach ($files as $file) {
            if ($file && $file->isValid()) {
                $filename = 'panen_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $this->ekstensiFoto($file);
                $path = $file->storeAs('panen', $filename, 'public');
                if ($path) {
                    $paths[] = $path;
                }
            }
        }
        return $paths;
    }

    /**
     * Verifikasi catatan panen oleh Pengurus (Ketua) via API.
     */
    public function verifikasi(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        abort_unless(
            $user && $user->role === 'Pengurus' && $user->status === 'Aktif' && !empty($user->id_kelompok),
            403,
            'Akses ditolak. Anda harus menjadi Pengurus aktif yang terdaftar dalam kelompok tani.'
        );

        $request->validate([
            'status' => ['required', 'in:disetujui,ditolak,menunggu'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:500'],
        ]);

        $panen = Panen::where('id_kelompok', $user->id_kelompok)
            ->where('id_panen', $id)
            ->firstOrFail();

        $statusLama = $panen->status;
        $statusBaru = $request->input('status');

        $panen->status = $statusBaru;
        $panen->diverifikasi_oleh = $user->nama;
        $panen->tanggal_verifikasi = now();
        $panen->catatan_verifikasi = $request->input('catatan_verifikasi');
        $panen->save();

        // Dispatch notifikasi hasil verifikasi panen setelah commit
        $notificationService = app(NotificationService::class);
        if ($statusBaru === 'disetujui') {
            $notificationService->afterCommit(function () use ($notificationService, $panen, $user) {
                $notificationService->notifyPanenDisetujui($panen, $user, $panen->catatan_verifikasi);
            });
        } elseif ($statusBaru === 'ditolak') {
            $notificationService->afterCommit(function () use ($notificationService, $panen, $user) {
                $notificationService->notifyPanenDitolak($panen, $user, $panen->catatan_verifikasi);
            });
        }

        AuditLogger::log(
            'panen.diverifikasi',
            "Memverifikasi hasil panen {$panen->kode_panen} dari '{$statusLama}' menjadi '{$statusBaru}'.",
            'Panen',
            $panen->id_panen,
            $user->id_kelompok,
            $request
        );

        return response()->json([
            'success' => true,
            'message' => "Hasil panen ({$panen->kode_panen}) berhasil diverifikasi menjadi {$statusBaru}.",
            'data' => new PanenResource($panen->load(['lahan', 'kelompokTani', 'siklusTanam.komoditas'])),
        ]);
    }
}
