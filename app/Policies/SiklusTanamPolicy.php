<?php

namespace App\Policies;

use App\Models\Pengguna;
use App\Models\SiklusTanam;

class SiklusTanamPolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat daftar siklus tanam.
     */
    public function viewAny(Pengguna $user): bool
    {
        if ($user->status !== 'Aktif') {
            return false;
        }

        if ($user->role === 'Anggota') {
            return !empty($user->id_kelompok) && $user->anggota?->status_keanggotaan === 'Aktif';
        }

        if ($user->role === 'Pengurus') {
            return !empty($user->id_kelompok);
        }

        return false;
    }

    /**
     * Tentukan apakah pengguna dapat melihat detail siklus tanam tertentu.
     */
    public function view(Pengguna $user, SiklusTanam $siklus): bool
    {
        if ($user->status !== 'Aktif') {
            return false;
        }

        // Anggota: hanya jika siklus berada pada lahan miliknya di kelompok yang sama
        if ($user->role === 'Anggota') {
            return $user->anggota?->status_keanggotaan === 'Aktif'
                && $user->id_pengguna === $siklus->id_pengguna
                && $user->id_kelompok === $siklus->id_kelompok;
        }

        // Pengurus: hanya jika siklus berada di kelompok taninya
        if ($user->role === 'Pengurus') {
            return !empty($user->id_kelompok) && $user->id_kelompok === $siklus->id_kelompok;
        }

        return false;
    }

    /**
     * Tentukan apakah pengguna dapat membuat siklus tanam baru.
     * Hanya Anggota aktif yang sah pada kelompoknya.
     */
    public function create(Pengguna $user): bool
    {
        if ($user->status !== 'Aktif') {
            return false;
        }

        return $user->role === 'Anggota' && !empty($user->id_kelompok) && $user->anggota?->status_keanggotaan === 'Aktif';
    }

    /**
     * Tentukan apakah pengguna dapat melihat foto bukti kegagalan panen.
     * Hanya pemilik siklus dan Pengurus (Ketua) dari kelompok yang sama.
     */
    public function viewBuktiGagal(Pengguna $user, SiklusTanam $siklus): bool
    {
        if ($user->status !== 'Aktif') {
            return false;
        }

        // Anggota: hanya pemilik siklus di kelompok yang sama
        if ($user->role === 'Anggota') {
            return $user->id_pengguna === $siklus->id_pengguna
                && $user->id_kelompok === $siklus->id_kelompok;
        }

        // Pengurus / Ketua: hanya dari kelompok yang sama
        if ($user->role === 'Pengurus') {
            return !empty($user->id_kelompok) && $user->id_kelompok === $siklus->id_kelompok;
        }

        // Admin dan PPL untuk keperluan audit & supervisi
        if (in_array($user->role, ['Admin', 'PPL'], true)) {
            return true;
        }

        return false;
    }
}
