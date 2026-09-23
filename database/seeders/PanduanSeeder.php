<?php

namespace Database\Seeders;

use App\Models\Komoditas;
use App\Models\Panduan;
use Illuminate\Database\Seeder;

class PanduanSeeder extends Seeder
{
    public function run(): void
    {
        $kom = fn (string $nama): ?string => Komoditas::where('nama_komoditas', $nama)->value('id_komoditas');

        $items = [
            [
                'judul'       => 'Panduan Lengkap Budidaya Padi Sawah dari Olah Tanah hingga Panen',
                'slug'        => 'panduan-budidaya-padi-sawah',
                'kategori'    => 'Budidaya Tanaman',
                'komoditas_id'=> $kom('Padi Sawah'),
                'ringkasan'   => 'Panduan komprehensif seluruh tahapan budidaya padi sawah mulai pemilihan varietas, persemaian, tanam, pemeliharaan, hingga panen dan pascapanen.',
                'isi'         => "## 1. Pemilihan Varietas\nPilih varietas unggul bersertifikat seperti Ciherang, Inpari 32, atau Mekongga sesuai kondisi lahan.\n\n## 2. Persemaian\n- Siapkan lahan persemaian 400–500 m² per hektare.\n- Tabur benih yang sudah direndam 24 jam dengan kepadatan 40–50 g/m².\n- Bibit siap tanam pada umur 20–25 hari.\n\n## 3. Pengolahan Tanah\n- Bajak lahan sedalam 20–25 cm.\n- Garu dan ratakan permukaan petak sawah.\n- Biarkan lahan tergenang 7–10 hari sebelum tanam.\n\n## 4. Penanaman\n- Tanam 2–3 bibit per rumpun dengan jarak 20×20 cm.\n- Tanam dangkal 2–3 cm dari permukaan tanah.\n\n## 5. Pemupukan\n- Pupuk dasar: 50 kg Urea + 100 kg SP-36 + 75 kg KCl per ha.\n- Pupuk susulan I (21 HST): 100 kg Urea/ha.\n- Pupuk susulan II (40 HST): 50 kg Urea/ha.\n\n## 6. Panen\n- Panen saat 90–95% gabah sudah menguning.\n- Segera keringkan gabah hingga kadar air 14%.",
                'gambar'      => null,
                'tanggal'     => '2025-07-03',
                'status'      => 'Publik',
            ],
            [
                'judul'       => 'Panduan Pengendalian Hama Terpadu (PHT) pada Hortikultura',
                'slug'        => 'panduan-pht-hortikultura',
                'kategori'    => 'Hama & Penyakit',
                'komoditas_id'=> $kom('Cabai Merah'),
                'ringkasan'   => 'Penerapan prinsip PHT untuk tanaman hortikultura guna menekan penggunaan pestisida kimia secara signifikan.',
                'isi'         => "## Prinsip Dasar PHT\nMengutamakan keseimbangan ekosistem dengan menggabungkan berbagai metode pengendalian.\n\n## 1. Pemantauan Rutin\n- Amati lahan minimal 2 kali seminggu.\n- Tentukan ambang ekonomi sebelum memutuskan pengendalian.\n\n## 2. Pengendalian Kultur Teknis\n- Rotasi tanaman dengan famili berbeda setiap musim.\n- Sanitasi lahan: buang dan bakar sisa tanaman terserang.\n\n## 3. Pengendalian Biologis\n- Gunakan agens hayati: Beauveria bassiana untuk ulat, Trichoderma untuk penyakit tular tanah.\n\n## 4. Pengendalian Fisik\n- Pasang perangkap kuning (yellow sticky trap) untuk thrips dan kutu kebul.\n\n## 5. Pengendalian Kimia (Terakhir)\n- Gunakan hanya saat serangan melewati ambang ekonomi.\n- Rotasi bahan aktif untuk mencegah resistensi.",
                'gambar'      => null,
                'tanggal'     => '2025-07-15',
                'status'      => 'Publik',
            ],
            [
                'judul'       => 'Panduan Budidaya Cabai Rawit di Polybag untuk Lahan Sempit',
                'slug'        => 'panduan-cabai-rawit-polybag',
                'kategori'    => 'Budidaya Tanaman',
                'komoditas_id'=> $kom('Cabai Rawit'),
                'ringkasan'   => 'Teknik menanam cabai rawit dalam polybag 40×50 cm menggunakan media campuran tanah, kompos, dan sekam untuk lahan terbatas.',
                'isi'         => "## Persiapan Media Tanam\nCampur tanah top soil, kompos matang, dan sekam padi perbandingan 2:1:1. Isi polybag 40×50 cm hingga 80% penuh.\n\n## Persemaian Benih\n1. Rendam benih dalam air hangat (50°C) selama 15 menit.\n2. Semai di tray menggunakan media cocopeat + kompos.\n3. Benih berkecambah dalam 5–7 hari.\n\n## Pindah Tanam\n- Bibit siap pindah umur 25–30 hari (4–5 daun sejati).\n- Pindahkan sore hari untuk mengurangi stres tanaman.\n\n## Perawatan\n- Penyiraman 1–2 kali sehari.\n- Pupuk susulan NPK 16-16-16 kocor (5 g/l) setiap 2 minggu mulai 3 MST.\n\n## Panen\n- Mulai berbuah umur 75–90 hari setelah tanam.\n- Satu polybag menghasilkan 200–500 g cabai per musim.",
                'gambar'      => null,
                'tanggal'     => '2025-08-05',
                'status'      => 'Publik',
            ],
            [
                'judul'       => 'Panduan Pascapanen dan Penyimpanan Bawang Merah',
                'slug'        => 'panduan-pascapanen-bawang-merah',
                'kategori'    => 'Pascapanen',
                'komoditas_id'=> $kom('Bawang Merah'),
                'ringkasan'   => 'Teknik pengeringan, sortasi, dan penyimpanan bawang merah yang benar untuk menekan susut bobot dan mempertahankan kualitas hingga 3 bulan.',
                'isi'         => "## 1. Waktu Panen\n- Panen saat 70–80% daun sudah rebah alami (umur 60–75 HST).\n- Hindari panen saat hujan lebat.\n\n## 2. Cara Panen\n- Cabut umbi beserta daunnya dengan hati-hati.\n- Ikat 10–15 rumpun menjadi satu ikatan.\n\n## 3. Pengeringan (Curing)\n- Jemur ikatan bawang di para-para bambu selama 7–14 hari.\n- Posisi umbi di atas, daun di bawah.\n\n## 4. Sortasi\n- Pisahkan umbi sehat dari yang busuk atau luka.\n- Kelompokkan berdasarkan ukuran: besar (>4 cm), sedang (2–4 cm), kecil (<2 cm).\n\n## 5. Penyimpanan\n- Simpan di gudang berventilasi baik, suhu 25–30°C, kelembapan 65–70%.\n- Gantung ikatan atau simpan dalam keranjang bambu.\n- Periksa setiap 2 minggu, buang umbi yang membusuk.",
                'gambar'      => null,
                'tanggal'     => '2025-08-20',
                'status'      => 'Publik',
            ],
        ];

        foreach ($items as $item) {
            Panduan::updateOrCreate(['slug' => $item['slug']], $item);
        }
    }
}
