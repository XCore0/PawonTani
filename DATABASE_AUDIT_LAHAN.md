# PAWONTANI – DATABASE AUDIT REPORT
## Audit Struktur Database Aktual PostgreSQL / Supabase Menjelang Implementasi Fitur Lahan

> **Tanggal Audit:** 1 November 2026  
> **Target Database:** PostgreSQL 17.6 (Supabase Cloud - Region `ap-southeast-1`)  
> **Koneksi:** Pooler Port 6543 / Direct Database (`postgres`)  
> **Status Audit:** Selesai (Read-Only Inspection)  
> **Tujuan:** Verifikasi keselarasan struktur database riil dengan baseline [TECHNICAL_DESIGN_LAHAN.md](TECHNICAL_DESIGN_LAHAN.md) v3.0

---

## 1. Daftar Tabel Aktual

Berdasarkan inspeksi langsung pada skema `public` PostgreSQL di Supabase, terdapat **10 tabel operasional aplikasi** (di luar tabel internal sistem Supabase seperti `auth`, `storage`, `realtime`, dan `vault`):

| No | Nama Tabel | Jumlah Kolom | Ukuran Disk | Status RLS (Row Level Security) | Deskripsi / Fungsi |
|---|---|:---:|:---:|:---:|---|
| 1 | `kelompok_tani` | 6 | 48.00 KB | **ENABLED** | Master data kelompok tani |
| 2 | `pengguna` | 23 | 80.00 KB | **ENABLED** | Master akun autentikasi pengguna (PPL, Pengurus, Anggota) |
| 3 | `anggota` | 5 | 24.00 KB | **ENABLED** | Profil keanggotaan petani |
| 4 | `komoditas` | 5 | 40.00 KB | **ENABLED** | Master komoditas pertanian |
| 5 | `personal_access_tokens` | 10 | 80.00 KB | **ENABLED** | Penyimpanan token autentikasi mobile (Laravel Sanctum) |
| 6 | `sessions` | 6 | 104.00 KB | **ENABLED** | Session auth web Laravel |
| 7 | `artikel` | 9 | 80.00 KB | **ENABLED** | Konten edukasi artikel |
| 8 | `panduan` | 11 | 80.00 KB | **ENABLED** | Konten edukasi panduan budidaya |
| 9 | `tips` | 9 | 32.00 KB | **ENABLED** | Konten edukasi tips pertanian |
| 10 | `migrations` | 3 | 24.00 KB | **ENABLED** | Riwayat batch migrasi database Laravel (29 migrasi) |

> [!NOTE]
> **Status Tabel Lahan & Notifikasi:**  
> Hasil kueri katalog sistem (`information_schema.tables` dan `information_schema.columns`) membuktikan bahwa **belum ada tabel maupun kolom yang bernama `lahan`, `riwayat_verifikasi_lahan`, atau `notifikasi`** di skema database saat ini. Database dalam kondisi bersih (*clean state*) untuk menerima implementasi fitur baru.

---

## 2. Struktur Kolom dan Tipe Data Aktual

Berikut adalah rincian kolom, tipe data, *nullability*, dan nilai *default* dari 5 tabel inti yang bersinggungan langsung dengan fitur lahan:

### 2.1 Tabel `public.kelompok_tani`
| Nama Kolom | Tipe Data PostgreSQL | Nullable | Default | Keterangan |
|---|---|:---:|---|---|
| `id_kelompok` | `character varying(20)` | NO | - | **Primary Key** (Format: `PokTan-XXXX`) |
| `nama_kelompok` | `character varying(150)` | NO | - | Nama resmi kelompok tani (Unique) |
| `alamat` | `text` | NO | - | Alamat sekretariat / wilayah kelompok |
| `status` | `character varying(20)` | NO | - | `'Aktif'` / `'Tidak Aktif'` |
| `created_at` | `timestamp(0) without time zone` | YES | NULL | Waktu registrasi kelompok |
| `updated_at` | `timestamp(0) without time zone` | YES | NULL | Waktu pembaruan profil |

### 2.2 Tabel `public.pengguna`
| Nama Kolom | Tipe Data PostgreSQL | Nullable | Default | Keterangan |
|---|---|:---:|---|---|
| `id_pengguna` | `character varying(20)` | NO | - | **Primary Key** (Format: `PGR-XXXX`) |
| `nama` | `character varying(100)` | NO | - | Nama lengkap pengguna |
| `nik` | `character varying(16)` | YES | NULL | Nomor Induk Kependudukan (16 digit, Unique) |
| `username` | `character varying(50)` | NO | - | Username login (Unique) |
| `password` | `character varying(255)` | NO | - | Bcrypt hashed password |
| `email` | `character varying(255)` | YES | NULL | Alamat email (Unique) |
| `no_telepon` | `character varying(20)` | YES | NULL | Nomor kontak |
| `alamat` | `text` | YES | NULL | Alamat domisili |
| `foto_profil` | `character varying(255)` | YES | NULL | URL Cloudinary foto profil |
| `role` | `character varying(20)` | NO | - | Role akun: `'PPL'`, `'Pengurus'`, `'Anggota'` |
| `jabatan` | `character varying(30)` | YES | NULL | Jabatan pengurus: `'Ketua'`, `'Sekretaris'`, `'Bendahara'`, dll |
| `id_kelompok` | `character varying(20)` | YES | NULL | **Foreign Key** ke `kelompok_tani(id_kelompok)` |
| `status` | `character varying(20)` | NO | - | Status akun: `'Aktif'`, `'Tidak Aktif'` |
| `remember_token` | `character varying(100)` | YES | NULL | Token sesi remember me |
| `provinsi_id` | `character varying(10)` | YES | NULL | Kode BPS provinsi |
| `kabupaten_id` | `character varying(10)` | YES | NULL | Kode BPS kabupaten/kota |
| `kecamatan_id` | `character varying(10)` | YES | NULL | Kode BPS kecamatan |
| `desa_id` | `character varying(20)` | YES | NULL | Kode BPS desa/kelurahan |
| `lokasi_nama` | `character varying(200)` | YES | NULL | Nama teks wilayah lokasi |
| `latitude` | `numeric(10,7)` | YES | NULL | Titik lintang GPS domisili |
| `longitude` | `numeric(10,7)` | YES | NULL | Titik bujur GPS domisili |
| `created_at` | `timestamp(0) without time zone` | YES | NULL | - |
| `updated_at` | `timestamp(0) without time zone` | YES | NULL | - |

### 2.3 Tabel `public.anggota`
| Nama Kolom | Tipe Data PostgreSQL | Nullable | Default | Keterangan |
|---|---|:---:|---|---|
| `id_anggota` | `character varying(8)` | NO | - | **Primary Key** (Format: `AGT-XXXX`, panjang 8 char) |
| `id_pengguna` | `character varying(20)` | NO | - | **Foreign Key** ke `pengguna(id_pengguna)` |
| `status_keanggotaan` | `character varying(20)` | NO | - | `'Aktif'` / `'Tidak Aktif'` |
| `created_at` | `timestamp(0) without time zone` | YES | NULL | - |
| `updated_at` | `timestamp(0) without time zone` | YES | NULL | - |

### 2.4 Tabel `public.komoditas`
| Nama Kolom | Tipe Data PostgreSQL | Nullable | Default | Keterangan |
|---|---|:---:|---|---|
| `id_komoditas` | `character varying(20)` | NO | - | **Primary Key** (Format aktual: `kmdt-XXXX`) |
| `nama_komoditas` | `character varying(100)` | NO | - | Nama komoditas (Unique, e.g. "Padi Sawah", "Jagung") |
| `kategori` | `character varying(100)` | NO | - | Kategori (Tanaman Pangan, Hortikultura, dll) |
| `created_at` | `timestamp(0) without time zone` | YES | NULL | - |
| `updated_at` | `timestamp(0) without time zone` | YES | NULL | - |

### 2.5 Tabel `public.personal_access_tokens`
| Nama Kolom | Tipe Data PostgreSQL | Nullable | Default | Keterangan |
|---|---|:---:|---|---|
| `id` | `bigint` | NO | `nextval(...)` | **Primary Key** auto-increment |
| `tokenable_type` | `character varying(255)` | NO | - | Model class (`App\Models\Pengguna`) |
| `tokenable_id` | `character varying(50)` | NO | - | ID user (menyimpan `id_pengguna`) |
| `name` | `text` | NO | - | Nama token (misal: "mobile_auth_token") |
| `token` | `character varying(64)` | NO | - | SHA-256 hashed token (Unique) |
| `abilities` | `text` | YES | NULL | Izin akses token |
| `last_used_at` | `timestamp(0) without time zone` | YES | NULL | - |
| `expires_at` | `timestamp(0) without time zone` | YES | NULL | - |
| `created_at` | `timestamp(0) without time zone` | YES | NULL | - |
| `updated_at` | `timestamp(0) without time zone` | YES | NULL | - |

---

## 3. Relasi Primary Key dan Foreign Key Aktual

Berdasarkan pembacaan `information_schema.referential_constraints`, relasi foreign key yang aktif pada database adalah sebagai berikut:

```
kelompok_tani (id_kelompok)
      ▲
      │ ON DELETE SET NULL / NO ACTION
pengguna (id_kelompok) 
      ▲
      │ ON DELETE CASCADE / NO ACTION
anggota (id_pengguna)

komoditas (id_komoditas)
      ▲
      ├─── ON DELETE SET NULL ── tips (komoditas_id)
      ├─── ON DELETE SET NULL ── panduan (komoditas_id)
      └─── ON DELETE SET NULL ── artikel (komoditas_id)
```

### Karakteristik Penting Relasi Terdaftar:
1. **Konvensi Nama Kolom Relasi Komoditas:**
   Pada tabel konten (`tips`, `panduan`, `artikel`), kolom referensi ke `komoditas` dinamakan `komoditas_id`, bukan `id_komoditas`. Namun tipe datanya tetap merujuk ke `komoditas.id_komoditas` (`character varying(20)`).
2. **Penghapusan Pengguna vs Anggota:**
   Jika akun `pengguna` dihapus, data di tabel `anggota` terhapus secara beruntun (`ON DELETE CASCADE`). Namun jika `kelompok_tani` dihapus, `pengguna.id_kelompok` di-set menjadi `NULL` (`ON DELETE SET NULL`).

---

## 4. Constraint dan Indeks yang Tersedia

Daftar indeks btree dan unique constraint yang terverifikasi aktif pada tabel terkait:

1. **`kelompok_tani`:**
   - `kelompok_tani_pkey` (PRIMARY KEY: `id_kelompok`)
   - `kelompok_tani_nama_kelompok_unique` (UNIQUE: `nama_kelompok`)
2. **`pengguna`:**
   - `pengguna_pkey` (PRIMARY KEY: `id_pengguna`)
   - `pengguna_username_unique` (UNIQUE: `username`)
   - `pengguna_nik_unique` (UNIQUE: `nik`)
   - `pengguna_email_unique` (UNIQUE: `email`)
   - *Catatan:* Belum ada indeks komposit untuk query pencarian pengurus per kelompok `(id_kelompok, role, jabatan, status)`.
3. **`anggota`:**
   - `anggota_pkey` (PRIMARY KEY: `id_anggota`)
   - *Catatan:* Kolom `id_pengguna` pada tabel `anggota` **belum memiliki UNIQUE constraint maupun explicit INDEX**, meskipun relasi modelnya bersifat *One-to-One* (`hasOne`).
4. **`komoditas`:**
   - `komoditas_pkey` (PRIMARY KEY: `id_komoditas`)
   - `komoditas_nama_komoditas_unique` (UNIQUE: `nama_komoditas`)
5. **`personal_access_tokens`:**
   - `personal_access_tokens_pkey` (PRIMARY KEY: `id`)
   - `personal_access_tokens_token_unique` (UNIQUE: `token`)
   - `personal_access_tokens_tokenable_type_tokenable_id_index` (COMPOUND: `tokenable_type, tokenable_id`)
   - `personal_access_tokens_expires_at_index` (INDEX: `expires_at`)

---

## 5. Validasi Struktur Pengguna dan Identifikasi Ketua Kelompok

### 5.1 Distribusi Nilai Aktual Kolom
Pemeriksaan kueri agregasi aman (*read-only aggregate*) pada seluruh record tabel `pengguna` menghasilkan distribusi data riil sebagai berikut:

- **Nilai Aktual Kolom `role`:**
  - `'PPL'` (1 akun)
  - `'Pengurus'` (5 akun)
  - `'Anggota'` (1 akun)
- **Nilai Aktual Kolom `jabatan`:**
  - `'Ketua'` (3 akun)
  - `'Sekretaris'` (1 akun)
  - `'Bendahara'` (1 akun)
  - `'PPL'` (1 akun)
  - `NULL` (1 akun - akun peran Anggota)
- **Nilai Aktual Kolom `status`:**
  - `'Aktif'` (100% dari 7 akun terdaftar)
- **Nilai Aktual Kolom `status_keanggotaan` pada tabel `anggota`:**
  - `'Aktif'` (1 akun terdaftar)

### 5.2 Validasi Cara Mengidentifikasi Ketua Kelompok
Audit membuktikan bahwa Ketua Kelompok Tani **dapat diidentifikasi secara pasti** menggunakan kombinasi kriteria:
```sql
SELECT id_pengguna, nama, no_telepon 
FROM public.pengguna 
WHERE id_kelompok = :target_id_kelompok 
  AND role = 'Pengurus' 
  AND jabatan = 'Ketua' 
  AND status = 'Aktif'
LIMIT 1;
```

### 5.3 Temuan Kritis Kondisi Data Kelompok Saat Ini:
Dari total 6 kelompok tani yang tersimpan di tabel `kelompok_tani`:
1. `PokTan-5575` (Kelompok Tani Maju Bersama) $\rightarrow$ Memiliki Ketua aktif (`PGR-6856`)
2. `PokTan-7377` (Kelompok Tani Subur Makmur) $\rightarrow$ Memiliki Ketua aktif (`PGR-5079`)
3. `PokTan-2906` (Kelompok Tani Karya Bersama) $\rightarrow$ Memiliki Ketua aktif (`PGR-8046`)
4. `PokTan-1859` (Kelompok Tani Sri Rejeki) $\rightarrow$ **BELUM memiliki akun Ketua yang terdaftar**
5. `PokTan-7406` (Kelompok Tani Tani Mandiri) $\rightarrow$ **BELUM memiliki akun Ketua yang terdaftar**
6. `PokTan-1474` (Kelompok Tani Mekar Wangi) $\rightarrow$ **Status kelompok 'Tidak Aktif' & belum ada Ketua**

> [!IMPORTANT]
> **Implikasi Arsitektural untuk Notifikasi:**  
> Fakta bahwa 50% kelompok tani saat ini belum memiliki pengurus dengan jabatan `'Ketua'` membuktikan bahwa **mekanisme fallback dalam `NotifikasiService` adalah kebutuhan mutlak**, bukan sekadar skenario teoritis. Jika seorang anggota di `PokTan-1859` mendaftarkan lahan baru, sistem tidak boleh crash atau gagal menyimpan transaksi hanya karena query Ketua menghasilkan `null`.

---

## 6. Perbandingan Database Aktual dengan Technical Design v3.0

| Parameter / Entitas | Rancangan di Technical Design v3.0 | Kondisi Database Aktual | Status Keselarasan | Rekomendasi Penyesuaian |
|---|---|---|:---:|---|
| **Panjang Kolom `id_kelompok`** | `varchar(50)` | `character varying(20)` pada `kelompok_tani` & `pengguna` | ⚠️ **Perlu Penyesuaian** | Samakan menjadi `varchar(20)` pada tabel `lahan.id_kelompok` |
| **Panjang Kolom `id_pengguna`** | `varchar(50)` | `character varying(20)` pada `pengguna` | ⚠️ **Perlu Penyesuaian** | Samakan menjadi `varchar(20)` pada foreign key lahan & notifikasi |
| **Tipe Data `id_komoditas`** | `varchar(20)` | `character varying(20)` pada `komoditas` | ✅ **Sesuai Sempurna** | Gunakan `varchar(20)` |
| **Konvensi Kolom FK Komoditas** | `id_komoditas` | `komoditas_id` (pada tips/panduan/artikel) | ℹ️ **Catatan Desain** | Gunakan `id_komoditas` atau `komoditas_id` (pilih konsistensi) |
| **Tipe Data Koordinat GPS** | `decimal(10,7)` | `numeric(10,7)` pada `pengguna` | ✅ **Sesuai Sempurna** | PostgreSQL memperlakukan `decimal` sebagai alias `numeric` |
| **Tipe Data Wilayah BPS** | `varchar(10)` & `varchar(20)` | `varchar(10)` & `varchar(20)` pada `pengguna` | ✅ **Sesuai Sempurna** | Cocok dengan kolom wilayah di tabel pengguna |
| **Satuan Luas Lahan** | Baku Hektare (`ha`) `decimal(10,4)` | Belum ada tabel lahan | ✅ **Sesuai Rencana** | Konversi m² di aplikasi, simpan desimal ha |
| **Penerima Notifikasi** | Khusus Ketua Kelompok Aktif | Kolom `role`, `jabatan`, `status`, `id_kelompok` tersedia | ✅ **Terverifikasi Siap** | Kolom penentu Ketua tersedia dan aktif |
| **Token Auth Mobile** | Sanctum Bearer Token | `personal_access_tokens` tersedia (batch 5) | ✅ **Terverifikasi Siap** | Token mobile siap menampung user Anggota |

---

## 7. Daftar Ketidaksesuaian (*Mismatches*)

1. **Perbedaan Alokasi Panjang Foreign Key ID Pengguna dan Kelompok:**  
   Pada dokumen Technical Design sebelumnya, kolom `id_pengguna` dan `id_kelompok` dirancang dengan panjang `varchar(50)`. Padahal primary key aktual pada tabel `pengguna` dan `kelompok_tani` adalah `character varying(20)`. Meskipun PostgreSQL mengizinkan tipe foreign key yang lebih panjang, penetapan `varchar(20)` jauh lebih efisien, konsisten, dan mencegah ketidakcocokan tipe data relasional.
2. **Ketiadaan Indeks Pencarian Pengurus Kelompok:**  
   Tabel `pengguna` belum memiliki indeks majemuk pada `(id_kelompok, role, jabatan, status)`. Query pencarian Ketua Kelompok untuk pengiriman notifikasi akan memicu *sequential scan* pada tabel `pengguna` jika indeks ini tidak ditambahkan.
3. **Format ID Komoditas Riil Menggunakan Awalan Kecil:**  
   Dokumen desain lama mencontohkan ID komoditas berformat kapital `KMD-XXXX`. Faktanya, data riil pada tabel `komoditas` menggunakan format huruf kecil: `kmdt-7318`, `kmdt-8528`, dll. Lapisan API dan Flutter harus mengantisipasi format string case-sensitive ini.

---

## 8. Rekomendasi Migration yang Diperlukan

Berdasarkan hasil audit, migration yang perlu dipersiapkan untuk implementasi fitur lahan adalah:

### Migration 1: Tabel `lahan`
```php
Schema::create('lahan', function (Blueprint $table) {
    $table->string('id_lahan', 30)->primary(); // Format: LHN-YYYY-XXXX (13 char)
    $table->string('id_pengguna', 20);          // FK pengguna(id_pengguna)
    $table->string('id_kelompok', 20);          // FK kelompok_tani(id_kelompok)
    $table->string('nama_lahan', 150);
    $table->decimal('luas', 10, 4);             // Baku Hektare (ha)
    $table->string('provinsi_id', 10)->nullable();
    $table->string('kabupaten_id', 10)->nullable();
    $table->string('kecamatan_id', 10)->nullable();
    $table->string('desa_id', 20)->nullable();
    $table->text('alamat_lahan');
    $table->decimal('latitude', 10, 7)->nullable();
    $table->decimal('longitude', 10, 7)->nullable();
    $table->string('id_komoditas', 20)->nullable(); // FK komoditas(id_komoditas)
    $table->string('musim_tanam', 50)->nullable();
    $table->string('foto_lahan', 255)->nullable();
    $table->text('keterangan')->nullable();
    $table->string('status_operasional', 30)->default('persiapan');
    $table->string('status_verifikasi', 30)->default('menunggu_verifikasi')->index();
    $table->text('catatan_verifikasi')->nullable();
    $table->timestamp('tanggal_verifikasi')->nullable();
    $table->string('verifikator_id', 20)->nullable(); // FK pengguna(id_pengguna)
    $table->integer('nomor_revisi')->default(1);
    $table->timestamps();
    $table->softDeletes();

    $table->foreign('id_pengguna')->references('id_pengguna')->on('pengguna')->onDelete('restrict');
    $table->foreign('id_kelompok')->references('id_kelompok')->on('kelompok_tani')->onDelete('restrict');
    $table->foreign('id_komoditas')->references('id_komoditas')->on('komoditas')->onDelete('set null');
    $table->foreign('verifikator_id')->references('id_pengguna')->on('pengguna')->onDelete('set null');
});

// Enable RLS di Supabase
if (DB::connection()->getDriverName() === 'pgsql') {
    DB::statement('ALTER TABLE lahan ENABLE ROW LEVEL SECURITY');
}
```

### Migration 2: Tabel `riwayat_verifikasi_lahan`
```php
Schema::create('riwayat_verifikasi_lahan', function (Blueprint $table) {
    $table->bigIncrements('id_riwayat');
    $table->string('id_lahan', 30);            // FK lahan(id_lahan)
    $table->string('id_pengguna', 20);         // FK pengguna(id_pengguna)
    $table->string('aksi', 40);                // pengajuan_awal, minta_perbaikan, pengajuan_ulang, disetujui, pencabutan_verifikasi
    $table->string('status_sebelumnya', 30)->nullable();
    $table->string('status_sesudahnya', 30);
    $table->text('catatan')->nullable();       // Alasan instruksi perbaikan/pencabutan
    $table->integer('nomor_revisi')->default(1);
    $table->jsonb('snapshot_data')->nullable(); // JSON snapshot atribut lahan
    $table->timestamp('created_at')->useCurrent();

    $table->foreign('id_lahan')->references('id_lahan')->on('lahan')->onDelete('no action');
    $table->foreign('id_pengguna')->references('id_pengguna')->on('pengguna')->onDelete('restrict');
});

if (DB::connection()->getDriverName() === 'pgsql') {
    DB::statement('ALTER TABLE riwayat_verifikasi_lahan ENABLE ROW LEVEL SECURITY');
}
```

### Migration 3: Tabel `notifikasi`
```php
Schema::create('notifikasi', function (Blueprint $table) {
    $table->string('id_notifikasi', 30)->primary(); // Format: NTF-YYYYMMDD-XXXX (17 char)
    $table->string('id_pengirim', 20)->nullable();
    $table->string('id_penerima', 20)->index();
    $table->string('id_lahan', 30)->nullable()->index();
    $table->unsignedBigInteger('id_riwayat')->nullable();
    $table->string('jenis_notifikasi', 50);
    $table->string('judul', 150);
    $table->text('pesan');
    $table->jsonb('data_payload')->nullable();
    $table->boolean('sudah_dibaca')->default(false)->index();
    $table->timestamp('dibaca_pada')->nullable();
    $table->timestamps();

    $table->foreign('id_pengirim')->references('id_pengguna')->on('pengguna')->onDelete('set null');
    $table->foreign('id_penerima')->references('id_pengguna')->on('pengguna')->onDelete('cascade');
    $table->foreign('id_lahan')->references('id_lahan')->on('lahan')->onDelete('set null');
    $table->foreign('id_riwayat')->references('id_riwayat')->on('riwayat_verifikasi_lahan')->onDelete('set null');
});

if (DB::connection()->getDriverName() === 'pgsql') {
    DB::statement('ALTER TABLE notifikasi ENABLE ROW LEVEL SECURITY');
}
```

### Migration 4 (Optimasi Opsional): Indeks Majemuk Pencarian Ketua
```php
Schema::table('pengguna', function (Blueprint $table) {
    $table->index(['id_kelompok', 'role', 'jabatan', 'status'], 'idx_pengguna_ketua_lookup');
});
```

---

## 9. Analisis Risiko Integritas Data

1. **Risiko Transaksi Gagal Karena Ketua Kelompok Belum Terdaftar:**  
   - *Kondisi Riil:* Data aktual membuktikan ada 3 kelompok tani yang belum memiliki akun Ketua.
   - *Mitigasi:* Pemanggilan notifikasi **tidak boleh berada di dalam satu transaksi database yang memblokir penyimpanan lahan jika Ketua tidak ditemukan**. `NotifikasiService` harus menangani kasus `null` dengan melakukan fallback ke pengurus lain atau sekadar mencatat log sistem tanpa me-rollback transaksi pendaftaran lahan anggota.
2. **Risiko Format Satuan Luas (Inkonsistensi Agregasi):**  
   - *Kondisi Riil:* Tanpa konversi baku ke `ha` sebelum persistensi, data lahan bisa tercampur antara nilai hektare (misal: 0.8) dan meter persegi (misal: 8000). Hal ini akan menghancurkan akurasi statistik kelompok tani.
   - *Mitigasi:* Form Request Laravel (`StoreLahanRequest` & `UpdateLahanRequest`) wajib menerima parameter luas dan satuan dari mobile, lalu mengonversi nilai menjadi desimal baku hektare sebelum disimpan ke database.
3. **Risiko Penghapusan Berjenjang yang Menghilangkan Bukti Riwayat:**  
   - *Kondisi Riil:* Jika foreign key riwayat menggunakan `CASCADE`, penghapusan data lahan akan memusnahkan bukti penolakan/persetujuan yang sah.
   - *Mitigasi:* Konfigurasi `ON DELETE NO ACTION` pada foreign key `riwayat_verifikasi_lahan.id_lahan` dan penerapan `SoftDeletes` pada tabel `lahan` menjamin integritas data audit trail 100% terjaga.

---

## 10. Kesimpulan Kesiapan Database

Database PostgreSQL Supabase PawonTani **SANGAT SIAP (READY WITH MINOR ADJUSTMENTS)** untuk mengadopsi implementasi Fitur Lahan & Sistem Notifikasi, dengan catatan penyesuaian:

1. **Fondasi Autentikasi & Relasi Siap:**  
   Tabel `pengguna`, `kelompok_tani`, `anggota`, `komoditas`, dan `personal_access_tokens` telah aktif, memiliki integritas constraint yang valid, dan siap menjadi jangkar foreign key.
2. **Penyesuaian Panjang Kolom ID:**  
   Gunakan panjang `varchar(20)` untuk seluruh foreign key yang mengacu ke `pengguna.id_pengguna` dan `kelompok_tani.id_kelompok`.
3. **Penerapan RLS:**  
   Pastikan setiap migration tabel baru menyertakan perintah pengaktifan Row Level Security (`ALTER TABLE ... ENABLE ROW LEVEL SECURITY`) sesuai standar Supabase yang diterapkan pada tabel-tabel sebelumnya.
4. **Tidak Ada Konflik Skema:**  
   Tidak ditemukan adanya tabel atau kolom lama yang tumpang tindih (*conflict-free*).

Laporan audit ini menjadi dasar penyempurnaan migrasi sebelum proses pengkodean (*implementation phase*) dimulai.
