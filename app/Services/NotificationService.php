<?php

namespace App\Services;

use App\Models\Lahan;
use App\Models\Notifikasi;
use App\Models\Panen;
use App\Models\Pengguna;
use App\Models\RiwayatVerifikasiLahan;
use App\Models\SiklusTanam;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationService
{
    // ==================== JENIS NOTIFIKASI: LAHAN ====================
    public const JENIS_PENGAJUAN_BARU = 'pengajuan_lahan';
    public const JENIS_PENGAJUAN_ULANG = 'pengajuan_ulang_lahan';
    public const JENIS_PERLU_PERBAIKAN = 'perlu_perbaikan';
    public const JENIS_TERVERIFIKASI = 'lahan_terverifikasi';

    // ==================== JENIS NOTIFIKASI: SIKLUS TANAM ====================
    public const JENIS_SIKLUS_PENGAJUAN_SELESAI = 'siklus_pengajuan_selesai';
    public const JENIS_SIKLUS_PENGAJUAN_GAGAL = 'siklus_pengajuan_gagal';
    public const JENIS_SIKLUS_DISETUJUI = 'siklus_disetujui';
    public const JENIS_SIKLUS_DITOLAK = 'siklus_ditolak';
    public const JENIS_SIKLUS_DIBATALKAN = 'siklus_dibatalkan';

    // ==================== JENIS NOTIFIKASI: PANEN ====================
    public const JENIS_PANEN_PENGAJUAN_BARU = 'panen_pengajuan_baru';
    public const JENIS_PANEN_PENGAJUAN_ULANG = 'panen_pengajuan_ulang';
    public const JENIS_PANEN_DISETUJUI = 'panen_disetujui';
    public const JENIS_PANEN_DITOLAK = 'panen_ditolak';

    // ==================== RECIPIENT RESOLUTION ====================

    /**
     * Cari Ketua aktif untuk kelompok tani tertentu.
     * Aturan Ketua:
     * - Tepat satu Ketua aktif: kembalikan objek Pengguna.
     * - Tidak ditemukan Ketua aktif: catat Log::warning, kembalikan null.
     * - Lebih dari satu Ketua aktif: catat Log::warning, kembalikan null (ambigu).
     * - Tidak ada fallback ke Sekretaris/Bendahara/Pengurus lain untuk Lahan/Siklus.
     */
    public function findActiveKetua(string $idKelompok): ?Pengguna
    {
        $ketuaList = Pengguna::where('role', 'Pengurus')
            ->where('jabatan', 'Ketua')
            ->where('status', 'Aktif')
            ->where('id_kelompok', $idKelompok)
            ->get();

        if ($ketuaList->isEmpty()) {
            Log::warning("Pengiriman notifikasi dibatalkan: Tidak ditemukan Ketua aktif untuk kelompok tani [{$idKelompok}].");
            return null;
        }

        if ($ketuaList->count() > 1) {
            Log::warning("Pengiriman notifikasi dibatalkan: Ditemukan {$ketuaList->count()} Ketua aktif untuk kelompok tani [{$idKelompok}]. Penerima ambigu sehingga notifikasi tidak dikirim.");
            return null;
        }

        return $ketuaList->first();
    }

    /**
     * Cari seluruh Pengurus aktif yang berwenang memverifikasi panen pada kelompok tani terkait.
     * Mengikuti aturan authorization Panen existing: seluruh Pengurus aktif dalam kelompok berhak memverifikasi.
     *
     * @param string $idKelompok
     * @return Collection<int, Pengguna>
     */
    public function findActiveVerifikatorPanen(string $idKelompok): Collection
    {
        $pengurusList = Pengguna::where('role', 'Pengurus')
            ->where('status', 'Aktif')
            ->where('id_kelompok', $idKelompok)
            ->get();

        if ($pengurusList->isEmpty()) {
            Log::warning("Pengiriman notifikasi dibatalkan: Tidak ditemukan Pengurus aktif untuk kelompok tani [{$idKelompok}].");
        }

        return $pengurusList;
    }

    // ==================== DOMAIN: LAHAN (PRESERVED) ====================

    /**
     * Kirim notifikasi pengajuan lahan baru atau pengajuan ulang perbaikan kepada Ketua Kelompok Tani.
     */
    public function notifyLahanDiajukan(
        Lahan $lahan,
        ?RiwayatVerifikasiLahan $riwayat = null,
        bool $isResubmission = false
    ): ?Notifikasi {
        $ketua = $this->findActiveKetua($lahan->id_kelompok);

        if (!$ketua) {
            return null;
        }

        $namaPengirim = $lahan->pengguna?->nama ?? 'Anggota';
        $nomorRevisi = $lahan->nomor_revisi ?? 1;

        if ($isResubmission) {
            $jenis = self::JENIS_PENGAJUAN_ULANG;
            $judul = 'Pengajuan Ulang Verifikasi Lahan';
            $pesan = "Anggota {$namaPengirim} telah memperbarui data lahan '{$lahan->nama_lahan}' (Revisi #{$nomorRevisi}) dan mengajukannya kembali untuk diverifikasi.";
        } else {
            $jenis = self::JENIS_PENGAJUAN_BARU;
            $judul = 'Pengajuan Verifikasi Lahan Baru';
            $pesan = "Anggota {$namaPengirim} mengajukan pendaftaran lahan baru '{$lahan->nama_lahan}' untuk diverifikasi.";
        }

        $payload = [
            'entity_type' => 'lahan',
            'entity_id' => $lahan->id_lahan,
            'id_lahan' => $lahan->id_lahan,
            'nama_lahan' => $lahan->nama_lahan,
            'nomor_revisi' => $nomorRevisi,
            'id_pengguna' => $lahan->id_pengguna,
            'id_kelompok' => $lahan->id_kelompok,
            'is_resubmission' => $isResubmission,
            'idempotency_key' => "lahan:{$lahan->id_lahan}:diajukan:revisi_{$nomorRevisi}",
        ];

        return $this->createNotification([
            'id_pengirim' => $lahan->id_pengguna,
            'id_penerima' => $ketua->id_pengguna,
            'id_lahan' => $lahan->id_lahan,
            'id_riwayat' => $riwayat?->id_riwayat,
            'jenis_notifikasi' => $jenis,
            'judul' => $judul,
            'pesan' => $pesan,
            'data_payload' => $payload,
        ]);
    }

    /**
     * Kirim notifikasi permintaan perbaikan lahan dari Ketua kepada Anggota pemilik lahan.
     */
    public function notifyPerluPerbaikan(
        Lahan $lahan,
        Pengguna $ketua,
        string $catatan,
        ?RiwayatVerifikasiLahan $riwayat = null
    ): ?Notifikasi {
        $nomorRevisi = $lahan->nomor_revisi ?? 1;
        $jenis = self::JENIS_PERLU_PERBAIKAN;
        $judul = 'Permintaan Perbaikan Lahan';
        $pesan = "Pengajuan verifikasi lahan '{$lahan->nama_lahan}' memerlukan perbaikan. Catatan Ketua: {$catatan}";

        $payload = [
            'entity_type' => 'lahan',
            'entity_id' => $lahan->id_lahan,
            'id_lahan' => $lahan->id_lahan,
            'nama_lahan' => $lahan->nama_lahan,
            'nomor_revisi' => $nomorRevisi,
            'catatan' => $catatan,
            'verifikator_id' => $ketua->id_pengguna,
            'idempotency_key' => "lahan:{$lahan->id_lahan}:perbaikan:revisi_{$nomorRevisi}",
        ];

        return $this->createNotification([
            'id_pengirim' => $ketua->id_pengguna,
            'id_penerima' => $lahan->id_pengguna,
            'id_lahan' => $lahan->id_lahan,
            'id_riwayat' => $riwayat?->id_riwayat,
            'jenis_notifikasi' => $jenis,
            'judul' => $judul,
            'pesan' => $pesan,
            'data_payload' => $payload,
        ]);
    }

    /**
     * Kirim notifikasi persetujuan verifikasi lahan dari Ketua kepada Anggota pemilik lahan.
     */
    public function notifyLahanTerverifikasi(
        Lahan $lahan,
        Pengguna $ketua,
        ?RiwayatVerifikasiLahan $riwayat = null,
        ?string $catatan = null
    ): ?Notifikasi {
        $nomorRevisi = $lahan->nomor_revisi ?? 1;
        $jenis = self::JENIS_TERVERIFIKASI;
        $judul = 'Lahan Berhasil Diverifikasi';
        $pesan = "Selamat! Data lahan '{$lahan->nama_lahan}' telah disetujui dan diverifikasi oleh Ketua Kelompok Tani.";
        if (!empty($catatan)) {
            $pesan .= " Catatan: {$catatan}";
        }

        $payload = [
            'entity_type' => 'lahan',
            'entity_id' => $lahan->id_lahan,
            'id_lahan' => $lahan->id_lahan,
            'nama_lahan' => $lahan->nama_lahan,
            'nomor_revisi' => $nomorRevisi,
            'catatan' => $catatan,
            'verifikator_id' => $ketua->id_pengguna,
            'idempotency_key' => "lahan:{$lahan->id_lahan}:terverifikasi:revisi_{$nomorRevisi}",
        ];

        return $this->createNotification([
            'id_pengirim' => $ketua->id_pengguna,
            'id_penerima' => $lahan->id_pengguna,
            'id_lahan' => $lahan->id_lahan,
            'id_riwayat' => $riwayat?->id_riwayat,
            'jenis_notifikasi' => $jenis,
            'judul' => $judul,
            'pesan' => $pesan,
            'data_payload' => $payload,
        ]);
    }

    // ==================== DOMAIN: SIKLUS TANAM ====================

    /**
     * Kirim notifikasi pengajuan pengakhiran selesai kepada Ketua Kelompok Tani.
     */
    public function notifySiklusPengajuanSelesai(SiklusTanam $siklus): ?Notifikasi
    {
        $ketua = $this->findActiveKetua($siklus->id_kelompok);
        if (!$ketua) {
            return null;
        }

        $namaPengirim = $siklus->pengguna?->nama ?? 'Anggota';
        $namaLahan = $siklus->lahan?->nama_lahan ?? 'Lahan Tani';
        $komoditas = $siklus->komoditas?->nama_komoditas ?? $siklus->komoditas_nama ?? 'Komoditas';

        $payload = [
            'entity_type' => 'siklus_tanam',
            'entity_id' => $siklus->id_siklus,
            'id_lahan' => $siklus->id_lahan,
            'id_kelompok' => $siklus->id_kelompok,
            'status' => 'menunggu_selesai',
            'komoditas' => $komoditas,
            'nama_lahan' => $namaLahan,
            'idempotency_key' => "siklus:{$siklus->id_siklus}:ajukan_selesai",
        ];

        return $this->createNotification([
            'id_pengirim' => $siklus->id_pengguna,
            'id_penerima' => $ketua->id_pengguna,
            'id_lahan' => $siklus->id_lahan,
            'jenis_notifikasi' => self::JENIS_SIKLUS_PENGAJUAN_SELESAI,
            'judul' => 'Pengajuan Penyelesaian Siklus Tanam',
            'pesan' => "Anggota {$namaPengirim} mengajukan penyelesaian siklus tanam {$komoditas} pada lahan '{$namaLahan}'.",
            'data_payload' => $payload,
        ]);
    }

    /**
     * Kirim notifikasi pengajuan pengakhiran gagal (puso/gagal panen) kepada Ketua Kelompok Tani.
     */
    public function notifySiklusPengajuanGagal(SiklusTanam $siklus, ?string $alasan = null): ?Notifikasi
    {
        $ketua = $this->findActiveKetua($siklus->id_kelompok);
        if (!$ketua) {
            return null;
        }

        $namaPengirim = $siklus->pengguna?->nama ?? 'Anggota';
        $namaLahan = $siklus->lahan?->nama_lahan ?? 'Lahan Tani';
        $komoditas = $siklus->komoditas?->nama_komoditas ?? $siklus->komoditas_nama ?? 'Komoditas';
        $alasanText = !empty($alasan) ? $alasan : ($siklus->alasan_gagal ?? 'Gagal Panen');

        $payload = [
            'entity_type' => 'siklus_tanam',
            'entity_id' => $siklus->id_siklus,
            'id_lahan' => $siklus->id_lahan,
            'id_kelompok' => $siklus->id_kelompok,
            'status' => 'menunggu_gagal',
            'komoditas' => $komoditas,
            'nama_lahan' => $namaLahan,
            'alasan' => $alasanText,
            'idempotency_key' => "siklus:{$siklus->id_siklus}:ajukan_gagal",
        ];

        return $this->createNotification([
            'id_pengirim' => $siklus->id_pengguna,
            'id_penerima' => $ketua->id_pengguna,
            'id_lahan' => $siklus->id_lahan,
            'jenis_notifikasi' => self::JENIS_SIKLUS_PENGAJUAN_GAGAL,
            'judul' => 'Laporan Kegagalan Panen (Siklus Tanam)',
            'pesan' => "PERHATIAN: Anggota {$namaPengirim} melaporkan kegagalan panen pada siklus {$komoditas} di '{$namaLahan}'. Alasan: {$alasanText}.",
            'data_payload' => $payload,
        ]);
    }

    /**
     * Kirim notifikasi persetujuan pengakhiran siklus (selesai/gagal) kepada Anggota pemilik.
     */
    public function notifySiklusDisetujui(
        SiklusTanam $siklus,
        Pengguna $verifikator,
        ?string $catatan = null
    ): ?Notifikasi {
        $namaLahan = $siklus->lahan?->nama_lahan ?? 'Lahan Tani';
        $statusText = $siklus->status === 'selesai' ? 'selesai' : 'dinyatakan gagal';

        $payload = [
            'entity_type' => 'siklus_tanam',
            'entity_id' => $siklus->id_siklus,
            'id_lahan' => $siklus->id_lahan,
            'id_kelompok' => $siklus->id_kelompok,
            'status' => $siklus->status,
            'verifikator_id' => $verifikator->id_pengguna,
            'verifikator_nama' => $verifikator->nama,
            'catatan' => $catatan,
            'idempotency_key' => "siklus:{$siklus->id_siklus}:disetujui:{$siklus->status}",
        ];

        return $this->createNotification([
            'id_pengirim' => $verifikator->id_pengguna,
            'id_penerima' => $siklus->id_pengguna,
            'id_lahan' => $siklus->id_lahan,
            'jenis_notifikasi' => self::JENIS_SIKLUS_DISETUJUI,
            'judul' => 'Pengakhiran Siklus Tanam Disetujui',
            'pesan' => "Pengajuan pengakhiran siklus tanam pada lahan '{$namaLahan}' telah disetujui ({$statusText}). Lahan kini berstatus istirahat.",
            'data_payload' => $payload,
        ]);
    }

    /**
     * Kirim notifikasi penolakan pengakhiran siklus kepada Anggota pemilik.
     */
    public function notifySiklusDitolak(
        SiklusTanam $siklus,
        Pengguna $verifikator,
        string $catatan
    ): ?Notifikasi {
        $namaLahan = $siklus->lahan?->nama_lahan ?? 'Lahan Tani';
        $timestamp = $siklus->tanggal_verifikasi
            ? (is_string($siklus->tanggal_verifikasi) ? $siklus->tanggal_verifikasi : $siklus->tanggal_verifikasi->format('YmdHis'))
            : ($siklus->updated_at?->format('YmdHis') ?? 'ditolak');
        $jenisPengakhiran = $siklus->jenis_pengakhiran ?? 'pengakhiran';

        $payload = [
            'entity_type' => 'siklus_tanam',
            'entity_id' => $siklus->id_siklus,
            'id_lahan' => $siklus->id_lahan,
            'id_kelompok' => $siklus->id_kelompok,
            'status' => 'aktif',
            'jenis_pengakhiran' => $jenisPengakhiran,
            'verifikator_id' => $verifikator->id_pengguna,
            'verifikator_nama' => $verifikator->nama,
            'catatan' => $catatan,
            'idempotency_key' => "siklus:{$siklus->id_siklus}:ditolak:{$jenisPengakhiran}:{$timestamp}",
        ];

        return $this->createNotification([
            'id_pengirim' => $verifikator->id_pengguna,
            'id_penerima' => $siklus->id_pengguna,
            'id_lahan' => $siklus->id_lahan,
            'jenis_notifikasi' => self::JENIS_SIKLUS_DITOLAK,
            'judul' => 'Pengakhiran Siklus Tanam Ditolak',
            'pesan' => "Pengajuan pengakhiran siklus tanam pada lahan '{$namaLahan}' ditolak oleh Ketua. Siklus tetap aktif. Catatan: {$catatan}",
            'data_payload' => $payload,
        ]);
    }

    /**
     * Kirim notifikasi pembatalan siklus mandiri oleh Anggota kepada Ketua Kelompok Tani.
     */
    public function notifySiklusDibatalkan(SiklusTanam $siklus, ?string $alasan = null): ?Notifikasi
    {
        $ketua = $this->findActiveKetua($siklus->id_kelompok);
        if (!$ketua) {
            return null;
        }

        $namaPengirim = $siklus->pengguna?->nama ?? 'Anggota';
        $namaLahan = $siklus->lahan?->nama_lahan ?? 'Lahan Tani';
        $komoditas = $siklus->komoditas?->nama_komoditas ?? $siklus->komoditas_nama ?? 'Komoditas';
        $alasanText = !empty($alasan) ? $alasan : ($siklus->alasan_pengakhiran ?? 'Dibatalkan oleh Anggota');

        $payload = [
            'entity_type' => 'siklus_tanam',
            'entity_id' => $siklus->id_siklus,
            'id_lahan' => $siklus->id_lahan,
            'id_kelompok' => $siklus->id_kelompok,
            'status' => 'dibatalkan',
            'komoditas' => $komoditas,
            'nama_lahan' => $namaLahan,
            'alasan' => $alasanText,
            'idempotency_key' => "siklus:{$siklus->id_siklus}:dibatalkan",
        ];

        return $this->createNotification([
            'id_pengirim' => $siklus->id_pengguna,
            'id_penerima' => $ketua->id_pengguna,
            'id_lahan' => $siklus->id_lahan,
            'jenis_notifikasi' => self::JENIS_SIKLUS_DIBATALKAN,
            'judul' => 'Siklus Tanam Dibatalkan',
            'pesan' => "Anggota {$namaPengirim} membatalkan siklus tanam {$komoditas} pada lahan '{$namaLahan}'. Alasan: {$alasanText}.",
            'data_payload' => $payload,
        ]);
    }

    // ==================== DOMAIN: PANEN ====================

    /**
     * Kirim notifikasi pencatatan panen baru atau revisi panen kepada seluruh Pengurus aktif kelompok terkait.
     *
     * @param Panen $panen
     * @param bool $isRevision
     * @return array<int, Notifikasi> Daftar model Notifikasi yang berhasil dibuat/didapatkan
     */
    public function notifyPanenDiajukan(Panen $panen, bool $isRevision = false): array
    {
        $pengurusList = $this->findActiveVerifikatorPanen($panen->id_kelompok);
        if ($pengurusList->isEmpty()) {
            return [];
        }

        $namaPengirim = $panen->pengguna?->nama ?? 'Anggota';
        $komoditas = $panen->komoditas_nama ?? 'Hasil Tani';
        $berat = (float) ($panen->berat_kg ?? 0);
        $timestamp = $isRevision
            ? ($panen->updated_at?->format('YmdHis') ?? now()->format('YmdHis'))
            : 'awal';

        $jenis = $isRevision ? self::JENIS_PANEN_PENGAJUAN_ULANG : self::JENIS_PANEN_PENGAJUAN_BARU;
        $judul = $isRevision ? 'Revisi Catatan Panen Diajukan' : 'Catatan Panen Baru Diajukan';
        $pesan = $isRevision
            ? "Anggota {$namaPengirim} telah merevisi catatan panen {$panen->kode_panen} ({$komoditas}) dan mengajukannya kembali untuk verifikasi."
            : "Anggota {$namaPengirim} mencatat hasil panen baru {$panen->kode_panen} ({$komoditas}) sebanyak {$berat} kg untuk diverifikasi.";

        $results = [];
        foreach ($pengurusList as $pengurus) {
            $payload = [
                'entity_type' => 'panen',
                'entity_id' => $panen->id_panen,
                'kode_panen' => $panen->kode_panen,
                'id_lahan' => $panen->id_lahan,
                'id_siklus' => $panen->id_siklus,
                'id_kelompok' => $panen->id_kelompok,
                'status' => 'menunggu',
                'berat_kg' => $berat,
                'is_revision' => $isRevision,
                'idempotency_key' => "panen:{$panen->id_panen}:diajukan:{$timestamp}",
            ];

            $notif = $this->createNotification([
                'id_pengirim' => $panen->id_pengguna,
                'id_penerima' => $pengurus->id_pengguna,
                'id_lahan' => $panen->id_lahan,
                'jenis_notifikasi' => $jenis,
                'judul' => $judul,
                'pesan' => $pesan,
                'data_payload' => $payload,
            ]);

            if ($notif) {
                $results[] = $notif;
            }
        }

        return $results;
    }

    /**
     * Kirim notifikasi persetujuan verifikasi panen kepada Anggota pemilik panen.
     */
    public function notifyPanenDisetujui(
        Panen $panen,
        Pengguna $verifikator,
        ?string $catatan = null
    ): ?Notifikasi {
        $komoditas = $panen->komoditas_nama ?? 'Hasil Tani';
        $timestamp = $panen->tanggal_verifikasi
            ? (is_string($panen->tanggal_verifikasi) ? $panen->tanggal_verifikasi : $panen->tanggal_verifikasi->format('YmdHis'))
            : ($panen->updated_at?->format('YmdHis') ?? 'disetujui');

        $payload = [
            'entity_type' => 'panen',
            'entity_id' => $panen->id_panen,
            'kode_panen' => $panen->kode_panen,
            'id_lahan' => $panen->id_lahan,
            'id_siklus' => $panen->id_siklus,
            'id_kelompok' => $panen->id_kelompok,
            'status' => 'disetujui',
            'verifikator_id' => $verifikator->id_pengguna,
            'verifikator_nama' => $verifikator->nama,
            'catatan' => $catatan,
            'idempotency_key' => "panen:{$panen->id_panen}:disetujui:{$timestamp}",
        ];

        return $this->createNotification([
            'id_pengirim' => $verifikator->id_pengguna,
            'id_penerima' => $panen->id_pengguna,
            'id_lahan' => $panen->id_lahan,
            'jenis_notifikasi' => self::JENIS_PANEN_DISETUJUI,
            'judul' => 'Hasil Panen Berhasil Diverifikasi',
            'pesan' => "Selamat! Catatan panen {$panen->kode_panen} ({$komoditas}) telah disetujui oleh Pengurus.",
            'data_payload' => $payload,
        ]);
    }

    /**
     * Kirim notifikasi penolakan verifikasi panen kepada Anggota pemilik panen.
     */
    public function notifyPanenDitolak(
        Panen $panen,
        Pengguna $verifikator,
        ?string $catatan = null
    ): ?Notifikasi {
        $komoditas = $panen->komoditas_nama ?? 'Hasil Tani';
        $timestamp = $panen->tanggal_verifikasi
            ? (is_string($panen->tanggal_verifikasi) ? $panen->tanggal_verifikasi : $panen->tanggal_verifikasi->format('YmdHis'))
            : ($panen->updated_at?->format('YmdHis') ?? 'ditolak');

        $pesan = "Catatan panen {$panen->kode_panen} ({$komoditas}) ditolak oleh Pengurus.";
        if (!empty($catatan)) {
            $pesan .= " Catatan verifikator: {$catatan}";
        }

        $payload = [
            'entity_type' => 'panen',
            'entity_id' => $panen->id_panen,
            'kode_panen' => $panen->kode_panen,
            'id_lahan' => $panen->id_lahan,
            'id_siklus' => $panen->id_siklus,
            'id_kelompok' => $panen->id_kelompok,
            'status' => 'ditolak',
            'verifikator_id' => $verifikator->id_pengguna,
            'verifikator_nama' => $verifikator->nama,
            'catatan' => $catatan,
            'idempotency_key' => "panen:{$panen->id_panen}:ditolak:{$timestamp}",
        ];

        return $this->createNotification([
            'id_pengirim' => $verifikator->id_pengguna,
            'id_penerima' => $panen->id_pengguna,
            'id_lahan' => $panen->id_lahan,
            'jenis_notifikasi' => self::JENIS_PANEN_DITOLAK,
            'judul' => 'Catatan Panen Ditolak',
            'pesan' => $pesan,
            'data_payload' => $payload,
        ]);
    }

    // ==================== CORE DISPATCHER & IDEMPOTENCY ====================

    /**
     * Buat dan simpan record Notifikasi ke database dengan proteksi idempotency dan validasi penerima.
     * Mencegah notifikasi duplikat melalui periksa idempotency_key pada data_payload.
     * Mencegah pengiriman ke pengguna non-aktif.
     *
     * @param array $attributes Atribut notifikasi yang akan disimpan
     * @return Notifikasi|null Model Notifikasi yang telah disimpan atau model existing jika duplikat
     */
    public function createNotification(array $attributes): ?Notifikasi
    {
        $penerimaId = $attributes['id_penerima'] ?? null;
        if (empty($penerimaId)) {
            Log::warning("Pembuatan notifikasi dibatalkan: ID penerima kosong.");
            return null;
        }

        // Validasi status penerima: hanya pengguna Aktif yang boleh menerima notifikasi
        $penerima = Pengguna::find($penerimaId);
        if (!$penerima || $penerima->status !== 'Aktif') {
            Log::warning("Pembuatan notifikasi dibatalkan: Penerima [{$penerimaId}] tidak ditemukan atau berstatus tidak aktif.");
            return null;
        }

        // Proteksi Idempotency: jika payload menyertakan idempotency_key
        $idempotencyKey = $attributes['data_payload']['idempotency_key'] ?? null;
        if (!empty($idempotencyKey)) {
            // PostgreSQL transaction-scoped advisory lock untuk mengeliminasi race condition
            if (DB::connection()->getDriverName() === 'pgsql' && DB::transactionLevel() > 0) {
                try {
                    DB::select('SELECT pg_advisory_xact_lock(hashtext(?))', [$idempotencyKey . ':' . $penerimaId]);
                } catch (Throwable $e) {
                    // Advisory lock bersifat perlindungan concurrency sekunder; jangan gagalkan proses jika error
                    Log::debug("Advisory lock failed (ignored): " . $e->getMessage());
                }
            }

            // Cari apakah notifikasi dengan idempotency_key yang sama untuk penerima ini sudah pernah dibuat
            $existing = Notifikasi::where('id_penerima', $penerimaId)
                ->whereRaw("data_payload->>'idempotency_key' = ?", [$idempotencyKey])
                ->first();

            if ($existing) {
                Log::info("Notifikasi duplikat dicegah via idempotency key: [{$idempotencyKey}] untuk penerima [{$penerimaId}].");
                return $existing;
            }
        }

        $notifikasi = new Notifikasi($attributes);
        $notifikasi->saveWithUniqueRetry();

        return $notifikasi;
    }

    // ==================== TRANSACTION SAFETY HELPER ====================

    /**
     * Jalankan proses dispatch notifikasi setelah transaksi database aktif berhasil commit.
     * Jika saat ini berada di luar transaksi, langsung mengeksekusi callback.
     * Memastikan rollback transaksi bisnis tidak menghasilkan notifikasi palsu.
     * Kesalahan dispatching di-catch dan dicatat ke log tanpa membatalkan transaksi caller.
     *
     * @param callable $callback
     */
    public function afterCommit(callable $callback): void
    {
        if (DB::transactionLevel() > 0) {
            DB::afterCommit(function () use ($callback) {
                try {
                    $callback();
                } catch (Throwable $e) {
                    Log::error("Gagal mengirim notifikasi pada afterCommit: " . $e->getMessage(), [
                        'exception' => $e,
                    ]);
                }
            });
        } else {
            try {
                $callback();
            } catch (Throwable $e) {
                Log::error("Gagal mengirim notifikasi (non-transactional): " . $e->getMessage(), [
                    'exception' => $e,
                ]);
            }
        }
    }
}
