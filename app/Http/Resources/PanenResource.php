<?php

namespace App\Http\Resources;

use App\Models\Panen;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Panen
 */
class PanenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lahan = $this->relationLoaded('lahan') ? $this->lahan : null;
        $poktan = $this->relationLoaded('kelompokTani') ? $this->kelompokTani : null;

        $weightKg = (float) $this->berat_kg;
        $pricePerKg = (float) $this->harga_per_kg;

        $fotoUrl = null;
        if ($this->foto_panen) {
            $fotoUrl = url('media/' . ltrim($this->foto_panen, '/'));
        }

        $photoUrls = [];
        if (!empty($this->foto_panen_list) && is_array($this->foto_panen_list)) {
            foreach ($this->foto_panen_list as $p) {
                if ($p) {
                    $photoUrls[] = url('media/' . ltrim($p, '/'));
                }
            }
        } elseif ($fotoUrl) {
            $photoUrls[] = $fotoUrl;
        }

        return [
            'id' => (string) $this->id_panen,
            'id_panen' => (string) $this->id_panen,
            'kode_panen' => (string) $this->kode_panen,
            'id_lahan' => (string) $this->id_lahan,
            'id_siklus' => $this->id_siklus,
            'is_legacy' => $this->id_siklus === null,
            'siklus' => $this->whenLoaded('siklusTanam', fn () => [
                'id_siklus' => $this->siklusTanam?->id_siklus,
                'status' => $this->siklusTanam?->status,
                'musim_tanam' => $this->siklusTanam?->musim_tanam,
                'varietas' => $this->siklusTanam?->varietas,
                'komoditas_nama' => $this->siklusTanam?->komoditas?->nama_komoditas ?? $this->siklusTanam?->komoditas_nama,
                'tanggal_mulai' => $this->siklusTanam?->tanggal_mulai?->format('Y-m-d'),
                'target_panen_ton' => $this->siklusTanam?->target_panen_ton,
            ]),
            'nama_lahan' => (string) ($lahan?->nama_lahan ?? 'Petak Lahan'),
            'alamat_lahan' => (string) ($lahan?->alamat_lahan ?? ''),
            'blok_luas' => (string) ($lahan ? "Blok Garapan • " . rtrim(rtrim((string)$lahan->luas, '0'), '.') . " Ha" : ''),
            'id_komoditas' => $this->id_komoditas,
            'komoditas_nama' => (string) $this->komoditas_nama,
            'varietas' => (string) $this->varietas,
            'jenis_panen' => $this->jenis_panen ?? 'Gabah Kering Panen (GKP)',
            'crop_type' => strtolower($this->komoditas_nama) === 'jagung' ? 'jagung' : 'padi',
            'status' => (string) $this->status, // menunggu, disetujui, ditolak
            'tanggal_panen' => $this->tanggal_panen?->format('Y-m-d'),
            'tanggal_panen_format' => $this->tanggal_panen?->locale('id')->isoFormat('D MMM YYYY') ?? '',
            'waktu_panen' => (string) ($this->waktu_panen ?? '09:00 WIB'),
            'musim_tanam' => (string) ($this->musim_tanam ?? 'Musim Rendeng'),
            'poktan' => (string) ($poktan?->nama_kelompok ?? 'Kelompok Tani'),
            'berat_kg' => $weightKg,
            'berat_ton' => round($weightKg / 1000.0, 3),
            'berat_kuintal' => round($weightKg / 100.0, 2),
            'kualitas_grade' => (string) ($this->kualitas_grade ?? 'Grade A'),
            'kadar_air' => $this->kadar_air ? (float) $this->kadar_air : null,
            'persen_butir_utuh' => $this->persen_butir_utuh ? (float) $this->persen_butir_utuh : null,
            'persen_hampa_kotoran' => $this->persen_hampa_kotoran ? (float) $this->persen_hampa_kotoran : null,
            'metode_panen' => (string) ($this->metode_panen ?? 'Mesin Combine Harvester'),
            'biaya_panen' => (float) $this->biaya_panen,
            'keterangan_tenaga_kerja' => $this->keterangan_tenaga_kerja ?? '',
            'harga_per_kg' => $pricePerKg,
            'estimasi_nilai' => $weightKg * $pricePerKg,
            'catatan' => (string) ($this->catatan ?? ''),
            'foto_panen' => $fotoUrl,
            'foto_panen_path' => $this->foto_panen,
            'photos' => $photoUrls,
            'diverifikasi_oleh' => $this->diverifikasi_oleh,
            'tanggal_verifikasi' => $this->tanggal_verifikasi?->locale('id')->isoFormat('D MMM YYYY HH:mm'),
            'catatan_verifikasi' => $this->catatan_verifikasi,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
