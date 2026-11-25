<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SiklusTanamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_siklus' => $this->id_siklus,
            'id' => $this->id_siklus,
            'id_lahan' => $this->id_lahan,
            'lahan' => $this->whenLoaded('lahan', function () {
                return [
                    'id_lahan' => $this->lahan?->id_lahan,
                    'nama_lahan' => $this->lahan?->nama_lahan,
                    'alamat_lahan' => $this->lahan?->alamat_lahan,
                    'luas' => (float) ($this->lahan?->luas ?? 0),
                    'status_verifikasi' => $this->lahan?->status_verifikasi,
                    'status_operasional' => $this->lahan?->status_operasional,
                ];
            }),
            'id_komoditas' => $this->id_komoditas,
            'komoditas_nama' => $this->komoditas_nama,
            'detail_komoditas' => $this->whenLoaded('komoditas', function () {
                return $this->komoditas ? [
                    'id_komoditas' => $this->komoditas->id_komoditas,
                    'nama_komoditas' => $this->komoditas->nama_komoditas,
                    'kategori' => $this->komoditas->kategori,
                    'gambar' => $this->komoditas->gambar ?? null,
                ] : null;
            }),
            'varietas' => $this->varietas,
            'musim_tanam' => $this->musim_tanam,
            'tanggal_mulai' => $this->tanggal_mulai?->format('Y-m-d'),
            'estimasi_panen' => $this->estimasi_panen?->format('Y-m-d'),
            'tanggal_selesai' => $this->tanggal_selesai?->format('Y-m-d'),
            'status' => $this->status,
            'is_berjalan' => $this->isBerjalan(),
            'is_boleh_input' => $this->isBolehInput(),
            'jenis_pengakhiran' => $this->jenis_pengakhiran,
            'tanggal_pengajuan' => $this->tanggal_pengajuan?->toIso8601String(),
            'tanggal_verifikasi' => $this->tanggal_verifikasi?->toIso8601String(),
            'alasan_pengakhiran' => $this->alasan_pengakhiran,
            'foto_bukti_gagal' => $this->foto_bukti_gagal,
            'foto_bukti_gagal_url' => $this->foto_bukti_gagal ? url("/api/v1/siklus-tanam/{$this->id_siklus}/bukti-gagal") : null,
            'diverifikasi_oleh' => $this->diverifikasi_oleh,
            'catatan_verifikasi' => $this->catatan_verifikasi,
            'target_panen_ton' => $this->target_panen_ton !== null ? (float) $this->target_panen_ton : null,
            'total_panen_kg' => $this->totalPanenKg(),
            'total_panen_ton' => $this->totalPanenTon(),
            'persentase_target' => $this->persentaseTargetPanen(),
            'riwayat_pengakhiran' => $this->whenLoaded('riwayatPengakhiran', function () {
                return $this->riwayatPengakhiran->map(function ($r) {
                    return [
                        'id_riwayat' => $r->id_riwayat,
                        'jenis_pengakhiran' => $r->jenis_pengakhiran,
                        'aksi' => $r->aksi,
                        'status_sebelumnya' => $r->status_sebelumnya,
                        'status_sesudahnya' => $r->status_sesudahnya,
                        'alasan' => $r->alasan,
                        'foto_bukti' => $r->foto_bukti,
                        'verifikator_id' => $r->verifikator_id,
                        'verifikator_nama' => $r->verifikator_nama,
                        'catatan_verifikasi' => $r->catatan_verifikasi,
                        'tanggal_keputusan' => $r->tanggal_keputusan?->toIso8601String(),
                        'snapshot_data' => $r->snapshot_data,
                        'created_at' => $r->created_at?->toIso8601String(),
                    ];
                });
            }),
            'id_kelompok' => $this->id_kelompok,
            'kelompok' => $this->whenLoaded('kelompokTani', function () {
                return [
                    'id_kelompok' => $this->kelompokTani?->id_kelompok,
                    'nama_kelompok' => $this->kelompokTani?->nama_kelompok,
                ];
            }),
            'pencatat' => $this->whenLoaded('pengguna', function () {
                return [
                    'id_pengguna' => $this->pengguna?->id_pengguna,
                    'nama' => $this->pengguna?->nama,
                    'role' => $this->pengguna?->role,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
