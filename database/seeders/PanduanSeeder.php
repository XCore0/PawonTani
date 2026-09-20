<?php

namespace Database\Seeders;

use App\Models\Panduan;
use Illuminate\Database\Seeder;

class PanduanSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'judul' => 'Panduan Dasar Persiapan Lahan',
                'slug' => 'panduan-dasar-persiapan-lahan',
                'kategori' => 'Budidaya',
                'komoditas' => 'Jagung',
                'ringkasan' => 'Langkah dasar menyiapkan lahan agar siap digunakan untuk budidaya jagung.',
                'isi' => "1. Bersihkan gulma dan sisa tanaman.\n2. Gemburkan tanah sesuai kondisi lahan.\n3. Buat saluran drainase bila diperlukan.\n4. Pastikan lahan siap sebelum penanaman.",
                'tanggal' => '2026-09-20',
                'status' => 'publik',
            ],
            [
                'judul' => 'Panduan Pemupukan Berimbang',
                'slug' => 'panduan-pemupukan-berimbang',
                'kategori' => 'Pemupukan',
                'komoditas' => 'Padi',
                'ringkasan' => 'Draft panduan untuk membantu menentukan tahapan pemupukan tanaman padi secara lebih teratur.',
                'isi' => "1. Identifikasi kebutuhan tanaman.\n2. Sesuaikan jenis pupuk dengan kondisi tanah.\n3. Tentukan waktu dan dosis pemupukan.\n4. Catat hasil pengamatan setelah pemupukan.",
                'tanggal' => '2026-09-20',
                'status' => 'draft',
            ],
        ];

        foreach ($items as $item) {
            Panduan::updateOrCreate(
                ['slug' => $item['slug']],
                $item
            );
        }
    }
}
