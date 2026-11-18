<?php

namespace App\Services;

use App\Models\AktivitasTanam;
use App\Models\DokumenAnggota;
use App\Models\DokumenLahan;
use App\Models\Lahan;
use App\Models\Notifikasi;
use App\Models\Pengguna;
use App\Models\PermintaanKeanggotaan;
use App\Models\RiwayatVerifikasiLahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Menghapus data pribadi akun secara permanen sambil mempertahankan ID teknis
 * yang masih direferensikan oleh audit trail/riwayat lahan.
 */
class AccountErasureService
{
    public function erase(Pengguna $user, ?Request $request = null, ?string $reason = null): void
    {
        $request ??= request();
        $userId = $user->id_pengguna;
        $filesToDelete = [];
        $cloudinaryToDelete = [];
        $relatedSubjects = [
            'DokumenAnggota' => [],
            'DokumenLahan' => [],
            'Lahan' => [],
            'AktivitasTanam' => [],
            'PermintaanKeanggotaan' => [],
        ];
        $profilePhoto = $user->foto_profil;

        DB::transaction(function () use ($userId, $request, $reason, &$filesToDelete, &$cloudinaryToDelete, &$relatedSubjects) {
            /** @var Pengguna $lockedUser */
            $lockedUser = Pengguna::query()->whereKey($userId)->lockForUpdate()->firstOrFail();

            // Cabut semua token agar sesi Flutter yang lama tidak lagi berlaku.
            $lockedUser->tokens()->delete();

            $identityDocs = DokumenAnggota::where('id_pengguna', $userId)->get();
            foreach ($identityDocs as $document) {
                $filesToDelete[] = ['disk' => 'documents', 'path' => $document->file_path];
                $relatedSubjects['DokumenAnggota'][] = (string) $document->id;
            }
            DokumenAnggota::where('id_pengguna', $userId)->delete();

            $lands = Lahan::withTrashed()->where('id_pengguna', $userId)->get();
            foreach ($lands as $land) {
                $relatedSubjects['Lahan'][] = (string) $land->id_lahan;
                $sppt = DokumenLahan::where('id_lahan', $land->id_lahan)->first();
                if ($sppt) {
                    $filesToDelete[] = ['disk' => 'documents', 'path' => $sppt->file_path];
                    $relatedSubjects['DokumenLahan'][] = (string) $sppt->id;
                    $sppt->delete();
                }

                if ($land->foto_lahan) {
                    $cloudinaryToDelete[] = $land->foto_lahan;
                }

                // Redaksi informasi properti dan arsipkan record lahan.
                $land->forceFill([
                    'nama_lahan' => 'Lahan diarsipkan',
                    'luas' => 0,
                    'alamat_lahan' => 'Data pribadi telah dihapus.',
                    'latitude' => null,
                    'longitude' => null,
                    'foto_lahan' => null,
                    'keterangan' => null,
                    'catatan_verifikasi' => null,
                ])->save();
                if (!$land->trashed()) {
                    $land->delete();
                }
            }

            // Hapus foto aktivitas lokal setelah transaksi berhasil, dan redaksi isi aktivitas.
            $relatedSubjects['AktivitasTanam'] = AktivitasTanam::withTrashed()
                ->where('id_pengguna', $userId)
                ->pluck('id_aktivitas')->map(fn ($id) => (string) $id)->all();
            $activityPhotos = AktivitasTanam::withTrashed()
                ->where('id_pengguna', $userId)
                ->pluck('foto_aktivitas')
                ->filter(fn ($path) => is_string($path) && $path !== '' && !str_contains($path, '..'));
            foreach ($activityPhotos as $path) {
                if ($this->isExternalUrl($path)) {
                    $host = strtolower((string) parse_url($path, PHP_URL_HOST));
                    $urlPath = (string) parse_url($path, PHP_URL_PATH);
                    if ($host === 'res.cloudinary.com' || str_contains($urlPath, '/storage/')) {
                        $cloudinaryToDelete[] = $path;
                    }
                } else {
                    $filesToDelete[] = ['disk' => 'public', 'path' => $path];
                }
            }

            // Pertahankan ringkasan jenis/tanggal kegiatan, hapus narasi, foto dan biaya pribadi.
            AktivitasTanam::withTrashed()->where('id_pengguna', $userId)->update([
                'judul' => 'Aktivitas diarsipkan',
                'catatan' => null,
                'foto_aktivitas' => null,
                'biaya' => 0,
                'deleted_at' => now(),
            ]);

            RiwayatVerifikasiLahan::where('id_pengguna', $userId)->update([
                'catatan' => null,
                'snapshot_data' => null,
            ]);

            Notifikasi::where('id_penerima', $userId)
                ->orWhere('id_pengirim', $userId)
                ->delete();

            $relatedSubjects['PermintaanKeanggotaan'] = PermintaanKeanggotaan::where('id_pengguna', $userId)
                ->pluck('id')->map(fn ($id) => (string) $id)->all();
            PermintaanKeanggotaan::where('id_pengguna', $userId)->update([
                'id_kelompok' => null,
                'alasan' => null,
                'catatan_admin' => null,
                'status' => 'Dibatalkan',
            ]);

            // Log terdahulu tetap ada tetapi tidak lagi memuat nama, IP, atau user-agent akun.
            DB::table('log_aktivitas_sistem')->where('actor_id', $userId)->update([
                'actor_name' => 'Akun dihapus',
                'actor_role' => 'Anggota',
                'ip_address' => null,
                'user_agent' => null,
                'description' => 'Aktivitas akun yang telah dihapus secara permanen.',
            ]);
            DB::table('log_aktivitas_sistem')
                ->where('subject_type', 'Pengguna')
                ->where('subject_id', $userId)
                ->update(['description' => 'Data subjek telah dihapus secara permanen.']);
            foreach ($relatedSubjects as $type => $ids) {
                if ($ids) {
                    DB::table('log_aktivitas_sistem')
                        ->where('subject_type', $type)
                        ->whereIn('subject_id', $ids)
                        ->update(['description' => 'Data terkait akun yang telah dihapus secara permanen.']);
                }
            }

            $member = $lockedUser->anggota;
            if ($member) {
                $member->forceFill([
                    'status_keanggotaan' => 'Dihapus',
                    'tanggal_keluar' => now(),
                    'alasan_keluar' => 'Akun dihapus secara permanen.',
                    'dikeluarkan_oleh' => null,
                ])->save();
            }

            // Anonimisasi kredensial dan informasi identitas. ID teknis dipertahankan
            // karena menjadi referensi pada riwayat verifikasi dan audit.
            $lockedUser->forceFill([
                'nama' => 'Akun Dihapus',
                'nik' => null,
                'username' => 'deleted_' . Str::lower(Str::random(32)),
                'password' => Hash::make(Str::random(64)),
                'email' => null,
                'no_telepon' => null,
                'alamat' => null,
                'foto_profil' => null,
                'id_kelompok' => null,
                'status' => 'Dihapus',
                'provinsi_id' => null,
                'kabupaten_id' => null,
                'kecamatan_id' => null,
                'desa_id' => null,
                'lokasi_nama' => null,
                'latitude' => null,
                'longitude' => null,
                'remember_token' => null,
            ])->save();
        });

        // File dihapus setelah transaksi database berhasil commit.
        foreach ($filesToDelete as $file) {
            try {
                Storage::disk($file['disk'])->delete($file['path']);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        if ($profilePhoto) {
            $cloudinaryToDelete[] = $profilePhoto;
        }
        foreach (array_unique($cloudinaryToDelete) as $photo) {
            try {
                app(CloudinaryService::class)->delete($photo);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        AuditLogger::log(
            'akun.hapus_permanen',
            'Penghapusan akun anggota selesai; data pribadi dan dokumen terkait telah dihapus/dianonimkan. ' . ($reason ? 'Pemroses: ' . $reason : ''),
            'Pengguna',
            $userId,
            null,
            $request
        );

        // Jangan biarkan log tindakan ini menyimpan identitas anggota yang dihapus bila ia
        // merupakan pelaku penghapusan mandiri.
        if ($request->user()?->id_pengguna === $userId) {
            DB::table('log_aktivitas_sistem')->where('actor_id', $userId)->update([
                'actor_name' => 'Akun dihapus',
                'actor_role' => 'Anggota',
                'ip_address' => null,
                'user_agent' => null,
            ]);
        }
    }

    private function isExternalUrl(string $value): bool
    {
        return (bool) preg_match('/^https?:\/\//i', $value);
    }
}
