<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LahanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Normalisasi URL foto agar selalu mengarah ke endpoint media dengan header CORS
        $fotoLahanUrl = null;
        if (!empty($this->foto_lahan)) {
            $rawFoto = $this->foto_lahan;
            if (str_contains($rawFoto, 'res.cloudinary.com') || str_contains($rawFoto, 'images.unsplash.com')) {
                $fotoLahanUrl = $rawFoto;
            } else {
                if (preg_match('#(?:storage|media)[/\\\\](.*)$#i', $rawFoto, $matches)) {
                    $rel = str_replace('\\', '/', $matches[1]);
                } else {
                    $rel = str_replace('\\', '/', ltrim($rawFoto, '/'));
                }
                $fotoLahanUrl = url('media/' . $rel);
            }
        }

        $fotoLahanListUrls = [];
        if (!empty($this->foto_lahan_list) && is_array($this->foto_lahan_list)) {
            foreach ($this->foto_lahan_list as $f) {
                if ($f) {
                    if (str_contains($f, 'res.cloudinary.com') || str_contains($f, 'images.unsplash.com')) {
                        $fotoLahanListUrls[] = $f;
                    } else {
                        if (preg_match('#(?:storage|media)[/\\\\](.*)$#i', $f, $matches)) {
                            $rel = str_replace('\\', '/', $matches[1]);
                        } else {
                            $rel = str_replace('\\', '/', ltrim($f, '/'));
                        }
                        $fotoLahanListUrls[] = url('media/' . $rel);
                    }
                }
            }
        } elseif ($fotoLahanUrl) {
            $fotoLahanListUrls[] = $fotoLahanUrl;
        }

        return [
            // Identitas & Alias Kompatibilitas Flutter
            'id' => $this->id_lahan,
            'id_lahan' => $this->id_lahan,
            'nama' => $this->nama_lahan,
            'nama_lahan' => $this->nama_lahan,
            'lokasi' => $this->alamat_lahan,
            'luas' => (float) $this->luas,
            'luas_formatted' => number_format((float) $this->luas, 2, ',', '.') . ' ha',

            // Data Lokasi Terstruktur
            'detail_lokasi' => [
                'provinsi_id' => $this->provinsi_id,
                'kabupaten_id' => $this->kabupaten_id,
                'kecamatan_id' => $this->kecamatan_id,
                'desa_id' => $this->desa_id,
                'alamat_lahan' => $this->alamat_lahan,
                'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
                'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            ],
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,

            // Komoditas
            'komoditas' => $this->whenLoaded('komoditas', function () {
                return $this->komoditas?->nama_komoditas;
            }),
            'detail_komoditas' => $this->whenLoaded('komoditas', function () {
                return $this->komoditas ? [
                    'id_komoditas' => $this->komoditas->id_komoditas,
                    'nama_komoditas' => $this->komoditas->nama_komoditas,
                    'kategori' => $this->komoditas->kategori,
                    'gambar' => $this->komoditas->gambar ?? null,
                ] : null;
            }),

            // Status
            'status' => match (strtolower($this->status_operasional ?? 'aktif')) {
                'aktif' => 'aktif',
                'istirahat' => 'istirahat',
                'persiapan' => 'persiapan',
                default => strtolower($this->status_operasional ?? 'aktif'),
            },

            // Atribut Operasional & Dokumen (Read-Only)
            'musim_tanam' => $this->musim_tanam,
            'status_penguasaan' => $this->status_penguasaan ?? 'Milik Sendiri',
            'foto_lahan' => $fotoLahanUrl,
            'foto_lahan_list' => $fotoLahanListUrls,
            'photos' => $fotoLahanListUrls,
            'keterangan' => $this->keterangan,
            'status_operasional' => $this->status_operasional,
            'status_verifikasi' => $this->status_verifikasi,
            'catatan_verifikasi' => $this->catatan_verifikasi,
            'tanggal_verifikasi' => $this->tanggal_verifikasi?->toIso8601String(),
            'nomor_revisi' => (int) $this->nomor_revisi,

            // Hak Akses Client (Murni Delegasi Policy)
            'is_can_edit' => $request->user()?->can('update', $this->resource) ?? false,
            'is_can_delete' => $request->user()?->can('delete', $this->resource) ?? false,

            // Relasi Terkondisi (whenLoaded)
            'kelompok_tani' => $this->whenLoaded('kelompokTani', function () {
                return [
                    'id_kelompok' => $this->kelompokTani?->id_kelompok,
                    'nama_kelompok' => $this->kelompokTani?->nama_kelompok,
                ];
            }),
            'verifikator' => $this->whenLoaded('verifikator', function () {
                return [
                    'id_pengguna' => $this->verifikator?->id_pengguna,
                    'nama' => $this->verifikator?->nama,
                    'jabatan' => $this->verifikator?->jabatan,
                ];
            }),
            'riwayat_verifikasi' => RiwayatVerifikasiLahanResource::collection(
                $this->whenLoaded('riwayatVerifikasi')
            ),
            'siklus_berjalan' => $this->whenLoaded('siklusBerjalan', function () {
                return $this->siklusBerjalan ? new SiklusTanamResource($this->siklusBerjalan) : null;
            }),

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
