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
            }, $this->komoditas?->nama_komoditas),
            'detail_komoditas' => $this->whenLoaded('komoditas', function () {
                return [
                    'id_komoditas' => $this->komoditas?->id_komoditas,
                    'nama_komoditas' => $this->komoditas?->nama_komoditas,
                    'kategori' => $this->komoditas?->kategori,
                    'gambar' => $this->komoditas?->gambar,
                ];
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
            'foto_lahan' => $this->foto_lahan,
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

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
