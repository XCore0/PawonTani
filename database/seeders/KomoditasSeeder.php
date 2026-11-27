<?php

namespace Database\Seeders;

use App\Models\Komoditas;
use Illuminate\Database\Seeder;

class KomoditasSeeder extends Seeder
{
    /**
     * Master komoditas beserta perkiraan umur panen (hari) untuk estimasi otomatis.
     *
     * @var array<int, array{nama_komoditas: string, kategori: string, umur_panen_hari: int}>
     */
    public const DATA = [
        // Tanaman Pangan
        ['nama_komoditas' => 'Padi Sawah', 'kategori' => 'Tanaman Pangan', 'umur_panen_hari' => 115],
        ['nama_komoditas' => 'Padi Gogo', 'kategori' => 'Tanaman Pangan', 'umur_panen_hari' => 120],
        ['nama_komoditas' => 'Jagung', 'kategori' => 'Tanaman Pangan', 'umur_panen_hari' => 105],
        ['nama_komoditas' => 'Kedelai', 'kategori' => 'Tanaman Pangan', 'umur_panen_hari' => 85],
        ['nama_komoditas' => 'Kacang Tanah', 'kategori' => 'Tanaman Pangan', 'umur_panen_hari' => 100],
        ['nama_komoditas' => 'Kacang Hijau', 'kategori' => 'Tanaman Pangan', 'umur_panen_hari' => 65],
        ['nama_komoditas' => 'Singkong / Ubi Kayu', 'kategori' => 'Tanaman Pangan', 'umur_panen_hari' => 270],
        ['nama_komoditas' => 'Ubi Jalar', 'kategori' => 'Tanaman Pangan', 'umur_panen_hari' => 120],

        // Hortikultura & Sayuran
        ['nama_komoditas' => 'Cabai Rawit', 'kategori' => 'Hortikultura & Sayuran', 'umur_panen_hari' => 90],
        ['nama_komoditas' => 'Cabai Merah', 'kategori' => 'Hortikultura & Sayuran', 'umur_panen_hari' => 100],
        ['nama_komoditas' => 'Bawang Merah', 'kategori' => 'Hortikultura & Sayuran', 'umur_panen_hari' => 60],
        ['nama_komoditas' => 'Bawang Putih', 'kategori' => 'Hortikultura & Sayuran', 'umur_panen_hari' => 120],
        ['nama_komoditas' => 'Tomat', 'kategori' => 'Hortikultura & Sayuran', 'umur_panen_hari' => 90],
        ['nama_komoditas' => 'Terong', 'kategori' => 'Hortikultura & Sayuran', 'umur_panen_hari' => 90],
        ['nama_komoditas' => 'Bayam', 'kategori' => 'Hortikultura & Sayuran', 'umur_panen_hari' => 30],
        ['nama_komoditas' => 'Kangkung', 'kategori' => 'Hortikultura & Sayuran', 'umur_panen_hari' => 30],
        ['nama_komoditas' => 'Sawi & Pakcoy', 'kategori' => 'Hortikultura & Sayuran', 'umur_panen_hari' => 35],
        ['nama_komoditas' => 'Kubis / Kol', 'kategori' => 'Hortikultura & Sayuran', 'umur_panen_hari' => 90],
        ['nama_komoditas' => 'Mentimun', 'kategori' => 'Hortikultura & Sayuran', 'umur_panen_hari' => 45],
        ['nama_komoditas' => 'Kacang Panjang', 'kategori' => 'Hortikultura & Sayuran', 'umur_panen_hari' => 60],
        ['nama_komoditas' => 'Wortel', 'kategori' => 'Hortikultura & Sayuran', 'umur_panen_hari' => 90],
        ['nama_komoditas' => 'Kentang', 'kategori' => 'Hortikultura & Sayuran', 'umur_panen_hari' => 100],

        // Buah-buahan
        ['nama_komoditas' => 'Melon', 'kategori' => 'Buah-buahan', 'umur_panen_hari' => 75],
        ['nama_komoditas' => 'Semangka', 'kategori' => 'Buah-buahan', 'umur_panen_hari' => 75],
        ['nama_komoditas' => 'Pisang', 'kategori' => 'Buah-buahan', 'umur_panen_hari' => 300],
        ['nama_komoditas' => 'Pepaya', 'kategori' => 'Buah-buahan', 'umur_panen_hari' => 270],
        ['nama_komoditas' => 'Jeruk', 'kategori' => 'Buah-buahan', 'umur_panen_hari' => 365],
        ['nama_komoditas' => 'Mangga', 'kategori' => 'Buah-buahan', 'umur_panen_hari' => 365],
        ['nama_komoditas' => 'Alpukat', 'kategori' => 'Buah-buahan', 'umur_panen_hari' => 365],
        ['nama_komoditas' => 'Durian', 'kategori' => 'Buah-buahan', 'umur_panen_hari' => 1825],
        ['nama_komoditas' => 'Nanas', 'kategori' => 'Buah-buahan', 'umur_panen_hari' => 450],
        ['nama_komoditas' => 'Jambu Kristal', 'kategori' => 'Buah-buahan', 'umur_panen_hari' => 240],

        // Perkebunan & Rempah
        ['nama_komoditas' => 'Kopi', 'kategori' => 'Perkebunan & Rempah', 'umur_panen_hari' => 730],
        ['nama_komoditas' => 'Kakao / Cokelat', 'kategori' => 'Perkebunan & Rempah', 'umur_panen_hari' => 540],
        ['nama_komoditas' => 'Kelapa', 'kategori' => 'Perkebunan & Rempah', 'umur_panen_hari' => 1095],
        ['nama_komoditas' => 'Cengkeh', 'kategori' => 'Perkebunan & Rempah', 'umur_panen_hari' => 1825],
        ['nama_komoditas' => 'Lada', 'kategori' => 'Perkebunan & Rempah', 'umur_panen_hari' => 730],
        ['nama_komoditas' => 'Jahe', 'kategori' => 'Perkebunan & Rempah', 'umur_panen_hari' => 270],
        ['nama_komoditas' => 'Kunyit', 'kategori' => 'Perkebunan & Rempah', 'umur_panen_hari' => 270],
        ['nama_komoditas' => 'Lengkuas', 'kategori' => 'Perkebunan & Rempah', 'umur_panen_hari' => 300],
        ['nama_komoditas' => 'Serai', 'kategori' => 'Perkebunan & Rempah', 'umur_panen_hari' => 120],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::DATA as $item) {
            Komoditas::updateOrCreate(
                ['nama_komoditas' => $item['nama_komoditas']],
                [
                    'kategori' => $item['kategori'],
                    'umur_panen_hari' => $item['umur_panen_hari'],
                ]
            );
        }
    }
}
