<?php

namespace App\Services;

use App\Jobs\DeleteCloudinaryPhotoJob;
use App\Models\Lahan;
use App\Models\Pengguna;
use App\Models\RiwayatVerifikasiLahan;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class LahanService
{
    /**
     * Daftar field yang diizinkan untuk diisi dari input pengguna.
     * Field internal/workflow diproteksi ketat dan tidak boleh di-override dari input client.
     */
    protected const ALLOWED_ATTRIBUTES = [
        'nama_lahan',
        'luas',
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'desa_id',
        'alamat_lahan',
        'latitude',
        'longitude',
        'id_komoditas',
        'musim_tanam',
        'keterangan',
    ];

    public function __construct(
        protected NotificationService $notificationService,
        protected CloudinaryService $cloudinaryService
    ) {}

    /**
     * Dapatkan query builder Lahan yang di-scope berdasarkan konteks otorisasi aktor.
     * Mencegah IDOR (Insecure Direct Object Reference) pada level query database.
     *
     * - Anggota Aktif: Hanya lahan miliknya sendiri.
     * - Pengurus Aktif: Hanya lahan anggota dalam kelompok tani miliknya.
     * - Aktor Lain (PPL, Admin, Akun Non-Aktif): Ditolak (query kosong).
     *
     * @param Pengguna $user
     * @return Builder
     */
    public function getLahanQueryForUser(Pengguna $user): Builder
    {
        if ($user->status !== 'Aktif') {
            return Lahan::query()->whereRaw('1 = 0');
        }

        if ($user->role === 'Anggota') {
            return Lahan::query()->where('id_pengguna', $user->id_pengguna);
        }

        if ($user->role === 'Pengurus') {
            if (empty($user->id_kelompok)) {
                return Lahan::query()->whereRaw('1 = 0');
            }
            return Lahan::query()->where('id_kelompok', $user->id_kelompok);
        }

        return Lahan::query()->whereRaw('1 = 0');
    }

    /**
     * Temukan lahan berdasarkan ID dengan proteksi IDOR sesuai scope akses aktor.
     *
     * @param Pengguna $user
     * @param string $idLahan
     * @return Lahan
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findLahanForUser(Pengguna $user, string $idLahan): Lahan
    {
        return $this->getLahanQueryForUser($user)->findOrFail($idLahan);
    }

    /**
     * Pendaftaran lahan baru oleh Anggota aktif.
     *
     * @param Pengguna $user Aktor yang membuat lahan
     * @param array $data Data input lahan
     * @param UploadedFile|null $foto File gambar lahan (opsional)
     * @return Lahan
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function createLahan(Pengguna $user, array $data, ?UploadedFile $foto = null): Lahan
    {
        // 1. Otorisasi aktor menggunakan Policy
        if (Gate::forUser($user)->denies('create', Lahan::class)) {
            throw new AuthorizationException('Pengguna tidak memiliki izin untuk mendaftarkan lahan.');
        }

        // 2. Upload foto baru ke Cloudinary sebelum membuka transaksi database
        $newPhotoUrl = null;
        if ($foto) {
            $newPhotoUrl = $this->cloudinaryService->upload($foto->getRealPath(), 'pawontani/lahan');
        }

        // 3. Eksekusi transaksi database dengan kompensasi foto
        try {
            return DB::transaction(function () use ($user, $data, $newPhotoUrl) {
                $filteredData = array_intersect_key($data, array_flip(self::ALLOWED_ATTRIBUTES));

                $lahan = new Lahan($filteredData);
                $lahan->id_pengguna = $user->id_pengguna;
                $lahan->id_kelompok = $user->id_kelompok;
                $lahan->status_verifikasi = 'menunggu_verifikasi';
                $lahan->status_operasional = 'persiapan';
                $lahan->nomor_revisi = 1;
                $lahan->catatan_verifikasi = null;
                $lahan->tanggal_verifikasi = null;
                $lahan->verifikator_id = null;

                if ($newPhotoUrl) {
                    $lahan->foto_lahan = $newPhotoUrl;
                }

                $lahan->saveWithUniqueRetry();

                // Catat riwayat audit trail pengajuan pertama
                $riwayat = new RiwayatVerifikasiLahan([
                    'id_lahan' => $lahan->id_lahan,
                    'id_pengguna' => $user->id_pengguna,
                    'aksi' => 'pengajuan_baru',
                    'status_sebelumnya' => null,
                    'status_sesudahnya' => 'menunggu_verifikasi',
                    'catatan' => $data['keterangan'] ?? 'Pendaftaran lahan baru oleh anggota',
                    'nomor_revisi' => 1,
                    'snapshot_data' => $lahan->toArray(),
                ]);
                $riwayat->save();

                // Kirim notifikasi ke Ketua aktif (jika ada dan tunggal)
                $this->notificationService->notifyLahanDiajukan($lahan, $riwayat, false);

                return $lahan;
            });
        } catch (Throwable $e) {
            // Kompensasi: Hapus foto baru di Cloudinary jika transaksi database gagal
            if ($newPhotoUrl) {
                try {
                    $this->cloudinaryService->delete($newPhotoUrl);
                } catch (Throwable $compensationError) {
                    Log::critical('Kompensasi penghapusan foto baru gagal saat createLahan rollback.', [
                        'photo_url' => $newPhotoUrl,
                        'error' => $compensationError->getMessage(),
                    ]);
                }
            }
            throw $e;
        }
    }

    /**
     * Pembaruan data lahan oleh Anggota pemilik lahan.
     *
     * @param Pengguna $user Aktor yang melakukan pembaruan
     * @param Lahan|string $lahan Instance atau ID Lahan
     * @param array $data Data perubahan lahan
     * @param UploadedFile|null $foto File gambar baru (opsional)
     * @return Lahan
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function updateLahan(Pengguna $user, Lahan|string $lahan, array $data, ?UploadedFile $foto = null): Lahan
    {
        $idLahan = $lahan instanceof Lahan ? $lahan->id_lahan : $lahan;
        $lahanInstance = $lahan instanceof Lahan ? $lahan : Lahan::findOrFail($idLahan);

        // 1. Verifikasi otorisasi pengguna SEBELUM melakukan upload ke Cloudinary
        if (Gate::forUser($user)->denies('update', $lahanInstance)) {
            throw new AuthorizationException('Pengguna tidak memiliki izin untuk mengubah data lahan ini.');
        }

        // 2. Upload foto baru ke Cloudinary HANYA setelah otorisasi awal lolos
        $newPhotoUrl = null;
        if ($foto) {
            $newPhotoUrl = $this->cloudinaryService->upload($foto->getRealPath(), 'pawontani/lahan');
        }

        $oldPhotoUrlToDelete = null;

        try {
            $updatedLahan = DB::transaction(function () use ($user, $idLahan, $data, $newPhotoUrl, &$oldPhotoUrlToDelete) {
                // 3. Kunci baris dengan lockForUpdate untuk mencegah race condition
                $lahanLocked = Lahan::where('id_lahan', $idLahan)->lockForUpdate()->firstOrFail();

                // 4. Verifikasi kembali otorisasi aktor terhadap data terkunci
                if (Gate::forUser($user)->denies('update', $lahanLocked)) {
                    throw new AuthorizationException('Pengguna tidak memiliki izin untuk mengubah data lahan ini.');
                }

                $statusSebelumnya = $lahanLocked->status_verifikasi;
                $nomorRevisiSebelumnya = $lahanLocked->nomor_revisi ?? 1;

                // 4. Evaluasi aturan transisi workflow status dan nomor revisi
                $isResubmission = false;
                $aksiRiwayat = 'pembaruan_draft';
                $nomorRevisiBaru = $nomorRevisiSebelumnya;

                if ($statusSebelumnya === 'terverifikasi') {
                    // Perubahan lahan terverifikasi mencabut status dan menjadikannya pengajuan ulang
                    $lahanLocked->status_verifikasi = 'menunggu_verifikasi';
                    $lahanLocked->verifikator_id = null;
                    $lahanLocked->tanggal_verifikasi = null;
                    $lahanLocked->catatan_verifikasi = null;
                    $nomorRevisiBaru = $nomorRevisiSebelumnya + 1;
                    $isResubmission = true;
                    $aksiRiwayat = 'pengajuan_ulang';
                } elseif ($statusSebelumnya === 'perlu_perbaikan') {
                    // Perbaikan lahan ditransisikan kembali menjadi menunggu_verifikasi
                    $lahanLocked->status_verifikasi = 'menunggu_verifikasi';
                    $lahanLocked->verifikator_id = null;
                    $lahanLocked->tanggal_verifikasi = null;
                    $lahanLocked->catatan_verifikasi = null;
                    $nomorRevisiBaru = $nomorRevisiSebelumnya + 1;
                    $isResubmission = true;
                    $aksiRiwayat = 'pengajuan_ulang';
                } else {
                    // Status menunggu_verifikasi: perubahan draft biasa tanpa menaikkan revisi
                    $lahanLocked->status_verifikasi = 'menunggu_verifikasi';
                    $aksiRiwayat = 'pembaruan_draft';
                }

                $lahanLocked->nomor_revisi = $nomorRevisiBaru;

                // 5. Terapkan data input yang difilter
                $filteredData = array_intersect_key($data, array_flip(self::ALLOWED_ATTRIBUTES));
                $lahanLocked->fill($filteredData);

                // 6. Tangani pergantian foto
                if ($newPhotoUrl) {
                    if ($lahanLocked->foto_lahan && $lahanLocked->foto_lahan !== $newPhotoUrl) {
                        $oldPhotoUrlToDelete = $lahanLocked->foto_lahan;
                    }
                    $lahanLocked->foto_lahan = $newPhotoUrl;
                }

                $lahanLocked->save();

                // 7. Catat riwayat perubahan
                $riwayat = new RiwayatVerifikasiLahan([
                    'id_lahan' => $lahanLocked->id_lahan,
                    'id_pengguna' => $user->id_pengguna,
                    'aksi' => $aksiRiwayat,
                    'status_sebelumnya' => $statusSebelumnya,
                    'status_sesudahnya' => 'menunggu_verifikasi',
                    'catatan' => $data['keterangan'] ?? ($isResubmission ? 'Pembaruan data dan pengajuan ulang verifikasi' : 'Pembaruan data lahan'),
                    'nomor_revisi' => $nomorRevisiBaru,
                    'snapshot_data' => $lahanLocked->toArray(),
                ]);
                $riwayat->save();

                // 8. Kirim notifikasi jika berupa pengajuan ulang
                if ($isResubmission) {
                    $this->notificationService->notifyLahanDiajukan($lahanLocked, $riwayat, true);
                }

                return $lahanLocked;
            });

            // 9. Setelah transaksi database COMMIT: Dispatch penghapusan foto lama ke queue
            if ($oldPhotoUrlToDelete) {
                try {
                    DeleteCloudinaryPhotoJob::dispatch($oldPhotoUrlToDelete);
                } catch (Throwable $queueError) {
                    // Kegagalan antrean setelah commit TIDAK BOLEH membatalkan operasi database yang sudah sah
                    Log::error('Gagal menjadwalkan DeleteCloudinaryPhotoJob setelah transaksi commit.', [
                        'lahan_id' => $updatedLahan->id_lahan,
                        'error' => $queueError->getMessage(),
                    ]);
                }
            }

            return $updatedLahan;
        } catch (Throwable $e) {
            // Kompensasi: Hapus foto baru di Cloudinary jika transaksi database gagal
            if ($newPhotoUrl) {
                try {
                    $this->cloudinaryService->delete($newPhotoUrl);
                } catch (Throwable $compensationError) {
                    Log::critical('Kompensasi penghapusan foto baru gagal saat updateLahan rollback.', [
                        'photo_url' => $newPhotoUrl,
                        'error' => $compensationError->getMessage(),
                    ]);
                }
            }
            throw $e;
        }
    }

    /**
     * Penghapusan lahan (Soft Delete) oleh Anggota pemilik lahan.
     * Lahan terverifikasi dilarang keras untuk dihapus.
     *
     * @param Pengguna $user Aktor yang meminta penghapusan
     * @param Lahan|string $lahan Instance atau ID Lahan
     * @return bool
     *
     * @throws AuthorizationException
     * @throws DomainException
     * @throws Throwable
     */
    public function deleteLahan(Pengguna $user, Lahan|string $lahan): bool
    {
        $idLahan = $lahan instanceof Lahan ? $lahan->id_lahan : $lahan;

        return DB::transaction(function () use ($user, $idLahan) {
            $lahanLocked = Lahan::where('id_lahan', $idLahan)->lockForUpdate()->firstOrFail();

            // 1. Periksa otorisasi aktor terlebih dahulu (apakah pemilik lahan yang sah dan aktif)
            // Pengguna yang tidak berhak harus langsung ditolak dengan AuthorizationException
            // sehingga tidak dapat mengetahui status verifikasi lahan melalui respons (mencegah information disclosure)
            if ($user->status !== 'Aktif' || $user->role !== 'Anggota' || $user->id_pengguna !== $lahanLocked->id_pengguna) {
                throw new AuthorizationException('Pengguna tidak memiliki izin untuk menghapus lahan ini.');
            }

            // 2. Setelah pengguna dinyatakan berhak (pemilik sah), periksa status verifikasi
            if ($lahanLocked->status_verifikasi === 'terverifikasi') {
                throw new DomainException('Lahan yang sudah terverifikasi tidak dapat dihapus.');
            }

            // 3. Pengecekan Policy penuh
            if (Gate::forUser($user)->denies('delete', $lahanLocked)) {
                throw new AuthorizationException('Pengguna tidak memiliki izin untuk menghapus lahan ini.');
            }

            // Catat riwayat penghapusan sebelum soft delete dieksekusi
            $riwayat = new RiwayatVerifikasiLahan([
                'id_lahan' => $lahanLocked->id_lahan,
                'id_pengguna' => $user->id_pengguna,
                'aksi' => 'penghapusan_lahan',
                'status_sebelumnya' => $lahanLocked->status_verifikasi,
                'status_sesudahnya' => 'dihapus',
                'catatan' => 'Lahan dihapus oleh pemilik (Soft Delete)',
                'nomor_revisi' => $lahanLocked->nomor_revisi ?? 1,
                'snapshot_data' => $lahanLocked->toArray(),
            ]);
            $riwayat->save();

            // Soft delete data lahan (foto Cloudinary tetap dipertahankan karena data dapat di-restore)
            return (bool) $lahanLocked->delete();
        });
    }

    /**
     * Verifikasi lahan oleh Ketua Kelompok Tani.
     *
     * Aksi yang didukung:
     * 1. 'setujui'           -> status menjadi 'terverifikasi'
     * 2. 'perlu_perbaikan'   -> status menjadi 'perlu_perbaikan'
     * 3. 'cabut_verifikasi'  -> status menjadi 'perlu_perbaikan' (khusus lahan yang sebelumnya terverifikasi)
     *
     * @param Pengguna $user Aktor verifikator (Ketua)
     * @param Lahan|string $lahan Instance atau ID Lahan
     * @param string $aksi Aksi verifikasi
     * @param string|null $catatan Alasan/catatan verifikasi
     * @return Lahan
     *
     * @throws AuthorizationException
     * @throws DomainException
     * @throws InvalidArgumentException
     * @throws Throwable
     */
    public function verifyLahan(Pengguna $user, Lahan|string $lahan, string $aksi, ?string $catatan = null): Lahan
    {
        if (!in_array($aksi, ['setujui', 'perlu_perbaikan', 'cabut_verifikasi'], true)) {
            throw new InvalidArgumentException("Aksi verifikasi [{$aksi}] tidak valid.");
        }

        if (in_array($aksi, ['perlu_perbaikan', 'cabut_verifikasi'], true) && empty(trim($catatan ?? ''))) {
            throw new InvalidArgumentException("Catatan wajib diisi ketika meminta perbaikan atau mencabut verifikasi.");
        }

        $idLahan = $lahan instanceof Lahan ? $lahan->id_lahan : $lahan;

        return DB::transaction(function () use ($user, $idLahan, $aksi, $catatan) {
            $lahanLocked = Lahan::where('id_lahan', $idLahan)->lockForUpdate()->firstOrFail();

            // Otorisasi berdasarkan jenis aksi verifikasi
            if ($aksi === 'setujui') {
                if (Gate::forUser($user)->denies('verify', $lahanLocked)) {
                    throw new AuthorizationException('Hanya Ketua aktif dari kelompok tani terkait yang dapat menyetujui lahan.');
                }
            } else {
                if (Gate::forUser($user)->denies('requestRevision', $lahanLocked)) {
                    throw new AuthorizationException('Hanya Ketua aktif dari kelompok tani terkait yang dapat meminta perbaikan atau mencabut verifikasi.');
                }
            }

            $statusSebelumnya = $lahanLocked->status_verifikasi;

            if ($aksi === 'setujui') {
                if ($statusSebelumnya !== 'menunggu_verifikasi') {
                    throw new DomainException("Hanya lahan berstatus 'menunggu_verifikasi' yang dapat disetujui. Status saat ini: [{$statusSebelumnya}].");
                }

                $lahanLocked->status_verifikasi = 'terverifikasi';
                $lahanLocked->tanggal_verifikasi = now();
                $lahanLocked->verifikator_id = $user->id_pengguna;
                $lahanLocked->catatan_verifikasi = $catatan;
                $lahanLocked->save();

                $riwayat = new RiwayatVerifikasiLahan([
                    'id_lahan' => $lahanLocked->id_lahan,
                    'id_pengguna' => $user->id_pengguna,
                    'aksi' => 'verifikasi_disetujui',
                    'status_sebelumnya' => $statusSebelumnya,
                    'status_sesudahnya' => 'terverifikasi',
                    'catatan' => $catatan ?? 'Lahan disetujui dan terverifikasi oleh Ketua Kelompok',
                    'nomor_revisi' => $lahanLocked->nomor_revisi ?? 1,
                    'snapshot_data' => $lahanLocked->toArray(),
                ]);
                $riwayat->save();

                $this->notificationService->notifyLahanTerverifikasi($lahanLocked, $user, $riwayat, $catatan);
            } elseif ($aksi === 'perlu_perbaikan') {
                if ($statusSebelumnya !== 'menunggu_verifikasi') {
                    throw new DomainException("Permintaan perbaikan hanya dapat dilakukan pada lahan berstatus 'menunggu_verifikasi'. Status saat ini: [{$statusSebelumnya}].");
                }

                $lahanLocked->status_verifikasi = 'perlu_perbaikan';
                $lahanLocked->tanggal_verifikasi = now();
                $lahanLocked->verifikator_id = $user->id_pengguna;
                $lahanLocked->catatan_verifikasi = $catatan;
                $lahanLocked->save();

                $riwayat = new RiwayatVerifikasiLahan([
                    'id_lahan' => $lahanLocked->id_lahan,
                    'id_pengguna' => $user->id_pengguna,
                    'aksi' => 'permintaan_perbaikan',
                    'status_sebelumnya' => $statusSebelumnya,
                    'status_sesudahnya' => 'perlu_perbaikan',
                    'catatan' => $catatan,
                    'nomor_revisi' => $lahanLocked->nomor_revisi ?? 1,
                    'snapshot_data' => $lahanLocked->toArray(),
                ]);
                $riwayat->save();

                $this->notificationService->notifyPerluPerbaikan($lahanLocked, $user, $catatan, $riwayat);
            } elseif ($aksi === 'cabut_verifikasi') {
                if ($statusSebelumnya !== 'terverifikasi') {
                    throw new DomainException("Pencabutan verifikasi hanya dapat dilakukan pada lahan yang telah berstatus 'terverifikasi'. Status saat ini: [{$statusSebelumnya}].");
                }

                // Cabut status verifikasi dan kembalikan ke perlu_perbaikan
                $lahanLocked->status_verifikasi = 'perlu_perbaikan';
                $lahanLocked->verifikator_id = null;
                $lahanLocked->tanggal_verifikasi = null;
                $lahanLocked->catatan_verifikasi = $catatan;
                $lahanLocked->save();

                $riwayat = new RiwayatVerifikasiLahan([
                    'id_lahan' => $lahanLocked->id_lahan,
                    'id_pengguna' => $user->id_pengguna,
                    'aksi' => 'pencabutan_verifikasi',
                    'status_sebelumnya' => $statusSebelumnya,
                    'status_sesudahnya' => 'perlu_perbaikan',
                    'catatan' => $catatan,
                    'nomor_revisi' => $lahanLocked->nomor_revisi ?? 1,
                    'snapshot_data' => $lahanLocked->toArray(),
                ]);
                $riwayat->save();

                $this->notificationService->notifyPerluPerbaikan($lahanLocked, $user, $catatan, $riwayat);
            }

            return $lahanLocked;
        });
    }
}
