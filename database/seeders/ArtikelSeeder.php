<?php

namespace Database\Seeders;

use App\Models\Artikel;
use App\Models\Komoditas;
use Illuminate\Database\Seeder;

class ArtikelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Helper: resolve komoditas_id by name, returns null if not found
        $kom = fn (string $nama): ?string => Komoditas::where('nama_komoditas', $nama)->value('id_komoditas');

        $items = [
            [
                'judul' => 'Mengenal Sistem Tanam Jajar Legowo dan Manfaatnya bagi Petani Padi',
                'kategori' => 'Budidaya Tanaman',
                'komoditas_id' => $kom('Padi Sawah'),
                'ringkasan' => 'Sistem tanam jajar legowo terbukti meningkatkan produktivitas padi hingga 20% dibanding cara tanam konvensional berkat optimasi cahaya matahari dan kemudahan perawatan.',
                'isi' => "Sistem tanam jajar legowo (jarwo) adalah teknik penanaman padi dengan cara mengatur jarak tanam sehingga setiap beberapa baris tanaman terdapat satu baris yang dikosongkan (lorong). Nama \"legowo\" berasal dari bahasa Jawa yang berarti lapang atau longgar.\n\n## Jenis-jenis Jarwo\n\n**Jarwo 2:1** — dua baris tanam, satu baris kosong. Jarak antar baris 20 cm, jarak dalam baris 10 cm, lebar lorong 40 cm. Paling banyak digunakan karena sederhana.\n\n**Jarwo 4:1** — empat baris tanam, satu baris kosong. Populasi tanaman lebih banyak dari jarwo 2:1 namun tetap memberikan efek tepi yang lebih besar dari tanam biasa.\n\n**Jarwo Super** — kombinasi jarwo dengan bibit muda (<21 HSS), pemupukan berimbang berbasis PUTS, dan penggunaan varietas unggul. Potensi hasil bisa mencapai 9-10 ton GKP/ha.\n\n## Keunggulan Jajar Legowo\n\n1. **Efek tepi (border effect)**: Seluruh tanaman mendapat posisi tepi yang menerima lebih banyak cahaya matahari, meningkatkan fotosintesis dan jumlah anakan produktif.\n2. **Sirkulasi udara lebih baik**: Kelembapan kanopi turun sehingga risiko penyakit blast dan bercak daun berkurang.\n3. **Kemudahan perawatan**: Lorong antar baris memudahkan petani masuk untuk pemupukan, penyiangan, dan pemantauan OPT tanpa menginjak rumpun padi.\n4. **Pengendalian gulma lebih efisien**: Mesin penyiang (weeder) dapat dioperasikan melalui lorong.\n5. **Potensi hasil meningkat**: Penelitian Balitbangtan menunjukkan peningkatan hasil 15-25% dibanding tanam tegel (25x25 cm).\n\n## Cara Penerapan di Lapangan\n\n1. Gunakan caplak bergigi ganda (untuk jarwo 2:1) atau buat mal dari bambu sebagai panduan jarak tanam.\n2. Tanam 2-3 bibit per rumpun, sedalam 2-3 cm.\n3. Pastikan lorong bebas dari penanaman agar efek tepi optimal.\n4. Lanjutkan dengan pemupukan dan pengairan sesuai anjuran varietas.\n\nDengan menerapkan sistem jarwo, petani dapat meningkatkan pendapatan sekaligus efisiensi penggunaan sarana produksi.",
                'gambar' => 'jajar-legowo.jpg',
                'tanggal' => '2025-01-10',
                'status' => 'Publik',
            ],
            [
                'judul' => 'Peran Pupuk Organik dalam Menjaga Kesehatan Tanah Jangka Panjang',
                'kategori' => 'Nutrisi & Pupuk',
                'komoditas_id' => null,
                'ringkasan' => 'Penggunaan pupuk organik secara konsisten memperbaiki struktur tanah, meningkatkan aktivitas mikroba, dan mengurangi ketergantungan pada pupuk kimia sintetis.',
                'isi' => "Kesehatan tanah adalah fondasi utama pertanian yang berkelanjutan. Sayangnya, penggunaan pupuk kimia berlebihan selama puluhan tahun telah menyebabkan degradasi kualitas tanah di banyak daerah pertanian di Indonesia.\n\n## Apa Itu Pupuk Organik?\n\nPupuk organik adalah pupuk yang berasal dari bahan-bahan organik seperti kotoran hewan, sisa tanaman, atau limbah organik lainnya yang telah mengalami penguraian. Jenisnya antara lain: kompos, pupuk kandang (pukan), pupuk hijau, vermikompos (kascing), dan bokashi.\n\n## Manfaat Pupuk Organik bagi Tanah\n\n**1. Memperbaiki Struktur Tanah**\nBahan organik bertindak sebagai agen pengikat partikel tanah (soil aggregation), menjadikan tanah liat lebih gembur dan tanah berpasir lebih mampu menahan air serta hara.\n\n**2. Meningkatkan KTK (Kapasitas Tukar Kation)**\nHumus dari bahan organik memiliki KTK tinggi sehingga lebih banyak hara positif yang tersimpan dan tidak mudah tercuci hujan.\n\n**3. Menyuplai Hara Makro dan Mikro Secara Lambat (Slow Release)**\nBerbeda dengan pupuk kimia yang langsung tersedia, pupuk organik melepaskan hara secara bertahap, mengurangi risiko pemborosan hara akibat pencucian.\n\n**4. Meningkatkan Aktivitas Biologi Tanah**\nBahan organik adalah sumber energi bagi jutaan mikroorganisme tanah yang berperan dalam siklus hara dan penekanan patogen.\n\n**5. Menstabilkan pH Tanah**\nHumus memiliki kapasitas buffer yang membantu menjaga pH tanah tetap stabil.\n\n## Rekomendasi Penggunaan\n\n- **Dosis**: 2-5 ton kompos/ha atau 5-10 ton pupuk kandang sapi/ha per musim tanam.\n- **Waktu**: Aplikasikan 1-2 minggu sebelum tanam agar ada waktu dekomposisi lanjut di tanah.\n- **Integrasi**: Kombinasikan dengan pupuk anorganik dosis separuh untuk efisiensi optimal.\n- **Kontinuitas**: Manfaat maksimal dirasakan setelah 2-3 musim tanam berturut-turut.",
                'gambar' => 'pupuk-organik.jpg',
                'tanggal' => '2025-01-28',
                'status' => 'Publik',
            ],
            [
                'judul' => 'Dampak Perubahan Iklim terhadap Pola Tanam Petani di Jawa Tengah',
                'kategori' => 'Iklim & Lingkungan',
                'komoditas_id' => $kom('Padi Sawah'),
                'ringkasan' => 'Pergeseran musim hujan dan kenaikan suhu rata-rata mendorong petani Jawa Tengah untuk menyesuaikan kalender tanam dan memilih varietas yang lebih adaptif.',
                'isi' => "Perubahan iklim bukan lagi isu masa depan — dampaknya sudah dirasakan langsung oleh petani Indonesia saat ini. Di Jawa Tengah, beberapa perubahan signifikan telah terdokumentasi dalam dekade terakhir.\n\n## Perubahan yang Terjadi\n\n**1. Pergeseran Awal Musim Hujan**\nData BMKG menunjukkan bahwa awal musim hujan di Jawa Tengah cenderung bergeser 2-4 minggu lebih lambat dibanding 20 tahun lalu. Akibatnya, kalender tanam MT I yang biasa dimulai Oktober-November kini mundur ke November-Desember.\n\n**2. Intensitas Hujan Ekstrem**\nMeski total curah hujan tidak selalu bertambah, intensitas hujan dalam durasi pendek meningkat. Ini menyebabkan genangan di lahan sawah yang tidak siap dan erosi pada lahan miring.\n\n**3. Periode Kemarau Lebih Panjang**\nMusim kemarau yang memanjang menyulitkan petani yang mengandalkan tadah hujan untuk MT II dan MT III.\n\n**4. Kenaikan Suhu**\nSuhu rata-rata yang naik berdampak pada percepatan evapotranspirasi dan peningkatan populasi hama tertentu seperti wereng.\n\n## Adaptasi yang Dilakukan Petani\n\n- **Pergeseran jadwal tanam**: Mengikuti informasi prakiraan cuaca BMKG untuk menentukan awal semai yang tepat.\n- **Pemilihan varietas genjah**: Menggunakan varietas berumur pendek (90-100 hari) seperti Inpari 42.\n- **Diversifikasi komoditas**: Beralih ke tanaman palawija tahan kering pada MT III.\n- **Pemanenan air hujan**: Membangun embung atau sumur resapan untuk menyimpan air saat musim hujan.",
                'gambar' => 'perubahan-iklim.jpg',
                'tanggal' => '2025-02-15',
                'status' => 'Publik',
            ],
            [
                'judul' => 'Mengenal Penyakit Antraknosa pada Cabai dan Cara Penanganannya',
                'kategori' => 'Hama & Penyakit',
                'komoditas_id' => $kom('Cabai Merah'),
                'ringkasan' => 'Antraknosa atau patek adalah penyakit jamur paling merugikan pada cabai yang bisa memusnahkan 80% hasil panen jika tidak ditangani sejak dini.',
                'isi' => "Antraknosa (patek, busuk buah) yang disebabkan oleh jamur Colletotrichum capsici adalah musuh nomor satu petani cabai di Indonesia. Pada kondisi cuaca lembap dan hujan tinggi, penyakit ini dapat memusnahkan 50-80% produksi dalam waktu singkat.\n\n## Gejala\n\n- Buah muda: muncul bercak kecil berwarna coklat muda yang meluas dan tenggelam (sunken lesion).\n- Buah matang: bercak hitam melingkar dengan bagian tengah berwarna jingga kemerahan (massa spora jamur).\n- Buah yang terinfeksi mengkerut, mengering, dan tetap menggantung di tanaman (mummy fruit).\n\n## Faktor Penyebab Meledaknya Serangan\n\n1. Kelembapan udara >80% selama beberapa hari berturut-turut.\n2. Suhu optimal perkembangan jamur: 25-30 derajat Celcius.\n3. Luka mekanis pada buah akibat serangga atau gesekan.\n4. Benih terinfeksi yang tidak didesinfeksi.\n\n## Pengendalian Terpadu\n\n**Preventif:**\n- Gunakan benih bebas patogen dan rendam dalam fungisida sebelum semai.\n- Tanam varietas tahan antraknosa.\n- Pasang mulsa plastik untuk mencegah percikan tanah ke buah bawah.\n\n**Kuratif:**\n- Semprot fungisida sistemik berbahan aktif azoksistrobin setiap 5-7 hari saat cuaca lembap.\n- Petik dan musnahkan buah terinfeksi agar spora tidak menyebar.\n- Rotasi fungisida dengan bahan aktif berbeda setiap 2-3 aplikasi untuk mencegah resistensi.",
                'gambar' => 'antraknosa-cabai.jpg',
                'tanggal' => '2025-03-01',
                'status' => 'Publik',
            ],
            [
                'judul' => 'Potensi dan Tantangan Pertanian Organik Bersertifikat di Indonesia',
                'kategori' => 'Pertanian Organik',
                'komoditas_id' => null,
                'ringkasan' => 'Permintaan produk organik bersertifikat tumbuh 15% per tahun, namun petani masih menghadapi hambatan biaya sertifikasi, masa transisi, dan akses pasar yang belum merata.',
                'isi' => "Pertanian organik bersertifikat adalah sistem produksi yang melarang penggunaan pupuk dan pestisida sintetis, serta organisme hasil rekayasa genetika (GMO). Di Indonesia, sertifikasi organik diatur oleh SNI 6729:2016.\n\n## Peluang Pasar\n\nPasar produk organik global terus tumbuh pesat. Di Indonesia, kesadaran konsumen perkotaan terhadap produk sehat mendorong pertumbuhan permintaan organik 12-15% per tahun. Harga jual produk organik bersertifikat umumnya 30-100% lebih tinggi dari produk konvensional.\n\n## Tantangan Utama\n\n**1. Masa Transisi (Konversi)**\nLahan bekas pertanian konvensional harus melewati masa transisi 2-3 tahun tanpa input sintetis sebelum dapat disertifikasi. Selama masa ini, petani menanggung biaya konversi tanpa mendapat premium harga organik.\n\n**2. Biaya Sertifikasi**\nBiaya sertifikasi organik mencapai Rp 3-10 juta per kelompok tani per tahun, memberatkan petani kecil.\n\n**3. Kendala Teknis**\nPengendalian hama tanpa pestisida kimia membutuhkan pengetahuan PHT yang lebih dalam. Produktivitas awal umumnya turun 20-30%.\n\n**4. Akses Pasar**\nKebanyakan petani organik di daerah terpencil kesulitan menjangkau konsumen organik di kota besar.\n\n## Langkah Memulai\n\n1. Bergabung dengan kelompok tani organik untuk sertifikasi bersama.\n2. Hubungi Dinas Pertanian setempat untuk program pendampingan organik.\n3. Daftarkan lahan ke lembaga sertifikasi untuk audit awal dan rencana konversi.",
                'gambar' => 'pertanian-organik.jpg',
                'tanggal' => '2025-03-18',
                'status' => 'Publik',
            ],
            [
                'judul' => 'Teknologi Smart Farming: Peluang bagi Petani Milenial Indonesia',
                'kategori' => 'Teknologi Pertanian',
                'komoditas_id' => null,
                'ringkasan' => 'Sensor IoT, drone pertanian, dan analitik data mulai diadopsi petani muda Indonesia untuk meningkatkan efisiensi lahan dan ketepatan keputusan budidaya.',
                'isi' => "Revolusi teknologi tidak melewatkan sektor pertanian. Di berbagai negara maju, smart farming atau pertanian cerdas telah mengubah cara petani mengelola lahan, dan Indonesia mulai mengikuti tren ini, terutama di kalangan petani milenial.\n\n## Apa Itu Smart Farming?\n\nSmart farming adalah pendekatan pertanian modern yang mengintegrasikan teknologi informasi dan komunikasi, sensor, dan analitik data untuk mengoptimalkan proses produksi pertanian.\n\n## Teknologi yang Mulai Diadopsi\n\n**1. Sensor Tanah dan Cuaca IoT**\nSensor kelembapan tanah, suhu, dan pH yang terhubung ke smartphone memungkinkan petani memantau kondisi lahan secara real-time. Sistem ini membantu keputusan irigasi yang lebih tepat, menghemat air hingga 30%.\n\n**2. Drone Pertanian**\nDrone multi-spektral digunakan untuk pemetaan kesehatan tanaman, mendeteksi area stres sebelum gejala tampak secara visual. Drone sprayer dapat menyemprot lahan 1 ha dalam 10-15 menit.\n\n**3. Aplikasi Pertanian Berbasis AI**\nAplikasi agritech lokal menggunakan AI untuk memberikan rekomendasi pemupukan, prediksi cuaca hiper-lokal, dan identifikasi hama dari foto.\n\n**4. Sistem Irigasi Otomatis**\nTimer dan sensor kelembapan yang terintegrasi dapat mengoperasikan pompa irigasi secara otomatis berdasarkan kebutuhan aktual tanaman.\n\n## Tantangan Adopsi\n\n- Investasi awal tinggi untuk drone dan sensor berkualitas.\n- Sinyal internet di daerah pertanian pedesaan masih terbatas.\n- Petani senior memerlukan pendampingan untuk mengoperasikan perangkat.\n\n## Peluang ke Depan\n\nBerbagai startup agritech Indonesia menawarkan model sharing economy — petani dapat menyewa drone atau sensor per paket.",
                'gambar' => 'smart-farming.jpg',
                'tanggal' => '2025-04-05',
                'status' => 'Publik',
            ],
            [
                'judul' => 'Memahami Harga Komoditas: Faktor Penentu Harga Cabai di Pasaran',
                'kategori' => 'Agribisnis & Pasar',
                'komoditas_id' => $kom('Cabai Rawit'),
                'ringkasan' => 'Harga cabai yang berfluktuasi tajam dipengaruhi oleh musim panen, biaya logistik, spekulasi pedagang, dan permintaan hari raya yang harus dipahami petani untuk merencanakan tanam.',
                'isi' => "Harga cabai rawit bisa melonjak dari Rp 20.000 menjadi Rp 100.000 per kilogram dalam hitungan minggu — atau sebaliknya anjlok hingga di bawah Rp 10.000 saat panen raya. Memahami faktor-faktor penentu harga adalah kunci bagi petani cabai untuk membuat keputusan tanam yang lebih cerdas.\n\n## Faktor-faktor yang Mempengaruhi Harga\n\n**1. Siklus Produksi dan Musim Tanam**\nHarga cabai mengikuti pola siklus 3-4 bulanan yang berkorelasi dengan jadwal panen massal. Saat banyak petani panen bersamaan, pasokan melimpah dan harga turun. Saat paceklik, harga melonjak.\n\n**2. Faktor Cuaca**\nHujan lebat dan serangan penyakit dapat merusak panen secara masif, tiba-tiba mengurangi pasokan dan memicu kenaikan harga drastis.\n\n**3. Biaya Logistik dan Rantai Distribusi**\nCabai dari Jawa Timur yang dikirim ke Jakarta melewati 3-5 lapisan pedagang, masing-masing mengambil margin. Biaya BBM, tol, dan pendinginan berkontribusi signifikan terhadap harga akhir.\n\n**4. Permintaan Hari Raya dan Momen Khusus**\nPermintaan cabai melonjak 30-50% menjelang Lebaran, Natal, dan Tahun Baru.\n\n**5. Spekulasi Pedagang**\nPada saat pasokan diprediksi turun, pedagang besar menimbun untuk dijual lebih mahal.\n\n## Strategi Petani Menghadapi Fluktuasi\n\n- **Tanam di luar puncak musim**: Jadwalkan panen saat pasokan sedang sedikit.\n- **Stagger planting**: Tanam secara bertahap setiap 2-3 minggu untuk panen bertahap.\n- **Kontrak penjualan**: Jual sebagian hasil ke industri pengolahan dengan harga tetap.\n- **Bergabung dengan koperasi**: Memperkuat posisi tawar saat bernegosiasi dengan pedagang besar.",
                'gambar' => 'harga-cabai.jpg',
                'tanggal' => '2025-05-12',
                'status' => 'Publik',
            ],
            [
                'judul' => 'Mengenal Varietas Jagung Hibrida Unggul dan Tips Memilih yang Tepat',
                'kategori' => 'Budidaya Tanaman',
                'komoditas_id' => $kom('Jagung'),
                'ringkasan' => 'Memilih varietas jagung hibrida yang tepat sesuai tujuan budidaya, kondisi iklim, dan ketinggian lahan bisa meningkatkan hasil panen hingga 40% dibanding varietas lokal.',
                'isi' => "Jagung hibrida telah mendominasi pertanaman jagung komersial di Indonesia karena keunggulannya dalam produktivitas dan keseragaman.\n\n## Perbedaan Jagung Hibrida vs Varietas Lokal\n\nJagung hibrida menghasilkan 8-12 ton/ha dengan keseragaman tinggi, namun benih harus dibeli tiap musim. Varietas lokal hanya menghasilkan 3-5 ton/ha tetapi benih bisa disimpan dan lebih adaptif terhadap kondisi setempat.\n\n## Varietas Jagung Hibrida Populer di Indonesia\n\n**Bisi-18**\n- Potensi hasil: 10-12 ton/ha\n- Umur: 98-100 hari\n- Ketahanan: tahan bulai dan busuk batang\n- Cocok: dataran rendah-menengah (<800 mdpl)\n\n**Pioneer P36**\n- Potensi hasil: 10-13 ton/ha\n- Umur: 100-105 hari\n- Keunggulan: tongkol besar, biji dalam (deep kernel)\n- Cocok: berbagai jenis tanah, lahan kering dan irigasi\n\n**NK 212**\n- Potensi hasil: 9-11 ton/ha\n- Umur: 95-98 hari (relatif genjah)\n- Ketahanan: cukup tahan kering\n\n**Pertiwi 3**\n- Potensi hasil: 8-10 ton/ha\n- Keunggulan: harga benih lebih terjangkau\n\n## Tips Memilih Varietas yang Tepat\n\n1. **Sesuaikan tujuan**: Untuk pakan ternak? Pilih varietas tinggi biomasa. Untuk konsumsi segar? Pilih varietas sweet corn.\n2. **Perhatikan ketinggian lahan**: Dataran tinggi >700 mdpl lebih cocok varietas toleran suhu rendah.\n3. **Cek ketersediaan lokal**: Varietas yang mudah ditemukan di kios pertanian setempat lebih terjamin keasliannya.\n4. **Konsultasi PPL**: Penyuluh Pertanian Lapangan mengetahui varietas paling berhasil di wilayah setempat.",
                'gambar' => 'jagung-hibrida.jpg',
                'tanggal' => '2025-06-03',
                'status' => 'Draft',
            ],
        ];

        foreach ($items as $item) {
            Artikel::firstOrCreate(
                ['judul' => $item['judul']],
                $item
            );
        }
    }
}
