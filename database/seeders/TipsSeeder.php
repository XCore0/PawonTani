<?php

namespace Database\Seeders;

use App\Models\Komoditas;
use App\Models\Tip;
use Illuminate\Database\Seeder;

class TipsSeeder extends Seeder
{
    public function run(): void
    {
        $kom = fn (string $nama): ?string => Komoditas::where('nama_komoditas', $nama)->value('id_komoditas');

        $items = [
            [
                'judul'       => '5 Tips Mengendalikan Hama Wereng Secara Alami',
                'kategori'    => 'Hama & Penyakit',
                'komoditas_id'=> $kom('Padi Sawah'),
                'target'      => 'Semua Kelompok',
                'ringkasan'   => 'Semprotkan ramuan daun mimba & tembakau pada sore hari untuk menekan populasi wereng tanpa bahan kimia.',
                'isi'         => "1. Buat ekstrak daun mimba dan tembakau dengan perendaman selama 24 jam.\n2. Semprotkan cairan pada pangkal batang rumpun padi saat sore hari.\n3. Bersihkan gulma di pematang sawah secara rutin.\n4. Lakukan pengeringan berkala pada petakan sawah.\n5. Pasang lampu perangkap kuning untuk memantau populasi wereng dewasa.",
                'gambar'      => null,
                'tanggal'     => '2025-06-10',
                'status'      => 'Publik',
            ],
            [
                'judul'       => 'Tips Hemat Air Saat Musim Kemarau pada Sayuran',
                'kategori'    => 'Irigasi & Air',
                'komoditas_id'=> $kom('Tomat'),
                'target'      => 'Semua Kelompok',
                'ringkasan'   => 'Gunakan mulsa jerami dan penyiraman pagi hari agar kelembapan tanah perakaran bertahan lebih lama.',
                'isi'         => "1. Berikan mulsa jerami padi setebal 5–8 cm di sekitar pangkal tanaman.\n2. Lakukan penyiraman sebelum pukul 08.00 WIB untuk mencegah penguapan tinggi.\n3. Manfaatkan irigasi tetes sederhana dengan botol bekas berlubang halus.\n4. Hindari mencangkul tanah terlalu sering saat terik matahari.",
                'gambar'      => null,
                'tanggal'     => '2025-06-15',
                'status'      => 'Publik',
            ],
            [
                'judul'       => 'Trik Pemupukan Susulan Cabai Rawit Bebas Rontok',
                'kategori'    => 'Nutrisi & Pupuk',
                'komoditas_id'=> $kom('Cabai Rawit'),
                'target'      => 'Semua Kelompok',
                'ringkasan'   => 'Kocorkan larutan NPK seimbang setiap 7 hari sekali dengan dosis tepat agar bunga dan buah lebat.',
                'isi'         => "1. Larutkan 1 sendok makan NPK 16-16-16 ke dalam 5 liter air bersih.\n2. Kocorkan 200 ml larutan per lubang tanam di sore hari.\n3. Tambahkan kalsium nitrat pada awal fase berbunga untuk mencegah patek.\n4. Jangan memupuk saat tanah dalam kondisi sangat kering.",
                'gambar'      => null,
                'tanggal'     => '2025-07-01',
                'status'      => 'Publik',
            ],
            [
                'judul'       => 'Cara Membuat Pupuk Kompos dari Sisa Panen',
                'kategori'    => 'Nutrisi & Pupuk',
                'komoditas_id'=> null,
                'target'      => 'Semua Kelompok',
                'ringkasan'   => 'Manfaatkan jerami, batang jagung, dan sisa sayuran untuk membuat kompos berkualitas tinggi dalam 30 hari.',
                'isi'         => "1. Kumpulkan sisa panen (jerami, daun, batang) dan potong sepanjang 5–10 cm.\n2. Susun berlapis: bahan hijau (nitrogen) dan bahan coklat (karbon) perbandingan 1:2.\n3. Siram tiap lapisan dengan air hingga lembap tapi tidak tergenang.\n4. Tambahkan EM4 atau decomposer cair untuk mempercepat penguraian.\n5. Balik tumpukan setiap 7 hari agar aerasi merata.\n6. Kompos siap digunakan setelah 30–40 hari, ditandai warna kehitaman dan tidak berbau.",
                'gambar'      => null,
                'tanggal'     => '2025-07-10',
                'status'      => 'Publik',
            ],
        ];

        foreach ($items as $data) {
            Tip::firstOrCreate(['judul' => $data['judul']], $data);
        }
    }
}
