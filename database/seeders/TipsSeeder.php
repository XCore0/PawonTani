<?php

namespace Database\Seeders;

use App\Models\Komoditas;
use App\Models\Tip;
use Illuminate\Database\Seeder;

class TipsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Helper: resolve komoditas_id by name, returns null if not found
        $kom = fn (string $nama): ?string => Komoditas::where('nama_komoditas', $nama)->value('id_komoditas');

        $initialTips = [
            [
                'judul' => '5 Tips Mengendalikan Hama Wereng Secara Alami',
                'kategori' => 'Hama & Penyakit',
                'komoditas_id' => $kom('Padi Sawah'),
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
                'komoditas_id' => $kom('Tomat'),
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
                'komoditas_id' => null,
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
                'komoditas_id' => $kom('Cabai Rawit'),
                'target' => 'Semua Kelompok',
                'ringkasan' => 'Kocorkan larutan NPK seimbang berinterval 7 hari sekali dengan dosis tepat agar bunga dan buah lebat.',
                'isi' => "1. Larutkan 1 sendok makan NPK 16-16-16 ke dalam 5 liter air bersih.\n2. Kocorkan 200 ml larutan per lubang tanam di sore hari.\n3. Tambahkan kalsium nitrat pada awal fase berbunga untuk mencegah patek.\n4. Jangan memupuk saat tanah dalam kondisi sangat kering.",
                'gambar' => 'pupuk-cabai.jpg',
                'tanggal' => '2024-10-05',
                'status' => 'Publik',
            ],
            [
                'judul' => 'Cara Membuat Pupuk Kompos dari Sisa Panen',
                'kategori' => 'Nutrisi & Pupuk',
                'komoditas_id' => null,
                'target' => 'Semua Kelompok',
                'ringkasan' => 'Manfaatkan jerami, batang jagung, dan sisa sayuran untuk membuat kompos berkualitas tinggi dalam 30 hari.',
                'isi' => "1. Kumpulkan sisa panen (jerami, daun, batang) dan potong sepanjang 5-10 cm.\n2. Susun secara berlapis: bahan hijau (nitrogen tinggi) dan bahan coklat (karbon tinggi) dengan perbandingan 1:2.\n3. Siram setiap lapisan dengan air hingga lembap tapi tidak tergenang.\n4. Tambahkan EM4 atau decomposer cair untuk mempercepat penguraian.\n5. Balik tumpukan setiap 7 hari agar aerasi merata.\n6. Kompos siap digunakan setelah 30-40 hari, ditandai warna kehitaman dan tidak berbau.",
                'gambar' => 'kompos.jpg',
                'tanggal' => '2024-12-01',
                'status' => 'Publik',
            ],
            [
                'judul' => 'Tips Menanam Bawang Merah di Musim Hujan',
                'kategori' => 'Budidaya Tanaman',
                'komoditas_id' => $kom('Bawang Merah'),
                'target' => 'Semua Kelompok',
                'ringkasan' => 'Buat bedengan tinggi dan gunakan mulsa plastik hitam perak untuk menjaga kelembapan dan mencegah penyakit busuk umbi.',
                'isi' => "1. Buat bedengan setinggi 25-30 cm agar air hujan tidak menggenang di zona akar.\n2. Pasang mulsa plastik hitam perak untuk menekan pertumbuhan gulma dan menjaga suhu tanah.\n3. Pilih varietas tahan hujan seperti Bima Brebes atau Bauji.\n4. Semprotkan fungisida berbahan aktif mankozeb setiap 5-7 hari sebagai pencegahan.\n5. Pastikan jarak tanam minimal 15 x 20 cm untuk sirkulasi udara yang baik.",
                'gambar' => 'bawang-hujan.jpg',
                'tanggal' => '2024-12-10',
                'status' => 'Publik',
            ],
            [
                'judul' => 'Kenali Gejala Kekurangan Hara Makro pada Tanaman Jagung',
                'kategori' => 'Hama & Penyakit',
                'komoditas_id' => $kom('Jagung'),
                'target' => 'Semua Kelompok',
                'ringkasan' => 'Pelajari ciri-ciri kekurangan N, P, dan K pada jagung sejak dini agar penanganan pupuk lebih tepat sasaran.',
                'isi' => "Kekurangan Nitrogen (N):\n- Daun menguning mulai dari ujung daun bagian bawah, menyebar ke daun atas.\n- Pertumbuhan tanaman terhambat dan batang kurus.\n- Solusi: Tambahkan Urea atau ZA sesuai dosis.\n\nKekurangan Fosfor (P):\n- Daun dan batang berwarna keunguan atau merah-coklat.\n- Akar berkembang buruk, tanaman mudah rebah.\n- Solusi: Aplikasikan SP-36 atau TSP saat pengolahan tanah.\n\nKekurangan Kalium (K):\n- Tepi daun tua mengering dan gosong (scorch), mirip terbakar.\n- Tongkol kecil dan biji tidak penuh.\n- Solusi: Semprot KCl atau K2SO4 lewat daun.",
                'gambar' => 'hara-jagung.jpg',
                'tanggal' => '2025-01-05',
                'status' => 'Publik',
            ],
            [
                'judul' => 'Tips Memilih Benih Padi Berkualitas Sebelum Semai',
                'kategori' => 'Budidaya Tanaman',
                'komoditas_id' => $kom('Padi Sawah'),
                'target' => 'Semua Kelompok',
                'ringkasan' => 'Lakukan seleksi benih dengan uji rendam air garam sebelum semai untuk memastikan daya kecambah optimal.',
                'isi' => "1. Larutkan 2 sendok makan garam dapur ke dalam 1 liter air (larutan 200 g/l).\n2. Masukkan benih padi ke dalam larutan; benih yang mengapung dibuang, yang tenggelam digunakan.\n3. Cuci benih terpilih dengan air bersih untuk menghilangkan sisa garam.\n4. Rendam benih dalam air bersih selama 24 jam, tiriskan, lalu simpan dalam karung basah selama 24 jam untuk proses perkecambahan.\n5. Semai benih yang sudah berkecambah pada persemaian basah atau kering sesuai metode yang digunakan.",
                'gambar' => 'benih-padi.jpg',
                'tanggal' => '2025-01-20',
                'status' => 'Publik',
            ],
            [
                'judul' => 'Strategi Pengendalian Ulat Grayak pada Tanaman Kedelai',
                'kategori' => 'Hama & Penyakit',
                'komoditas_id' => $kom('Kedelai'),
                'target' => 'Semua Kelompok',
                'ringkasan' => 'Kombinasikan perangkap feromon dan musuh alami seperti parasitoid telur untuk menekan serangan ulat grayak secara terpadu.',
                'isi' => "1. Pasang perangkap feromon seks Spodoptera litura di lahan dengan kepadatan 1 perangkap per 10 are.\n2. Pantau perangkap setiap 2 hari untuk menghitung populasi ngengat jantan.\n3. Lepaskan parasitoid telur Trichogramma sp. pada puncak penerbangan ngengat.\n4. Semprotkan Bacillus thuringiensis (Bt) konsentrasi 2 ml/l pada stadium larva instar 1-2.\n5. Lakukan pengecekan manual dan buang kelompok telur yang ditemukan di daun.",
                'gambar' => 'ulat-grayak.jpg',
                'tanggal' => '2025-02-08',
                'status' => 'Publik',
            ],
            [
                'judul' => 'Cara Meningkatkan Produksi Tomat dengan Pemangkasan Tunas Air',
                'kategori' => 'Perawatan Tanaman',
                'komoditas_id' => $kom('Tomat'),
                'target' => 'Semua Kelompok',
                'ringkasan' => 'Pemangkasan tunas air secara rutin setiap minggu terbukti meningkatkan ukuran buah dan produktivitas total tanaman tomat.',
                'isi' => "1. Identifikasi tunas air (wiwilan) yang tumbuh di ketiak daun antara batang utama dan cabang produktif.\n2. Patahkan atau potong tunas air saat masih kecil (panjang <5 cm) dengan tangan atau gunting bersih.\n3. Pertahankan 1-2 batang utama saja agar semua energi tanaman terfokus ke pembentukan buah.\n4. Lakukan pemangkasan setiap 5-7 hari sekali, terutama saat tanaman sedang berbunga dan berbuah.\n5. Setelah pemangkasan, semprotkan bakterisida ringan untuk mencegah infeksi luka.",
                'gambar' => 'pangkas-tomat.jpg',
                'tanggal' => '2025-02-20',
                'status' => 'Draft',
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
