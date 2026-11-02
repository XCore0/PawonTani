<?php

namespace App\Policies;

use App\Models\Lahan;
use App\Models\Pengguna;

class LahanPolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat daftar lahan (viewAny).
     *
     * - Anggota Aktif: Diizinkan melihat daftar lahan miliknya sendiri.
     * - Pengurus Aktif: Diizinkan melihat daftar lahan anggota dalam kelompoknya.
     * - PPL / Admin / Lainnya: Ditolak (belum disepakati).
     */
    public function viewAny(Pengguna $user): bool
    {
        if ($user->status !== 'Aktif') {
            return false;
        }

        if ($user->role === 'Anggota') {
            return !empty($user->id_kelompok);
        }

        if ($user->role === 'Pengurus') {
            return !empty($user->id_kelompok);
        }

        return false;
    }

    /**
     * Tentukan apakah pengguna dapat melihat detail lahan tertentu (view).
     *
     * - Anggota: Hanya dapat melihat lahan miliknya sendiri.
     * - Pengurus: Hanya dapat melihat lahan dari anggota di kelompok taninya sendiri.
     * - PPL / Admin: Ditolak.
     */
    public function view(Pengguna $user, Lahan $lahan): bool
    {
        if ($user->status !== 'Aktif') {
            return false;
        }

        if ($user->role === 'Anggota') {
            return $user->id_pengguna === $lahan->id_pengguna;
        }

        if ($user->role === 'Pengurus') {
            return !empty($user->id_kelompok) && $user->id_kelompok === $lahan->id_kelompok;
        }

        return false;
    }

    /**
     * Tentukan apakah pengguna dapat mendaftarkan/membuat lahan baru (create).
     *
     * - Anggota: Hanya jika berstatus Aktif dan memiliki kelompok tani yang valid.
     * - Pengurus: Tidak boleh membuat lahan.
     * - PPL / Admin: Ditolak.
     */
    public function create(Pengguna $user): bool
    {
        if ($user->status !== 'Aktif') {
            return false;
        }

        return $user->role === 'Anggota' && !empty($user->id_kelompok);
    }

    /**
     * Tentukan apakah pengguna dapat mengubah data lahan (update).
     *
     * - Anggota: Hanya dapat mengubah lahan miliknya sendiri (perubahan data lahan terverifikasi
     *   tetap diizinkan di level policy; reset status & revisi ditangani LahanService).
     * - Pengurus: Tidak boleh mengubah atribut fisik lahan.
     * - PPL / Admin: Ditolak.
     */
    public function update(Pengguna $user, Lahan $lahan): bool
    {
        if ($user->status !== 'Aktif') {
            return false;
        }

        if ($user->role === 'Anggota') {
            return $user->id_pengguna === $lahan->id_pengguna;
        }

        return false;
    }

    /**
     * Tentukan apakah pengguna dapat menghapus lahan (delete).
     *
     * - Anggota: Hanya lahan miliknya sendiri DAN statusnya BUKAN 'terverifikasi'.
     *   Lahan terverifikasi dilarang dihapus oleh anggota.
     * - Pengurus: Tidak boleh menghapus lahan anggota.
     * - PPL / Admin: Ditolak.
     */
    public function delete(Pengguna $user, Lahan $lahan): bool
    {
        if ($user->status !== 'Aktif') {
            return false;
        }

        if ($user->role === 'Anggota') {
            return $user->id_pengguna === $lahan->id_pengguna
                && $lahan->status_verifikasi !== 'terverifikasi';
        }

        return false;
    }

    /**
     * Tentukan apakah pengguna berhak menyetujui verifikasi lahan (verify).
     *
     * - Khusus Ketua:
     *   * Role harus Pengurus
     *   * Jabatan harus Ketua
     *   * Status harus Aktif
     *   * id_kelompok harus sama dengan kelompok pemilik lahan
     * - Pengurus lain (Sekretaris, Bendahara, dll): Ditolak (tanpa fallback).
     * - Anggota / PPL / Admin: Ditolak.
     */
    public function verify(Pengguna $user, Lahan $lahan): bool
    {
        return $this->isKetuaOfKelompok($user, $lahan);
    }

    /**
     * Tentukan apakah pengguna berhak meminta perbaikan atau mencabut status terverifikasi (requestRevision).
     *
     * - Khusus Ketua aktif dari kelompok tani yang sama.
     * - Dapat meminta perbaikan lahan menunggu verifikasi, maupun mencabut status terverifikasi
     *   menjadi perlu_perbaikan setelah pemeriksaan lapangan.
     */
    public function requestRevision(Pengguna $user, Lahan $lahan): bool
    {
        return $this->isKetuaOfKelompok($user, $lahan);
    }

    /**
     * Helper internal: validasi apakah pengguna adalah Ketua aktif dari kelompok lahan terkait.
     */
    protected function isKetuaOfKelompok(Pengguna $user, Lahan $lahan): bool
    {
        if ($user->status !== 'Aktif') {
            return false;
        }

        return $user->role === 'Pengurus'
            && $user->jabatan === 'Ketua'
            && !empty($user->id_kelompok)
            && $user->id_kelompok === $lahan->id_kelompok;
    }
}

