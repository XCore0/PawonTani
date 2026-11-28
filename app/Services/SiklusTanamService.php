<?php

namespace App\Services;

use App\Models\AktivitasTanam;
use App\Models\Komoditas;
use App\Models\Lahan;
use App\Models\Panen;
use App\Models\Pengguna;
use App\Models\RiwayatPengakhiranSiklus;
use App\Models\SiklusTanam;
use Carbon\Carbon;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SiklusTanamService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}
    /**
     * Dapatkan query builder Siklus Tanam yang di-scope berdasarkan konteks aktor.
     * Mencegah IDOR (Insecure Direct Object Reference) pada level query database.
     *
     * - Anggota Aktif: Hanya siklus pada lahan miliknya sendiri.
     * - Pengurus Aktif: Hanya siklus anggota dalam kelompok tani miliknya.
     * - Aktor Lain: Ditolak (query kosong).
     */
    public function getSiklusQueryForUser(Pengguna $user, ?string $idLahan = null): Builder
    {
        if ($user->status !== 'Aktif') {
            return SiklusTanam::query()->whereRaw('1 = 0');
        }

        $query = SiklusTanam::query();

        if ($user->role === 'Anggota') {
            if (empty($user->id_kelompok) || $user->anggota?->status_keanggotaan !== 'Aktif') {
                return SiklusTanam::query()->whereRaw('1 = 0');
            }

            // Anggota hanya dapat melihat siklus pada lahan miliknya
            $query->where('id_pengguna', $user->id_pengguna)
                  ->where('id_kelompok', $user->id_kelompok);
        } elseif ($user->role === 'Pengurus') {
            if (empty($user->id_kelompok)) {
                return SiklusTanam::query()->whereRaw('1 = 0');
            }

            // Pengurus dapat melihat siklus anggota dalam kelompoknya
            $query->where('id_kelompok', $user->id_kelompok);
        } else {
            return SiklusTanam::query()->whereRaw('1 = 0');
        }

        if ($idLahan) {
            $query->where('id_lahan', $idLahan);
        }

        return $query;
    }

    /**
     * Cari satu Siklus Tanam dengan otorisasi aktor.
     */
    public function findSiklusForUser(Pengguna $user, string $idSiklus): SiklusTanam
    {
        return $this->getSiklusQueryForUser($user)
            ->where('id_siklus', $idSiklus)
            ->firstOrFail();
    }

    /**
     * Ambil siklus berjalan pada lahan tertentu untuk pengguna.
     */
    public function getSiklusBerjalanForLahan(Pengguna $user, string $idLahan): ?SiklusTanam
    {
        return $this->getSiklusQueryForUser($user, $idLahan)
            ->berjalan()
            ->with(['komoditas', 'kelompokTani', 'pengguna'])
            ->first();
    }

    /**
     * Membuat Siklus Tanam baru secara transaksional dengan row locking & concurrency protection.
     *
     * @param Pengguna $user
     * @param array $data
     * @return SiklusTanam
     * @throws AuthorizationException|DomainException|QueryException
     */
    public function createSiklus(Pengguna $user, array $data): SiklusTanam
    {
        if ($user->status !== 'Aktif' || $user->role !== 'Anggota') {
            throw new AuthorizationException('Akses ditolak. Hanya Anggota aktif yang dapat membuat Siklus Tanam.');
        }

        return DB::transaction(function () use ($user, $data) {
            // 1. Lock baris Lahan untuk mencegah race condition concurrent creation
            $lahan = Lahan::where('id_lahan', $data['id_lahan'])
                ->lockForUpdate()
                ->first();

            if (!$lahan) {
                throw new ModelNotFoundException('Data lahan tidak ditemukan.');
            }

            // 2. Validasi kepemilikan lahan secara sah
            if ($lahan->id_pengguna !== $user->id_pengguna || $lahan->id_kelompok !== $user->id_kelompok) {
                throw new AuthorizationException('Anda tidak memiliki hak akses untuk mengelola siklus tanam pada lahan ini.');
            }

            // 3. Validasi status verifikasi Lahan (Wajib terverifikasi)
            if ($lahan->status_verifikasi !== 'terverifikasi') {
                throw new DomainException('Lahan belum dapat digunakan untuk memulai Siklus Tanam karena belum terverifikasi.');
            }

            // 4. Validasi status operasional Lahan (Harus persiapan untuk transisi ke aktif)
            $statusOperasional = strtolower($lahan->status_operasional ?? 'aktif');
            if ($statusOperasional === 'aktif') {
                throw new DomainException('Lahan sedang dalam masa tanam aktif.');
            } elseif ($statusOperasional === 'istirahat') {
                throw new DomainException('Lahan harus berada dalam status persiapan sebelum memulai siklus tanam baru.');
            } elseif ($statusOperasional !== 'persiapan') {
                throw new DomainException('Status operasional lahan tidak mengizinkan pembuatan siklus tanam baru.');
            }

            // 5. Validasi siklus berjalan di dalam transaksi (Double-check inside lock)
            $hasRunning = SiklusTanam::where('id_lahan', $lahan->id_lahan)
                ->whereIn('status', SiklusTanam::STATUSES_BERJALAN)
                ->exists();

            if ($hasRunning) {
                throw new DomainException('Lahan ini sudah memiliki siklus tanam yang sedang berjalan.');
            }

            // 6. Validasi tanggal mulai tidak boleh di masa depan (future date)
            $tanggalMulai = $data['tanggal_mulai'];
            if ($tanggalMulai > now()->toDateString()) {
                throw new DomainException('Tanggal mulai siklus tanam tidak boleh melebihi tanggal hari ini.');
            }

            // 7. Ambil master komoditas untuk snapshot nama komoditas
            $komoditas = Komoditas::findOrFail($data['id_komoditas']);

            // 8. Buat entitas Siklus Tanam
            $siklus = new SiklusTanam();
            $siklus->id_lahan = $lahan->id_lahan;
            $siklus->id_pengguna = $user->id_pengguna;
            $siklus->id_kelompok = $lahan->id_kelompok ?? $user->id_kelompok;
            $siklus->id_komoditas = $komoditas->id_komoditas;
            $siklus->komoditas_nama = $komoditas->nama_komoditas;
            $siklus->varietas = $data['varietas'] ?? null;
            $siklus->musim_tanam = $data['musim_tanam'] ?? null;
            $siklus->tanggal_mulai = $tanggalMulai;

            // Estimasi panen dihitung otomatis dari umur panen komoditas bila anggota
            // tidak mengirimkannya. Nilai manual tetap dihormati (mis. koreksi lapangan).
            $siklus->estimasi_panen = $data['estimasi_panen']
                ?? ($komoditas->umur_panen_hari
                    ? Carbon::parse($tanggalMulai)->addDays((int) $komoditas->umur_panen_hari)->toDateString()
                    : null);

            $siklus->target_panen_ton = $data['target_panen_ton'] ?? null;
            $siklus->status = SiklusTanam::STATUS_AKTIF;
            $siklus->save();

            // 9. Transisi status operasional Lahan menjadi 'aktif'
            // Perhatian: Data identitas fisik Lahan tetap tidak diubah
            $lahan->status_operasional = 'aktif';
            $lahan->save();

            return $siklus->load(['komoditas', 'kelompokTani', 'pengguna']);
        });
    }

    /**
     * Memulai persiapan Lahan oleh Anggota pemilik.
     * Mengubah status operasional lahan dari 'istirahat' -> 'persiapan'.
     * Boleh dilakukan tanpa memandang status verifikasi lahan.
     */
    public function mulaiPersiapanLahan(Pengguna $user, string $idLahan): Lahan
    {
        if ($user->status !== 'Aktif' || $user->role !== 'Anggota') {
            throw new AuthorizationException('Akses ditolak. Hanya Anggota aktif yang dapat mengubah status persiapan lahan.');
        }

        return DB::transaction(function () use ($user, $idLahan) {
            $lahan = Lahan::where('id_lahan', $idLahan)
                ->lockForUpdate()
                ->first();

            if (!$lahan) {
                throw new ModelNotFoundException('Data lahan tidak ditemukan.');
            }

            if ($lahan->id_pengguna !== $user->id_pengguna || $lahan->id_kelompok !== $user->id_kelompok) {
                throw new AuthorizationException('Anda tidak memiliki hak akses untuk mengelola lahan ini.');
            }

            $statusOperasional = strtolower($lahan->status_operasional ?? 'aktif');

            if ($statusOperasional === 'persiapan') {
                throw new DomainException('Lahan sudah dalam status persiapan.');
            }

            if ($statusOperasional === 'aktif') {
                throw new DomainException('Lahan sedang dalam masa tanam aktif, tidak dapat memulai persiapan.');
            }

            if ($statusOperasional !== 'istirahat') {
                throw new DomainException("Status operasional lahan saat ini ({$lahan->status_operasional}) tidak memungkinkan untuk memulai persiapan.");
            }

            // Ubah status operasional ke 'persiapan' tanpa mengubah status verifikasi
            $lahan->status_operasional = 'persiapan';
            $lahan->save();

            return $lahan;
        });
    }

    /**
     * Membatalkan persiapan Lahan oleh Anggota pemilik.
     * Mengubah status operasional lahan dari 'persiapan' -> 'istirahat'.
     */
    public function batalkanPersiapanLahan(Pengguna $user, string $idLahan): Lahan
    {
        if ($user->status !== 'Aktif' || $user->role !== 'Anggota') {
            throw new AuthorizationException('Akses ditolak. Hanya Anggota aktif yang dapat mengubah status persiapan lahan.');
        }

        return DB::transaction(function () use ($user, $idLahan) {
            $lahan = Lahan::where('id_lahan', $idLahan)
                ->lockForUpdate()
                ->first();

            if (!$lahan) {
                throw new ModelNotFoundException('Data lahan tidak ditemukan.');
            }

            if ($lahan->id_pengguna !== $user->id_pengguna || $lahan->id_kelompok !== $user->id_kelompok) {
                throw new AuthorizationException('Anda tidak memiliki hak akses untuk mengelola lahan ini.');
            }

            $statusOperasional = strtolower($lahan->status_operasional ?? 'aktif');

            if ($statusOperasional !== 'persiapan') {
                throw new DomainException('Hanya lahan dengan status persiapan yang dapat dibatalkan persiapannya.');
            }

            // Kembalikan ke 'istirahat' tanpa mengubah status verifikasi
            $lahan->status_operasional = 'istirahat';
            $lahan->save();

            return $lahan;
        });
    }

    /**
     * WORKFLOW A — Pengajuan Pengakhiran Berhasil (status -> 'menunggu_selesai').
     *
     * Syarat:
     * 1. Siklus berstatus 'aktif'.
     * 2. Minimal 1 catatan Panen terhubung ke siklus ini.
     * 3. Seluruh catatan Panen pada siklus ini HARUS berstatus 'disetujui' (tidak boleh ada yang 'menunggu' atau 'ditolak').
     * 4. Alasan pengakhiran wajib diisi.
     * 5. Foto bukti opsional.
     * 6. Status operasional Lahan tetap 'aktif'.
     *
     * @throws AuthorizationException|DomainException|ModelNotFoundException
     */
    public function ajukanPengakhiranSelesai(Pengguna $user, string $idSiklus, array $data, ?UploadedFile $fotoBukti = null): SiklusTanam
    {
        if ($user->status !== 'Aktif' || $user->role !== 'Anggota') {
            throw new AuthorizationException('Akses ditolak. Hanya Anggota aktif yang dapat mengajukan pengakhiran siklus tanam.');
        }

        return DB::transaction(function () use ($user, $idSiklus, $data, $fotoBukti) {
            $siklus = SiklusTanam::where('id_siklus', $idSiklus)
                ->lockForUpdate()
                ->first();

            if (!$siklus) {
                throw new ModelNotFoundException('Data siklus tanam tidak ditemukan.');
            }

            // Otorisasi kepemilikan siklus secara sah
            if ($siklus->id_pengguna !== $user->id_pengguna || $siklus->id_kelompok !== $user->id_kelompok) {
                throw new AuthorizationException('Anda tidak memiliki hak akses untuk mengakhiri siklus tanam ini.');
            }

            // Validasi status siklus
            if ($siklus->status === SiklusTanam::STATUS_MENUNGGU_SELESAI || $siklus->status === SiklusTanam::STATUS_MENUNGGU_GAGAL) {
                throw new DomainException('Siklus tanam sudah dalam proses pengakhiran dan sedang menunggu keputusan Ketua.');
            }

            if (in_array($siklus->status, SiklusTanam::STATUSES_TERMINAL, true)) {
                throw new DomainException("Siklus tanam sudah berakhir dengan status '{$siklus->status}'.");
            }

            if ($siklus->status !== SiklusTanam::STATUS_AKTIF) {
                throw new DomainException('Hanya siklus tanam yang berstatus aktif yang dapat diajukan pengakhiran.');
            }

            // Validasi Panen: Minimal 1 panen terhubung
            $totalPanen = Panen::where('id_siklus', $siklus->id_siklus)->count();
            if ($totalPanen < 1) {
                throw new DomainException('Pengakhiran berhasil memerlukan minimal 1 catatan panen.');
            }

            // Validasi Panen: Seluruh panen pada siklus ini HARUS berstatus 'disetujui'
            $unapprovedPanenCount = Panen::where('id_siklus', $siklus->id_siklus)
                ->where('status', '!=', 'disetujui')
                ->count();

            if ($unapprovedPanenCount > 0) {
                throw new DomainException('Pengakhiran berhasil tidak dapat diajukan karena masih terdapat catatan panen yang belum disetujui atau perlu perbaikan. Pastikan seluruh panen telah diverifikasi.');
            }

            // Alasan wajib
            $alasan = trim((string) ($data['alasan'] ?? ''));
            if ($alasan === '') {
                throw new DomainException('Alasan pengakhiran siklus tanam wajib diisi.');
            }

            // Simpan foto bukti jika dilampirkan
            $fotoBuktiPath = null;
            if ($fotoBukti && $fotoBukti->isValid()) {
                $filename = 'bukti_selesai_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $fotoBukti->getClientOriginalExtension();
                $fotoBuktiPath = $fotoBukti->storeAs('siklus/bukti_selesai', $filename, 'documents');
            }

            $statusLama = $siklus->status;
            $statusBaru = SiklusTanam::STATUS_MENUNGGU_SELESAI;

            // Update Siklus Tanam
            $siklus->status = $statusBaru;
            $siklus->jenis_pengakhiran = 'selesai';
            $siklus->tanggal_pengajuan = now();
            $siklus->alasan_pengakhiran = $alasan;
            $siklus->foto_bukti_gagal = $fotoBuktiPath;
            $siklus->save();

            // Catat Riwayat Pengakhiran Siklus (Audit Trail)
            RiwayatPengakhiranSiklus::create([
                'id_siklus' => $siklus->id_siklus,
                'id_pengguna' => $user->id_pengguna,
                'jenis_pengakhiran' => 'selesai',
                'aksi' => 'pengajuan_selesai',
                'status_sebelumnya' => $statusLama,
                'status_sesudahnya' => $statusBaru,
                'alasan' => $alasan,
                'foto_bukti' => $fotoBuktiPath,
                'created_at' => now(),
                'snapshot_data' => [
                    'total_panen_kg' => $siklus->totalPanenKg(),
                    'total_panen_ton' => $siklus->totalPanenTon(),
                    'target_panen_ton' => $siklus->target_panen_ton,
                    'persentase_target' => $siklus->persentaseTargetPanen(),
                    'jumlah_panen' => $totalPanen,
                ],
            ]);

            // Dispatch notifikasi pengajuan selesai setelah transaksi bisnis berhasil commit
            $this->notificationService->afterCommit(function () use ($siklus) {
                $this->notificationService->notifySiklusPengajuanSelesai($siklus);
            });

            // Status operasional Lahan tetap 'aktif' selama proses pengakhiran
            return $siklus->fresh(['komoditas', 'kelompokTani', 'pengguna', 'lahan', 'riwayatPengakhiran']);
        });
    }

    /**
     * WORKFLOW B — Pengajuan Pengakhiran Gagal (status -> 'menunggu_gagal').
     *
     * Syarat:
     * 1. Siklus berstatus 'aktif'.
     * 2. Panen boleh 0 atau ada sebagian.
     * 3. Alasan kegagalan wajib diisi.
     * 4. Foto bukti kegagalan WAJIB dilampirkan.
     * 5. Status operasional Lahan tetap 'aktif'.
     *
     * @throws AuthorizationException|DomainException|ModelNotFoundException
     */
    public function ajukanPengakhiranGagal(Pengguna $user, string $idSiklus, array $data, ?UploadedFile $fotoBukti): SiklusTanam
    {
        if ($user->status !== 'Aktif' || $user->role !== 'Anggota') {
            throw new AuthorizationException('Akses ditolak. Hanya Anggota aktif yang dapat mengajukan pengakhiran gagal.');
        }

        return DB::transaction(function () use ($user, $idSiklus, $data, $fotoBukti) {
            $siklus = SiklusTanam::where('id_siklus', $idSiklus)
                ->lockForUpdate()
                ->first();

            if (!$siklus) {
                throw new ModelNotFoundException('Data siklus tanam tidak ditemukan.');
            }

            if ($siklus->id_pengguna !== $user->id_pengguna || $siklus->id_kelompok !== $user->id_kelompok) {
                throw new AuthorizationException('Anda tidak memiliki hak akses untuk mengakhiri siklus tanam ini.');
            }

            if ($siklus->status === SiklusTanam::STATUS_MENUNGGU_SELESAI || $siklus->status === SiklusTanam::STATUS_MENUNGGU_GAGAL) {
                throw new DomainException('Siklus tanam sudah dalam proses pengakhiran dan sedang menunggu keputusan Ketua.');
            }

            if (in_array($siklus->status, SiklusTanam::STATUSES_TERMINAL, true)) {
                throw new DomainException("Siklus tanam sudah berakhir dengan status '{$siklus->status}'.");
            }

            if ($siklus->status !== SiklusTanam::STATUS_AKTIF) {
                throw new DomainException('Hanya siklus tanam yang berstatus aktif yang dapat diajukan pengakhiran gagal.');
            }

            $alasan = trim((string) ($data['alasan'] ?? ''));
            if ($alasan === '') {
                throw new DomainException('Alasan kegagalan panen wajib diisi.');
            }

            if (!$fotoBukti || !$fotoBukti->isValid()) {
                throw new DomainException('Foto bukti kegagalan panen wajib dilampirkan.');
            }

            $filename = 'bukti_gagal_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $fotoBukti->getClientOriginalExtension();
            $fotoBuktiPath = $fotoBukti->storeAs('siklus/bukti_gagal', $filename, 'documents');

            $statusLama = $siklus->status;
            $statusBaru = SiklusTanam::STATUS_MENUNGGU_GAGAL;

            $siklus->status = $statusBaru;
            $siklus->jenis_pengakhiran = 'gagal';
            $siklus->tanggal_pengajuan = now();
            $siklus->alasan_pengakhiran = $alasan;
            $siklus->foto_bukti_gagal = $fotoBuktiPath;
            $siklus->save();

            RiwayatPengakhiranSiklus::create([
                'id_siklus' => $siklus->id_siklus,
                'id_pengguna' => $user->id_pengguna,
                'jenis_pengakhiran' => 'gagal',
                'aksi' => 'pengajuan_gagal',
                'status_sebelumnya' => $statusLama,
                'status_sesudahnya' => $statusBaru,
                'alasan' => $alasan,
                'foto_bukti' => $fotoBuktiPath,
                'created_at' => now(),
                'snapshot_data' => [
                    'total_panen_kg' => $siklus->totalPanenKg(),
                    'total_panen_ton' => $siklus->totalPanenTon(),
                    'target_panen_ton' => $siklus->target_panen_ton,
                ],
            ]);

            // Dispatch notifikasi pengajuan gagal setelah transaksi bisnis berhasil commit
            $this->notificationService->afterCommit(function () use ($siklus, $alasan) {
                $this->notificationService->notifySiklusPengajuanGagal($siklus, $alasan);
            });

            return $siklus->fresh(['komoditas', 'kelompokTani', 'pengguna', 'lahan', 'riwayatPengakhiran']);
        });
    }

    /**
     * WORKFLOW C — Pembatalan Siklus (status -> 'dibatalkan').
     *
     * Syarat:
     * 1. Siklus berstatus 'aktif'.
     * 2. BELUM memiliki Aktivitas Tanam sama sekali.
     * 3. BELUM memiliki catatan Panen sama sekali.
     * 4. Alasan pembatalan wajib diisi.
     * 5. Langsung terminal 'dibatalkan' tanpa verifikasi Ketua.
     * 6. Status operasional Lahan transisi menjadi 'istirahat'.
     *
     * @throws AuthorizationException|DomainException|ModelNotFoundException
     */
    public function batalkanSiklus(Pengguna $user, string $idSiklus, array $data): SiklusTanam
    {
        if ($user->status !== 'Aktif' || $user->role !== 'Anggota') {
            throw new AuthorizationException('Akses ditolak. Hanya Anggota aktif yang dapat membatalkan siklus tanam.');
        }

        return DB::transaction(function () use ($user, $idSiklus, $data) {
            $siklus = SiklusTanam::where('id_siklus', $idSiklus)
                ->lockForUpdate()
                ->first();

            if (!$siklus) {
                throw new ModelNotFoundException('Data siklus tanam tidak ditemukan.');
            }

            if ($siklus->id_pengguna !== $user->id_pengguna || $siklus->id_kelompok !== $user->id_kelompok) {
                throw new AuthorizationException('Anda tidak memiliki hak akses untuk membatalkan siklus tanam ini.');
            }

            if ($siklus->status !== SiklusTanam::STATUS_AKTIF) {
                throw new DomainException('Hanya siklus tanam aktif yang dapat dibatalkan.');
            }

            // Validasi: Belum ada aktivitas
            $hasActivity = AktivitasTanam::where('id_siklus', $siklus->id_siklus)->exists();
            if ($hasActivity) {
                throw new DomainException('Siklus tanam tidak dapat dibatalkan karena sudah memiliki catatan aktivitas tanam. Gunakan opsi pengakhiran jika masa tanam tidak berlanjut.');
            }

            // Validasi: Belum ada panen
            $hasPanen = Panen::where('id_siklus', $siklus->id_siklus)->exists();
            if ($hasPanen) {
                throw new DomainException('Siklus tanam tidak dapat dibatalkan karena sudah memiliki catatan hasil panen.');
            }

            $alasan = trim((string) ($data['alasan'] ?? ''));
            if ($alasan === '') {
                throw new DomainException('Alasan pembatalan siklus tanam wajib diisi.');
            }

            $lahan = Lahan::where('id_lahan', $siklus->id_lahan)
                ->lockForUpdate()
                ->firstOrFail();

            $statusLama = $siklus->status;
            $statusBaru = SiklusTanam::STATUS_DIBATALKAN;

            // Update Siklus Tanam
            $siklus->status = $statusBaru;
            $siklus->jenis_pengakhiran = 'dibatalkan';
            $siklus->tanggal_selesai = now()->toDateString();
            $siklus->alasan_pengakhiran = $alasan;
            $siklus->save();

            // Transisi status operasional Lahan menjadi 'istirahat'
            $lahan->status_operasional = 'istirahat';
            $lahan->save();

            // Catat Riwayat Pengakhiran Siklus (Audit Trail)
            RiwayatPengakhiranSiklus::create([
                'id_siklus' => $siklus->id_siklus,
                'id_pengguna' => $user->id_pengguna,
                'jenis_pengakhiran' => 'dibatalkan',
                'aksi' => 'pembatalan',
                'status_sebelumnya' => $statusLama,
                'status_sesudahnya' => $statusBaru,
                'alasan' => $alasan,
                'tanggal_keputusan' => now(),
                'created_at' => now(),
            ]);

            // Dispatch notifikasi pembatalan siklus setelah transaksi bisnis berhasil commit
            $this->notificationService->afterCommit(function () use ($siklus, $alasan) {
                $this->notificationService->notifySiklusDibatalkan($siklus, $alasan);
            });

            return $siklus->fresh(['komoditas', 'kelompokTani', 'pengguna', 'lahan', 'riwayatPengakhiran']);
        });
    }

    /**
     * Keputusan Verifikasi Pengakhiran Siklus Tanam oleh Ketua/Pengurus Kelompok.
     *
     * @param Pengguna $user
     * @param string $idSiklus
     * @param array $data ['keputusan' => 'disetujui'|'ditolak', 'catatan' => '...']
     * @return SiklusTanam
     * @throws AuthorizationException|DomainException|ModelNotFoundException
     */
    public function verifikasiPengakhiran(Pengguna $user, string $idSiklus, array $data): SiklusTanam
    {
        if ($user->status !== 'Aktif' || $user->role !== 'Pengurus') {
            throw new AuthorizationException('Akses ditolak. Anda harus menjadi Pengurus aktif yang terdaftar dalam kelompok tani.');
        }

        return DB::transaction(function () use ($user, $idSiklus, $data) {
            $siklus = SiklusTanam::where('id_siklus', $idSiklus)
                ->lockForUpdate()
                ->first();

            if (!$siklus) {
                throw new ModelNotFoundException('Data siklus tanam tidak ditemukan.');
            }

            // Otorisasi: Hanya Ketua/Pengurus satu kelompok yang berwenang
            if (empty($user->id_kelompok) || $siklus->id_kelompok !== $user->id_kelompok) {
                throw new AuthorizationException('Akses ditolak. Anda hanya berwenang memverifikasi siklus tanam di kelompok tani Anda.');
            }

            // Validasi status saat ini harus dalam proses pengakhiran
            if (!in_array($siklus->status, [SiklusTanam::STATUS_MENUNGGU_SELESAI, SiklusTanam::STATUS_MENUNGGU_GAGAL], true)) {
                throw new DomainException('Siklus tanam ini tidak sedang dalam status menunggu persetujuan pengakhiran.');
            }

            $keputusan = strtolower(trim((string) ($data['keputusan'] ?? $data['status_verifikasi'] ?? '')));
            if (!in_array($keputusan, ['disetujui', 'ditolak'], true)) {
                throw new DomainException("Keputusan verifikasi tidak valid. Pilihan: 'disetujui' atau 'ditolak'.");
            }

            $catatan = trim((string) ($data['catatan'] ?? ''));
            if ($keputusan === 'ditolak' && $catatan === '') {
                throw new DomainException('Catatan alasan penolakan pengakhiran wajib diisi.');
            }

            $lahan = Lahan::where('id_lahan', $siklus->id_lahan)
                ->lockForUpdate()
                ->firstOrFail();

            $statusLama = $siklus->status;

            if ($keputusan === 'disetujui') {
                $statusBaru = ($statusLama === SiklusTanam::STATUS_MENUNGGU_SELESAI)
                    ? SiklusTanam::STATUS_SELESAI
                    : SiklusTanam::STATUS_GAGAL;

                $siklus->status = $statusBaru;
                $siklus->tanggal_selesai = now()->toDateString();
                $siklus->tanggal_verifikasi = now();
                $siklus->diverifikasi_oleh = $user->nama;
                $siklus->catatan_verifikasi = $catatan ?: null;
                $siklus->save();

                // Lahan bertransisi menjadi 'istirahat'
                $lahan->status_operasional = 'istirahat';
                $lahan->save();

                RiwayatPengakhiranSiklus::create([
                    'id_siklus' => $siklus->id_siklus,
                    'id_pengguna' => $user->id_pengguna,
                    'jenis_pengakhiran' => $siklus->jenis_pengakhiran ?? ($statusBaru === SiklusTanam::STATUS_SELESAI ? 'selesai' : 'gagal'),
                    'aksi' => 'disetujui',
                    'status_sebelumnya' => $statusLama,
                    'status_sesudahnya' => $statusBaru,
                    'verifikator_id' => $user->id_pengguna,
                    'verifikator_nama' => $user->nama,
                    'catatan_verifikasi' => $catatan ?: null,
                    'tanggal_keputusan' => now(),
                    'created_at' => now(),
                    'snapshot_data' => [
                        'total_panen_kg' => $siklus->totalPanenKg(),
                        'total_panen_ton' => $siklus->totalPanenTon(),
                        'target_panen_ton' => $siklus->target_panen_ton,
                        'persentase_target' => $siklus->persentaseTargetPanen(),
                    ],
                ]);

                // Dispatch notifikasi keputusan disetujui (selesai/gagal) setelah commit
                $this->notificationService->afterCommit(function () use ($siklus, $user, $catatan) {
                    $this->notificationService->notifySiklusDisetujui($siklus, $user, $catatan);
                });
            } else {
                // Ditolak: Siklus kembali menjadi 'aktif', Lahan tetap 'aktif'
                $statusBaru = SiklusTanam::STATUS_AKTIF;

                $siklus->status = $statusBaru;
                $siklus->tanggal_verifikasi = now();
                $siklus->diverifikasi_oleh = $user->nama;
                $siklus->catatan_verifikasi = $catatan;
                $siklus->save();

                // Lahan tetap 'aktif'
                $lahan->status_operasional = 'aktif';
                $lahan->save();

                RiwayatPengakhiranSiklus::create([
                    'id_siklus' => $siklus->id_siklus,
                    'id_pengguna' => $user->id_pengguna,
                    'jenis_pengakhiran' => $siklus->jenis_pengakhiran ?? 'selesai',
                    'aksi' => 'ditolak',
                    'status_sebelumnya' => $statusLama,
                    'status_sesudahnya' => $statusBaru,
                    'verifikator_id' => $user->id_pengguna,
                    'verifikator_nama' => $user->nama,
                    'catatan_verifikasi' => $catatan,
                    'tanggal_keputusan' => now(),
                    'created_at' => now(),
                ]);

                // Dispatch notifikasi penolakan pengakhiran setelah commit
                $this->notificationService->afterCommit(function () use ($siklus, $user, $catatan) {
                    $this->notificationService->notifySiklusDitolak($siklus, $user, $catatan);
                });
            }

            return $siklus->fresh(['komoditas', 'kelompokTani', 'pengguna', 'lahan', 'riwayatPengakhiran']);
        });
    }
}
