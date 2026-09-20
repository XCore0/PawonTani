<?php

namespace Database\Seeders;

use App\Models\Tip;
use Illuminate\Database\Seeder;

class TipsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $initialTips = [
            [
                'judul' => '5 Tips Mengendalikan Hama Wereng Secara Alami',
                'kategori' => 'Hama & Penyakit',
                'komoditas' => 'Padi Sawah',
                'target' => 'Semua Kelompok',
                'ringkasan' => 'Semprotkan ramuan daun mimba & tembakau pada sore hari untuk menekan populasi wereng tanpa kimia.',
                'isi' => "1. Buat ekstrak daun mimba dan tembakau dengan perendaman selama 24 jam.\n2. Semprotkan cairan pada pangkal batang rumpun padi saat sore hari.\n3. Bersihkan gulma di pematang sawah secara rutin.\n4. Lakukan pengeringan berkala pada petakan sawah.\n5. Pasang lampu perangkap kuning untuk memantau populasi wereng dewasa.",
                'gambar' => 'wereng.jpg',
                'tanggal' => '2024-11-12',
                'status' => 'Publik',
            ],
            [
                'judul' => 'Tips Hemat Air Saat Musim Kemarau pada Sayuran',
                'kategori' => 'Irigasi & Air',
                'komoditas' => 'Tomat',
                'target' => 'Semua Kelompok',
                'ringkasan' => 'Gunakan mulsa jerami dan penyiraman pagi hari agar kelembapan tanah perakaran bertahan lebih lama.',
                'isi' => "1. Berikan mulsa jerami padi setebal 5-8 cm di sekitar pangkal tanaman.\n2. Lakukan penyiraman sebelum pukul 08.00 WIB untuk mencegah penguapan tinggi.\n3. Manfaatkan irigasi tetes sederhana dengan botol bekas berlubang halus.\n4. Hindari mencangkul tanah terlalu sering saat terik matahari.",
                'gambar' => 'hemat-air.jpg',
                'tanggal' => '2024-10-30',
                'status' => 'Publik',
            ],
            [
                'judul' => 'Tips Mempercepat Pertumbuhan Akar Tanaman Muda',
                'kategori' => 'Perawatan Tanaman',
                'komoditas' => 'Semua Komoditas',
                'target' => 'Semua Kelompok',
                'ringkasan' => 'Rendam akar semaian dengan larutan air bawang merah sebagai ZPT alami sebelum pindah tanam.',
                'isi' => "1. Haluskan 3-4 siung bawang merah dan campurkan ke 1 liter air hangat.\n2. Rendam akar bibit selama 10-15 menit sebelum ditanam ke bedengan.\n3. Lindungi bibit dari sinar matahari terik langsung selama 3 hari pertama.\n4. Pastikan media tanam gembur dan tidak tergenang air.",
                'gambar' => 'akar-tanam.jpg',
                'tanggal' => '2024-10-15',
                'status' => 'Draft',
            ],
            [
                'judul' => 'Trik Pemupukan Susulan Cabai Rawit Bebas Rontok',
                'kategori' => 'Nutrisi & Pupuk',
                'komoditas' => 'Cabai Rawit',
                'target' => 'Semua Kelompok',
                'ringkasan' => 'Kocorkan larutan NPK seimbang berinterval 7 hari sekali dengan dosis tepat agar bunga dan buah lebat.',
                'isi' => "1. Larutkan 1 sendok makan NPK 16-16-16 ke dalam 5 liter air bersih.\n2. Kocorkan 200 ml larutan per lubang tanam di sore hari.\n3. Tambahkan kalsium nitrat pada awal fase berbunga untuk mencegah patek.\n4. Jangan memupuk saat tanah dalam kondisi sangat kering.",
                'gambar' => 'pupuk-cabai.jpg',
                'tanggal' => '2024-10-05',
                'status' => 'Publik',
            ],
        ];

        foreach ($initialTips as $data) {
            Tip::firstOrCreate(
                ['judul' => $data['judul']],
                $data
            );
        }
    }
}
