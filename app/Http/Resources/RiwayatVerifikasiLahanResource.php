<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiwayatVerifikasiLahanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_riwayat,
            'id_riwayat' => $this->id_riwayat,
            'id_lahan' => $this->id_lahan,
            'aksi' => $this->aksi,
            'status_sebelumnya' => $this->status_sebelumnya,
            'status_sesudahnya' => $this->status_sesudahnya,
            'catatan' => $this->catatan,
            'nomor_revisi' => (int) $this->nomor_revisi,
            'aktor' => $this->whenLoaded('pengguna', function () {
                return [
                    'id_pengguna' => $this->pengguna?->id_pengguna,
                    'nama' => $this->pengguna?->nama,
                    'role' => $this->pengguna?->role,
                    'jabatan' => $this->pengguna?->jabatan,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

