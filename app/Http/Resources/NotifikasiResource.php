<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotifikasiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Sanitasi data_payload: kecualikan metadata internal idempotency_key
        $payload = is_array($this->data_payload) ? $this->data_payload : [];
        unset($payload['idempotency_key']);

        return [
            'id_notifikasi' => $this->id_notifikasi,
            'id' => $this->id_notifikasi,
            'jenis_notifikasi' => $this->jenis_notifikasi,
            'judul' => $this->judul,
            'pesan' => $this->pesan,
            'id_pengirim' => $this->id_pengirim,
            'pengirim_nama' => $this->pengirim?->nama,
            'id_penerima' => $this->id_penerima,
            'id_lahan' => $this->id_lahan,
            'nama_lahan' => $this->lahan?->nama_lahan,
            'data_payload' => !empty($payload) ? $payload : null,
            'sudah_dibaca' => (bool) $this->sudah_dibaca,
            'dibaca_pada' => $this->dibaca_pada?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
