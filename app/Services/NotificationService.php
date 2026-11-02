<?php

namespace App\Services;

use App\Models\Lahan;
use App\Models\Notifikasi;
use App\Models\Pengguna;
use App\Models\RiwayatVerifikasiLahan;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public const JENIS_PENGAJUAN_BARU = 'pengajuan_lahan';
    public const JENIS_PENGAJUAN_ULANG = 'pengajuan_ulang_lahan';
    public const JENIS_PERLU_PERBAIKAN = 'perlu_perbaikan';
    public const JENIS_TERVERIFIKASI = 'lahan_terverifikasi';

    /**
     * Cari Ketua aktif untuk kelompok tani tertentu.
     * Aturan Ketua:
     * - Tepat satu Ketua aktif: kembalikan objek Pengguna.
     * - Tidak ditemukan Ketua aktif: catat Log::warning, kembalikan null.
     * - Lebih dari satu Ketua aktif: catat Log::warning, kembalikan null (ambigu).
     * - Tidak ada fallback ke Sekretaris/Bendahara/Pengurus lain.
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
     * Kirim notifikasi pengajuan lahan baru atau pengajuan ulang perbaikan kepada Ketua Kelompok Tani.
     *
     * @param Lahan $lahan Model lahan yang diajukan
     * @param RiwayatVerifikasiLahan|null $riwayat Riwayat verifikasi terkait (opsional)
     * @param bool $isResubmission Apakah ini pengajuan ulang setelah perbaikan
     * @return Notifikasi|null Mengembalikan model Notifikasi jika berhasil, atau null jika Ketua tidak ditemukan/ambigu
     */
    public function notifyLahanDiajukan(
        Lahan $lahan,
        ?RiwayatVerifikasiLahan $riwayat = null,
        bool $isResubmission = false
    ): ?Notifikasi {
        $ketua = $this->findActiveKetua($lahan->id_kelompok);

        if (!$ketua) {
            // Sesuai business rule: ketidakhadiran Ketua tidak boleh menggagalkan proses penyimpanan lahan
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
            'id_lahan' => $lahan->id_lahan,
            'nama_lahan' => $lahan->nama_lahan,
            'nomor_revisi' => $nomorRevisi,
            'id_pengguna' => $lahan->id_pengguna,
            'id_kelompok' => $lahan->id_kelompok,
            'is_resubmission' => $isResubmission,
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
     *
     * @param Lahan $lahan Model lahan yang diminta perbaikan
     * @param Pengguna $ketua Pengurus yang bertindak sebagai Ketua/verifikator
     * @param string $catatan Alasan/catatan perbaikan yang wajib diisi
     * @param RiwayatVerifikasiLahan|null $riwayat Riwayat verifikasi terkait (opsional)
     * @return Notifikasi
     */
    public function notifyPerluPerbaikan(
        Lahan $lahan,
        Pengguna $ketua,
        string $catatan,
        ?RiwayatVerifikasiLahan $riwayat = null
    ): Notifikasi {
        $nomorRevisi = $lahan->nomor_revisi ?? 1;
        $jenis = self::JENIS_PERLU_PERBAIKAN;
        $judul = 'Permintaan Perbaikan Lahan';
        $pesan = "Pengajuan verifikasi lahan '{$lahan->nama_lahan}' memerlukan perbaikan. Catatan Ketua: {$catatan}";

        $payload = [
            'id_lahan' => $lahan->id_lahan,
            'nama_lahan' => $lahan->nama_lahan,
            'nomor_revisi' => $nomorRevisi,
            'catatan' => $catatan,
            'verifikator_id' => $ketua->id_pengguna,
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
     *
     * @param Lahan $lahan Model lahan yang telah disetujui
     * @param Pengguna $ketua Pengurus yang bertindak sebagai Ketua/verifikator
     * @param RiwayatVerifikasiLahan|null $riwayat Riwayat verifikasi terkait (opsional)
     * @param string|null $catatan Catatan apresiasi/verifikasi opsional
     * @return Notifikasi
     */
    public function notifyLahanTerverifikasi(
        Lahan $lahan,
        Pengguna $ketua,
        ?RiwayatVerifikasiLahan $riwayat = null,
        ?string $catatan = null
    ): Notifikasi {
        $nomorRevisi = $lahan->nomor_revisi ?? 1;
        $jenis = self::JENIS_TERVERIFIKASI;
        $judul = 'Lahan Berhasil Diverifikasi';
        $pesan = "Selamat! Data lahan '{$lahan->nama_lahan}' telah disetujui dan diverifikasi oleh Ketua Kelompok Tani.";
        if (!empty($catatan)) {
            $pesan .= " Catatan: {$catatan}";
        }

        $payload = [
            'id_lahan' => $lahan->id_lahan,
            'nama_lahan' => $lahan->nama_lahan,
            'nomor_revisi' => $nomorRevisi,
            'catatan' => $catatan,
            'verifikator_id' => $ketua->id_pengguna,
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
     * Buat dan simpan record Notifikasi ke database menggunakan saveWithUniqueRetry().
     * Tidak membungkus dalam transaksi global baru agar kompatibel dengan transaksi caller.
     * Tidak menelan exception database.
     *
     * @param array $attributes Atribut notifikasi yang akan disimpan
     * @return Notifikasi Model Notifikasi yang telah disimpan
     */
    public function createNotification(array $attributes): Notifikasi
    {
        $notifikasi = new Notifikasi($attributes);
        $notifikasi->saveWithUniqueRetry();

        return $notifikasi;
    }
}

