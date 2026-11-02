<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenggunaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_pengguna,
            'nama' => $this->nama,
            'nik' => $this->nik,
            'username' => $this->username,
            'email' => $this->email,
            'no_telepon' => $this->no_telepon,
            'alamat' => $this->alamat,
            'foto_profil' => $this->foto_profil,
            'role' => $this->role,
            'status' => $this->status,
            'lokasi' => [
                'provinsi_id' => $this->provinsi_id,
                'kabupaten_id' => $this->kabupaten_id,
                'kecamatan_id' => $this->kecamatan_id,
                'desa_id' => $this->desa_id,
                'nama' => $this->lokasi_nama,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            'kelompok_tani' => $this->whenLoaded('kelompokTani', function () {
                return [
                    'id_kelompok' => $this->kelompokTani?->id_kelompok,
                    'nama_kelompok' => $this->kelompokTani?->nama_kelompok,
                    'alamat' => $this->kelompokTani?->alamat,
                    'status' => $this->kelompokTani?->status,
                ];
            }, function () {
                if ($this->kelompokTani) {
                    return [
                        'id_kelompok' => $this->kelompokTani->id_kelompok,
                        'nama_kelompok' => $this->kelompokTani->nama_kelompok,
                        'alamat' => $this->kelompokTani->alamat,
                        'status' => $this->kelompokTani->status,
                    ];
                }
                return null;
            }),
            'anggota' => $this->whenLoaded('anggota', function () {
                return [
                    'id_anggota' => $this->anggota?->id_anggota,
                    'status_keanggotaan' => $this->anggota?->status_keanggotaan,
                ];
            }, function () {
                if ($this->anggota) {
                    return [
                        'id_anggota' => $this->anggota->id_anggota,
                        'status_keanggotaan' => $this->anggota->status_keanggotaan,
                    ];
                }
                return null;
            }),
        ];
    }
}
