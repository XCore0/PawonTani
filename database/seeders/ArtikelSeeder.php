<?php

namespace Database\Seeders;

use App\Models\Artikel;
use App\Models\Komoditas;
use Illuminate\Database\Seeder;

class ArtikelSeeder extends Seeder
{
    public function run(): void
    {
        $kom = fn (string $nama): ?string => Komoditas::where('nama_komoditas', $nama)->value('id_komoditas');

        $items = [
            [
                'judul'       => 'Mengenal Sistem Tanam Jajar Legowo dan Manfaatnya bagi Petani Padi',
                'kategori'    => 'Budidaya Tanaman',
                'komoditas_id'=> $kom('Padi Sawah'),
                'ringkasan'   => 'Sistem tanam jajar legowo terbukti meningkatkan produktivitas padi hingga 20% berkat optimasi cahaya matahari dan kemudahan perawatan.',
                'isi'         => "Sistem tanam jajar legowo (jarwo) adalah teknik penanaman padi dengan mengatur jarak tanam sehingga setiap beberapa baris terdapat satu baris yang dikosongkan (lorong).\n\n## Keunggulan Jajar Legowo\n\n1. Efek tepi: Seluruh tanaman mendapat posisi tepi yang menerima lebih banyak cahaya matahari.\n2. Sirkulasi udara lebih baik: Kelembapan kanopi turun sehingga risiko penyakit berkurang.\n3. Kemudahan perawatan: Lorong antar baris memudahkan pemupukan dan pemantauan OPT.\n4. Potensi hasil meningkat 15–25% dibanding tanam tegel (25×25 cm).\n\n## Cara Penerapan\n\n1. Gunakan caplak bergigi ganda sebagai panduan jarak tanam.\n2. Tanam 2–3 bibit per rumpun, sedalam 2–3 cm.\n3. Pastikan lorong bebas dari penanaman agar efek tepi optimal.",
                'gambar'      => null,
                'tanggal'     => '2025-07-05',
                'status'      => 'Publik',
            ],
            [
                'judul'       => 'Peran Pupuk Organik dalam Menjaga Kesehatan Tanah Jangka Panjang',
                'kategori'    => 'Nutrisi & Pupuk',
                'komoditas_id'=> null,
                'ringkasan'   => 'Penggunaan pupuk organik secara konsisten memperbaiki struktur tanah, meningkatkan aktivitas mikroba, dan mengurangi ketergantungan pada pupuk kimia.',
                'isi'         => "Kesehatan tanah adalah fondasi utama pertanian berkelanjutan. Penggunaan pupuk kimia berlebihan telah menyebabkan degradasi kualitas tanah di banyak daerah.\n\n## Manfaat Pupuk Organik\n\n1. Memperbaiki struktur tanah — bahan organik menjadi pengikat partikel tanah.\n2. Meningkatkan KTK (Kapasitas Tukar Kation) sehingga hara tidak mudah tercuci.\n3. Suplai hara makro dan mikro secara lambat (slow release).\n4. Meningkatkan aktivitas biologi tanah.\n5. Menstabilkan pH tanah.\n\n## Rekomendasi\n\n- Dosis: 2–5 ton kompos/ha per musim tanam.\n- Aplikasikan 1–2 minggu sebelum tanam.\n- Kombinasikan dengan pupuk anorganik dosis separuh untuk efisiensi optimal.",
                'gambar'      => null,
                'tanggal'     => '2025-07-12',
                'status'      => 'Publik',
            ],
            [
                'judul'       => 'Mengenal Penyakit Antraknosa pada Cabai dan Cara Penanganannya',
                'kategori'    => 'Hama & Penyakit',
                'komoditas_id'=> $kom('Cabai Merah'),
                'ringkasan'   => 'Antraknosa atau patek adalah penyakit jamur paling merugikan pada cabai yang bisa memusnahkan 80% hasil panen jika tidak ditangani sejak dini.',
                'isi'         => "Antraknosa disebabkan oleh jamur Colletotrichum capsici. Pada kondisi lembap dan hujan tinggi, penyakit ini dapat memusnahkan 50–80% produksi.\n\n## Gejala\n\n- Buah muda: bercak coklat muda yang meluas dan tenggelam.\n- Buah matang: bercak hitam melingkar dengan bagian tengah berwarna jingga (massa spora).\n- Buah terinfeksi mengkerut dan mengering.\n\n## Pengendalian Terpadu\n\nPreventif:\n- Gunakan benih bebas patogen dan rendam dalam fungisida sebelum semai.\n- Pasang mulsa plastik untuk mencegah percikan tanah ke buah.\n\nKuratif:\n- Semprot fungisida sistemik azoksistrobin setiap 5–7 hari saat cuaca lembap.\n- Petik dan musnahkan buah terinfeksi agar spora tidak menyebar.",
                'gambar'      => null,
                'tanggal'     => '2025-07-20',
                'status'      => 'Publik',
            ],
            [
                'judul'       => 'Teknologi Smart Farming: Peluang bagi Petani Milenial Indonesia',
                'kategori'    => 'Teknologi Pertanian',
                'komoditas_id'=> null,
                'ringkasan'   => 'Sensor IoT, drone pertanian, dan analitik data mulai diadopsi petani muda untuk meningkatkan efisiensi lahan dan ketepatan keputusan budidaya.',
                'isi'         => "Smart farming mengintegrasikan teknologi informasi, sensor, dan analitik data untuk mengoptimalkan proses produksi pertanian.\n\n## Teknologi yang Mulai Diadopsi\n\n1. Sensor Tanah & Cuaca IoT — memantau kelembapan tanah, suhu, dan pH secara real-time via smartphone.\n2. Drone Pertanian — pemetaan kesehatan tanaman dan penyemprotan presisi (1 ha dalam 10–15 menit).\n3. Aplikasi Pertanian Berbasis AI — rekomendasi pemupukan, prediksi cuaca hiper-lokal, identifikasi hama dari foto.\n4. Irigasi Otomatis — timer dan sensor kelembapan mengoperasikan pompa secara otomatis.\n\n## Tantangan Adopsi\n\n- Investasi awal tinggi untuk drone dan sensor berkualitas.\n- Sinyal internet di daerah pedesaan masih terbatas.\n- Petani senior memerlukan pendampingan teknis.",
                'gambar'      => null,
                'tanggal'     => '2025-08-01',
                'status'      => 'Publik',
            ],
        ];

        foreach ($items as $item) {
            Artikel::firstOrCreate(['judul' => $item['judul']], $item);
        }
    }
}
