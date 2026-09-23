<?php

namespace Database\Seeders;

use App\Models\Komoditas;
use App\Models\Panduan;
use Illuminate\Database\Seeder;

class PanduanSeeder extends Seeder
{
    public function run(): void
    {
        // Helper: resolve komoditas_id by name, returns null if not found
        $kom = fn (string $nama): ?string => Komoditas::where('nama_komoditas', $nama)->value('id_komoditas');

        $items = [
            [
                'judul' => 'Panduan Dasar Persiapan Lahan',
                'slug' => 'panduan-dasar-persiapan-lahan',
                'kategori' => 'Budidaya Tanaman',
                'komoditas_id' => $kom('Jagung'),
                'ringkasan' => 'Langkah dasar menyiapkan lahan agar siap digunakan untuk budidaya jagung.',
                'isi' => "1. Bersihkan gulma dan sisa tanaman.\n2. Gemburkan tanah sesuai kondisi lahan.\n3. Buat saluran drainase bila diperlukan.\n4. Pastikan lahan siap sebelum penanaman.",
                'tanggal' => '2026-09-20',
                'status' => 'Publik',
            ],
            [
                'judul' => 'Panduan Pemupukan Berimbang',
                'slug' => 'panduan-pemupukan-berimbang',
                'kategori' => 'Nutrisi & Pupuk',
                'komoditas_id' => $kom('Padi Sawah'),
                'ringkasan' => 'Draft panduan untuk membantu menentukan tahapan pemupukan tanaman padi secara lebih teratur.',
                'isi' => "1. Identifikasi kebutuhan tanaman.\n2. Sesuaikan jenis pupuk dengan kondisi tanah.\n3. Tentukan waktu dan dosis pemupukan.\n4. Catat hasil pengamatan setelah pemupukan.",
                'tanggal' => '2026-09-20',
                'status' => 'Draft',
            ],
            [
                'judul' => 'Panduan Lengkap Budidaya Padi Sawah dari Olah Tanah hingga Panen',
                'slug' => 'panduan-budidaya-padi-sawah',
                'kategori' => 'Budidaya Tanaman',
                'komoditas_id' => $kom('Padi Sawah'),
                'ringkasan' => 'Panduan komprehensif seluruh tahapan budidaya padi sawah mulai dari pemilihan varietas, persemaian, tanam, pemeliharaan, hingga panen dan pascapanen.',
                'isi' => "## 1. Pemilihan Varietas\nPilih varietas unggul bersertifikat seperti Ciherang, Inpari 32, atau Mekongga sesuai kondisi lahan dan ketersediaan air. Varietas tahan blas cocok untuk daerah dataran tinggi.\n\n## 2. Persemaian\n- Siapkan lahan persemaian seluas 400-500 m² untuk setiap hektare lahan tanam.\n- Tabur benih yang sudah direndam 24 jam dan diperam 24 jam dengan kepadatan 40-50 g/m².\n- Bibit siap tanam pada umur 20-25 hari (sistem tanam pindah).\n\n## 3. Pengolahan Tanah\n- Bajak lahan sedalam 20-25 cm menggunakan traktor atau bajak.\n- Garu dan ratakan permukaan petak sawah.\n- Biarkan lahan tergenang 7-10 hari sebelum tanam untuk mematikan gulma.\n\n## 4. Penanaman\n- Tanam bibit 2-3 batang per rumpun dengan jarak 20 x 20 cm atau 25 x 25 cm.\n- Tanam dangkal 2-3 cm dari permukaan tanah agar anakan tumbuh optimal.\n\n## 5. Pemupukan\n- Pupuk dasar: 50 kg Urea + 100 kg SP-36 + 75 kg KCl per hektare saat tanam.\n- Pupuk susulan I (21 HST): 100 kg Urea per hektare.\n- Pupuk susulan II (40 HST): 50 kg Urea per hektare.\n\n## 6. Pengairan\n- Fase vegetatif: genangan 3-5 cm.\n- Fase primordia: genangan 5-10 cm.\n- Fase pengisian gabah: pengairan berselang (alternate wetting & drying).\n- Hentikan pengairan 7-10 hari sebelum panen.\n\n## 7. Pengendalian OPT\n- Pantau serangan wereng, penggerek batang, dan blast secara rutin.\n- Gunakan pestisida sesuai ambang ekonomi, utamakan PHT.\n\n## 8. Panen\n- Panen saat 90-95% gabah sudah menguning (90-110 HST tergantung varietas).\n- Gunakan sabit atau mesin combine harvester.\n- Segera keringkan gabah hingga kadar air 14% untuk penyimpanan.",
                'tanggal' => '2025-01-15',
                'status' => 'Publik',
            ],
            [
                'judul' => 'Panduan Pengendalian Hama Terpadu (PHT) pada Hortikultura',
                'slug' => 'panduan-pht-hortikultura',
                'kategori' => 'Hama & Penyakit',
                'komoditas_id' => $kom('Cabai Merah'),
                'ringkasan' => 'Penerapan prinsip Pengendalian Hama Terpadu (PHT) untuk tanaman hortikultura guna menekan penggunaan pestisida kimia secara signifikan.',
                'isi' => "## Prinsip Dasar PHT\nPHT mengutamakan keseimbangan ekosistem dengan menggabungkan berbagai metode pengendalian secara harmonis.\n\n## 1. Pemantauan Rutin\n- Lakukan pengamatan lahan minimal 2 kali seminggu.\n- Gunakan format kartu hama: catat jenis hama, lokasi, dan intensitas serangan.\n- Tentukan ambang ekonomi sebelum mengambil keputusan pengendalian.\n\n## 2. Pengendalian Kultur Teknis\n- Rotasi tanaman dengan tanaman dari famili berbeda setiap musim.\n- Sanitasi lahan: buang dan bakar sisa tanaman terserang.\n- Atur jarak tanam agar sirkulasi udara baik.\n- Gunakan varietas tahan hama/penyakit.\n\n## 3. Pengendalian Biologis\n- Perbanyak musuh alami: laba-laba, kumbang Coccinellidae, parasitoid.\n- Gunakan agens hayati: Beauveria bassiana untuk ulat, Trichoderma untuk penyakit tular tanah.\n- Hindari penyemprotan pestisida spektrum luas yang membunuh serangga berguna.\n\n## 4. Pengendalian Fisik/Mekanis\n- Pasang perangkap kuning (yellow sticky trap) untuk thrips dan kutu kebul.\n- Gunakan jaring serangga (insect net) pada tanaman muda.\n- Pungut dan hancurkan hama secara manual pada serangan ringan.\n\n## 5. Pengendalian Kimia (Terakhir)\n- Gunakan hanya saat serangan melewati ambang ekonomi.\n- Pilih pestisida selektif dan terdaftar resmi.\n- Rotasi bahan aktif untuk mencegah resistensi.\n- Patuhi dosis, waktu aplikasi, dan interval penyemprotan.",
                'tanggal' => '2025-02-10',
                'status' => 'Publik',
            ],
            [
                'judul' => 'Panduan Budidaya Cabai Rawit di Polybag untuk Lahan Sempit',
                'slug' => 'panduan-cabai-rawit-polybag',
                'kategori' => 'Budidaya Tanaman',
                'komoditas_id' => $kom('Cabai Rawit'),
                'ringkasan' => 'Teknik menanam cabai rawit dalam polybag 40 x 50 cm menggunakan media campuran tanah, kompos, dan sekam untuk lahan terbatas.',
                'isi' => "## Persiapan Media Tanam\nCampur tanah top soil, kompos matang, dan sekam padi dengan perbandingan 2:1:1. Masukkan ke polybag ukuran 40 x 50 cm hingga 80% penuh.\n\n## Persemaian Benih\n1. Rendam benih cabai rawit dalam air hangat (50°C) selama 15 menit.\n2. Semai di tray semai atau polybag kecil menggunakan media cocopeat + kompos.\n3. Letakkan di tempat teduh; benih berkecambah dalam 5-7 hari.\n4. Siram setiap pagi menggunakan sprayer agar media tetap lembap.\n\n## Pindah Tanam\n- Bibit siap pindah saat berumur 25-30 hari dan memiliki 4-5 helai daun sejati.\n- Pindahkan sore hari untuk mengurangi stres tanaman.\n- Siram segera setelah tanam.\n\n## Perawatan\n- Penyiraman: 1-2 kali sehari, pagi dan sore, sesuai kondisi cuaca.\n- Pemupukan dasar: pupuk kandang matang 200 g/polybag sebelum tanam.\n- Pupuk susulan: NPK 16-16-16 kocor (5 g/l air) setiap 2 minggu mulai umur 3 MST.\n- Pasang ajir bambu setinggi 60-80 cm saat tanaman mulai tinggi.\n\n## Panen\n- Cabai rawit mulai berbuah pada umur 75-90 hari setelah tanam.\n- Panen pertama dilakukan bertahap setiap 3-5 hari.\n- Satu polybag dapat menghasilkan 200-500 g cabai per musim.",
                'tanggal' => '2025-03-05',
                'status' => 'Publik',
            ],
            [
                'judul' => 'Panduan Irigasi Tetes Sederhana untuk Skala Petani Kecil',
                'slug' => 'panduan-irigasi-tetes-sederhana',
                'kategori' => 'Irigasi & Air',
                'komoditas_id' => $kom('Tomat'),
                'ringkasan' => 'Rancang dan bangun sistem irigasi tetes low-cost menggunakan selang PE dan emitter untuk menghemat air hingga 50% dibanding irigasi konvensional.',
                'isi' => "## Mengapa Irigasi Tetes?\nIrigasi tetes mengantarkan air langsung ke zona akar tanaman sehingga mengurangi penguapan, menekan pertumbuhan gulma, dan menghemat konsumsi air 30-50%.\n\n## Komponen yang Dibutuhkan\n- Tandon air (drum 200 liter atau bak penampung)\n- Pipa utama (mainline) PVC diameter 1 inci\n- Selang lateral PE diameter 12 mm\n- Emitter tetes (drip emitter) laju 2-4 liter/jam\n- Filter Y (screen filter) 120 mesh\n- Konektor, stop kran, dan fitting\n\n## Langkah Perakitan\n1. Tempatkan tandon di ketinggian minimal 1-1,5 meter dari lahan agar tekanan gravitasi cukup.\n2. Pasang filter Y langsung di outlet tandon untuk menyaring kotoran.\n3. Bentangkan pipa utama PVC sepanjang lahan, sambungkan ke outlet tandon via filter.\n4. Pasang selang lateral PE tegak lurus terhadap pipa utama, jarak antar lateral disesuaikan jarak tanam.\n5. Tusukkan emitter tetes pada selang lateral di posisi tiap tanaman.\n6. Uji sistem: buka kran dan periksa semua emitter mengeluarkan air.\n\n## Operasional\n- Aktifkan irigasi 1-2 kali sehari selama 30-60 menit.\n- Periksa dan bersihkan filter setiap minggu.\n- Ganti emitter yang tersumbat dengan merendamnya dalam larutan asam sitrat encer.",
                'tanggal' => '2025-03-20',
                'status' => 'Publik',
            ],
            [
                'judul' => 'Panduan Pascapanen dan Penyimpanan Bawang Merah',
                'slug' => 'panduan-pascapanen-bawang-merah',
                'kategori' => 'Pascapanen',
                'komoditas_id' => $kom('Bawang Merah'),
                'ringkasan' => 'Teknik pengeringan, sortasi, dan penyimpanan bawang merah yang benar untuk menekan susut bobot dan mempertahankan kualitas hingga 3 bulan.',
                'isi' => "## 1. Penentuan Waktu Panen\n- Panen bawang merah saat 70-80% daun sudah rebah secara alami (umur 60-75 HST).\n- Hindari panen saat hujan lebat untuk mencegah busuk di penyimpanan.\n\n## 2. Cara Panen\n- Cabut umbi beserta daunnya dengan hati-hati agar tidak ada luka.\n- Ikat 10-15 rumpun menjadi satu ikatan di bagian daunnya.\n\n## 3. Pengeringan (Curing)\n- Jemur ikatan bawang di atas para-para bambu atau plastik, posisi umbi di atas dan daun di bawah.\n- Keringkan selama 7-14 hari di bawah sinar matahari langsung.\n- Jika hujan, pindahkan ke tempat berventilasi baik.\n- Bawang siap disimpan saat kulit luar mengering, berkerut, dan tidak lengket.\n\n## 4. Sortasi dan Grading\n- Pisahkan umbi sehat dari yang busuk, luka, atau terserang penyakit.\n- Kelompokkan berdasarkan ukuran: besar (>4 cm), sedang (2-4 cm), dan kecil (<2 cm).\n\n## 5. Penyimpanan\n- Simpan dalam gudang berventilasi baik, suhu 25-30°C, dan kelembapan relatif 65-70%.\n- Gantung ikatan bawang atau simpan dalam keranjang bambu; jangan ditumpuk terlalu padat.\n- Periksa setiap 2 minggu, buang umbi yang mulai membusuk.\n- Bawang merah segar dapat disimpan 2-3 bulan dengan teknik ini.",
                'tanggal' => '2025-04-10',
                'status' => 'Publik',
            ],
            [
                'judul' => 'Panduan Pembuatan Pestisida Nabati dari Bahan Lokal',
                'slug' => 'panduan-pestisida-nabati',
                'kategori' => 'Hama & Penyakit',
                'komoditas_id' => null,
                'ringkasan' => 'Cara membuat dan mengaplikasikan pestisida nabati berbahan daun mimba, serai, dan bawang putih yang aman dan murah untuk mengendalikan hama.',
                'isi' => "## Mengapa Pestisida Nabati?\nPestisida nabati lebih ramah lingkungan, tidak meninggalkan residu berbahaya pada produk, dan bahan bakunya mudah diperoleh dari lingkungan sekitar.\n\n## Resep 1: Ekstrak Daun Mimba\n**Bahan:** 500 g daun mimba segar, 10 liter air, 5 ml sabun cuci cair (emulsifier)\n**Cara membuat:**\n1. Tumbuk daun mimba hingga halus.\n2. Rendam dalam 10 liter air selama 12-24 jam.\n3. Saring menggunakan kain halus.\n4. Tambahkan sabun cuci sebagai perata-perekat.\n5. Semprotkan langsung tanpa diencerkan lagi.\n**Sasaran hama:** Wereng, thrips, kutu daun, ulat muda.\n\n## Resep 2: Larutan Serai + Bawang Putih\n**Bahan:** 200 g serai, 100 g bawang putih, 5 liter air\n**Cara membuat:**\n1. Blender serai dan bawang putih dengan sedikit air.\n2. Campurkan ke dalam 5 liter air, aduk rata.\n3. Diamkan 30 menit, saring, dan semprotkan pada tanaman.\n**Sasaran hama:** Kutu kebul, tungau, ngengat.\n\n## Cara Aplikasi\n- Semprotkan pada pagi atau sore hari saat angin tidak kencang.\n- Fokuskan semprotan pada bagian bawah daun tempat hama bersembunyi.\n- Ulangi setiap 5-7 hari atau setelah hujan.\n- Simpan sisa larutan di tempat gelap; gunakan maksimal dalam 2 hari.",
                'tanggal' => '2025-04-25',
                'status' => 'Draft',
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
