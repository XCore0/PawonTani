<?php

namespace Database\Seeders;

use App\Models\Komoditas;
use Illuminate\Database\Seeder;

class KomoditasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // Tanaman Pangan
            ['nama_komoditas' => 'Padi Sawah', 'kategori' => 'Tanaman Pangan'],
            ['nama_komoditas' => 'Padi Gogo', 'kategori' => 'Tanaman Pangan'],
            ['nama_komoditas' => 'Jagung', 'kategori' => 'Tanaman Pangan'],
            ['nama_komoditas' => 'Kedelai', 'kategori' => 'Tanaman Pangan'],
            ['nama_komoditas' => 'Kacang Tanah', 'kategori' => 'Tanaman Pangan'],
            ['nama_komoditas' => 'Kacang Hijau', 'kategori' => 'Tanaman Pangan'],
            ['nama_komoditas' => 'Singkong / Ubi Kayu', 'kategori' => 'Tanaman Pangan'],
            ['nama_komoditas' => 'Ubi Jalar', 'kategori' => 'Tanaman Pangan'],

            // Hortikultura & Sayuran
            ['nama_komoditas' => 'Cabai Rawit', 'kategori' => 'Hortikultura & Sayuran'],
            ['nama_komoditas' => 'Cabai Merah', 'kategori' => 'Hortikultura & Sayuran'],
            ['nama_komoditas' => 'Bawang Merah', 'kategori' => 'Hortikultura & Sayuran'],
            ['nama_komoditas' => 'Bawang Putih', 'kategori' => 'Hortikultura & Sayuran'],
            ['nama_komoditas' => 'Tomat', 'kategori' => 'Hortikultura & Sayuran'],
            ['nama_komoditas' => 'Terong', 'kategori' => 'Hortikultura & Sayuran'],
            ['nama_komoditas' => 'Bayam', 'kategori' => 'Hortikultura & Sayuran'],
            ['nama_komoditas' => 'Kangkung', 'kategori' => 'Hortikultura & Sayuran'],
            ['nama_komoditas' => 'Sawi & Pakcoy', 'kategori' => 'Hortikultura & Sayuran'],
            ['nama_komoditas' => 'Kubis / Kol', 'kategori' => 'Hortikultura & Sayuran'],
            ['nama_komoditas' => 'Mentimun', 'kategori' => 'Hortikultura & Sayuran'],
            ['nama_komoditas' => 'Kacang Panjang', 'kategori' => 'Hortikultura & Sayuran'],
            ['nama_komoditas' => 'Wortel', 'kategori' => 'Hortikultura & Sayuran'],
            ['nama_komoditas' => 'Kentang', 'kategori' => 'Hortikultura & Sayuran'],

            // Buah-buahan
            ['nama_komoditas' => 'Melon', 'kategori' => 'Buah-buahan'],
            ['nama_komoditas' => 'Semangka', 'kategori' => 'Buah-buahan'],
            ['nama_komoditas' => 'Pisang', 'kategori' => 'Buah-buahan'],
            ['nama_komoditas' => 'Pepaya', 'kategori' => 'Buah-buahan'],
            ['nama_komoditas' => 'Jeruk', 'kategori' => 'Buah-buahan'],
            ['nama_komoditas' => 'Mangga', 'kategori' => 'Buah-buahan'],
            ['nama_komoditas' => 'Alpukat', 'kategori' => 'Buah-buahan'],
            ['nama_komoditas' => 'Durian', 'kategori' => 'Buah-buahan'],
            ['nama_komoditas' => 'Nanas', 'kategori' => 'Buah-buahan'],
            ['nama_komoditas' => 'Jambu Kristal', 'kategori' => 'Buah-buahan'],

            // Perkebunan & Rempah
            ['nama_komoditas' => 'Kopi', 'kategori' => 'Perkebunan & Rempah'],
            ['nama_komoditas' => 'Kakao / Cokelat', 'kategori' => 'Perkebunan & Rempah'],
            ['nama_komoditas' => 'Kelapa', 'kategori' => 'Perkebunan & Rempah'],
            ['nama_komoditas' => 'Cengkeh', 'kategori' => 'Perkebunan & Rempah'],
            ['nama_komoditas' => 'Lada', 'kategori' => 'Perkebunan & Rempah'],
            ['nama_komoditas' => 'Jahe', 'kategori' => 'Perkebunan & Rempah'],
            ['nama_komoditas' => 'Kunyit', 'kategori' => 'Perkebunan & Rempah'],
            ['nama_komoditas' => 'Lengkuas', 'kategori' => 'Perkebunan & Rempah'],
            ['nama_komoditas' => 'Serai', 'kategori' => 'Perkebunan & Rempah'],
        ];

        foreach ($data as $item) {
            Komoditas::firstOrCreate(
                ['nama_komoditas' => $item['nama_komoditas']],
                ['kategori' => $item['kategori']]
            );
        }
    }
}
