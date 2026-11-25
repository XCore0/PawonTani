<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AktivitasResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imageUrl = null;
        if (!empty($this->foto_aktivitas)) {
            $rawFoto = $this->foto_aktivitas;
            if (str_contains($rawFoto, 'res.cloudinary.com') || str_contains($rawFoto, 'images.unsplash.com')) {
                $imageUrl = $rawFoto;
            } else {
                if (preg_match('#(?:storage|media)[/\\\\](.*)$#i', $rawFoto, $matches)) {
                    $rel = str_replace('\\', '/', $matches[1]);
                } else {
                    $rel = str_replace('\\', '/', ltrim($rawFoto, '/'));
                }
                $imageUrl = url('media/' . $rel);
            }
        }

        $fotoAktivitasListUrls = [];
        if (!empty($this->foto_aktivitas_list) && is_array($this->foto_aktivitas_list)) {
            foreach ($this->foto_aktivitas_list as $f) {
                if ($f) {
                    if (str_contains($f, 'res.cloudinary.com') || str_contains($f, 'images.unsplash.com')) {
                        $fotoAktivitasListUrls[] = $f;
                    } else {
                        if (preg_match('#(?:storage|media)[/\\\\](.*)$#i', $f, $matches)) {
                            $rel = str_replace('\\', '/', $matches[1]);
                        } else {
                            $rel = str_replace('\\', '/', ltrim($f, '/'));
                        }
                        $fotoAktivitasListUrls[] = url('media/' . $rel);
                    }
                }
            }
        } elseif ($imageUrl) {
            $fotoAktivitasListUrls[] = $imageUrl;
        }

        $namaKomoditas = $this->lahan?->komoditas?->nama_komoditas ?? 'Pertanian';
        $namaLahan = $this->lahan?->nama_lahan ?? 'Lahan Petani';
        $luasHa = $this->lahan ? number_format((float) $this->lahan->luas, 2, ',', '.') . ' Ha' : '';
        $blockArea = $luasHa ? "{$luasHa} • {$namaKomoditas}" : $namaKomoditas;

        // Normalisasi kategori
        $kategori = strtolower($this->kategori ?? 'pemupukan');
        $kategoriLabel = match ($kategori) {
            'pemupukan' => 'Pemupukan',
            'penyiraman' => 'Penyiraman',
            'pengairan' => 'Pengairan',
            'penyemprotan' => 'Penyemprotan',
            'penyiangan' => 'Penyiangan',
            'pemangkasan' => 'Pemangkasan',
            default => ucfirst($kategori),
        };

        // Normalisasi kondisi tanaman
        $kondisi = $this->kondisi_tanaman ?? 'sehat';
        $kondisiLabel = match ($kondisi) {
            'sehat' => 'Sehat',
            'perluPerhatian', 'perlu_perhatian' => 'Perlu Perhatian',
            'terserangHama', 'terserang_hama' => 'Terserang Hama/Penyakit',
            default => 'Sehat',
        };

        $kondisiNormalized = match ($kondisi) {
            'perlu_perhatian' => 'perluPerhatian',
            'terserang_hama' => 'terserangHama',
            default => $kondisi,
        };

        $formattedDate = $this->tanggal ? $this->tanggal->translatedFormat('d M Y') : '';
        $rawDate = $this->tanggal ? $this->tanggal->format('Y-m-d') : '';

        return [
            'id' => $this->id_aktivitas,
            'id_aktivitas' => $this->id_aktivitas,
            'id_lahan' => $this->id_lahan,
            'lahan_id' => $this->id_lahan,
            'id_siklus' => $this->id_siklus,
            'is_legacy' => $this->id_siklus === null,
            'siklus' => $this->whenLoaded('siklusTanam', function () {
                return $this->siklusTanam ? [
                    'id_siklus' => $this->siklusTanam->id_siklus,
                    'komoditas_nama' => $this->siklusTanam->komoditas_nama,
                    'musim_tanam' => $this->siklusTanam->musim_tanam,
                    'status' => $this->siklusTanam->status,
                ] : null;
            }),
            'title' => $this->judul ?? $kategoriLabel,
            'judul' => $this->judul ?? $kategoriLabel,
            'crop_name' => $namaKomoditas,
            'cropName' => $namaKomoditas,
            'category' => $kategori,
            'category_label' => $kategoriLabel,
            'condition' => $kondisiNormalized,
            'condition_label' => $kondisiLabel,
            'date' => $formattedDate,
            'raw_date' => $rawDate,
            'time' => $this->waktu ?? '',
            'location' => $namaLahan,
            'block_area' => $blockArea,
            'blockArea' => $blockArea,
            'notes' => $this->catatan ?? '',
            'catatan' => $this->catatan ?? '',
            'image_url' => $imageUrl,
            'imageUrl' => $imageUrl,
            'foto_aktivitas_list' => $fotoAktivitasListUrls,
            'photos' => $fotoAktivitasListUrls,
            'day_number' => $this->hari_setelah_tanam,
            'dayNumber' => $this->hari_setelah_tanam,
            'biaya' => (float) ($this->biaya ?? 0),
            'id_pengguna' => $this->id_pengguna,
            'nama_petani' => $this->pengguna?->nama,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

