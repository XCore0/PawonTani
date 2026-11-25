# PawonTani

PawonTani adalah aplikasi informasi dan administrasi kelompok tani berbasis Laravel. Aplikasi ini menyediakan pengelolaan data kelompok tani, pengurus, anggota, cuaca pertanian, harga komoditas, dan edukasi pertanian untuk mendukung digitalisasi pertanian Indonesia.

## Teknologi

- PHP 8.3+
- Laravel 13
- PostgreSQL (Supabase)
- Node.js 20+ dan npm
- Vite dan Tailwind CSS 4
- Open-Meteo API (cuaca & geocoding)
- emsifa/wilayah-id (data wilayah Indonesia)

## Persyaratan Sistem

Pastikan perangkat sudah memiliki PHP 8.3+, Composer, Node.js 20.19+ atau 22.12+, npm, Git, dan database PostgreSQL, MySQL, MariaDB, atau SQLite. Pada Windows, Laragon dapat digunakan sebagai web server lokal.

## Instalasi

Clone repository dan masuk ke folder project:

```bash
git clone https://github.com/XCore0/PawonTani.git
cd PawonTani
```

Install dependency PHP dan frontend:

```bash
composer install
npm install
```

Buat file environment:

```bash
cp .env.example .env
```

Pada Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

## Konfigurasi Database

### SQLite

Buat file database SQLite:

```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
```

Pastikan `.env` berisi:

```env
DB_CONNECTION=sqlite
```

### PostgreSQL (Supabase)

> **Penting:** Jangan pernah commit file `.env` ke repository. File ini sudah ada di `.gitignore`.

```env
DB_CONNECTION=pgsql
DB_HOST=<supabase_host>.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=<your_supabase_password>
```

### MySQL atau MariaDB

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pawontani
DB_USERNAME=root
DB_PASSWORD=
```

Sesuaikan nilai tersebut dengan konfigurasi database lokal.

## Migrasi dan Data Awal

Jalankan migrasi:

```bash
php artisan migrate
```

Seeder kelompok tani dan pengurus:

```bash
php artisan db:seed --class=KelompokTaniSeeder
php artisan db:seed --class=PenggunaSeeder
```

Untuk menghapus database dan mengisi ulang data:

```bash
php artisan migrate:fresh
php artisan db:seed --class=KelompokTaniSeeder
php artisan db:seed --class=PenggunaSeeder
```

Data contoh pengurus menggunakan password `password123`. Gunakan hanya untuk pengembangan lokal dan jangan gunakan password tersebut di production.

## Menjalankan Aplikasi

Jalankan server Laravel:

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000` di browser.

Untuk menjalankan Vite dalam mode development, buka terminal kedua:

```bash
npm run dev
```

Untuk membuat asset production:

```bash
npm run build
```

## Menjalankan dengan Laragon

1. Letakkan project di `C:\laragon\www\PawonTani`.
2. Jalankan Apache/Nginx dan database dari Laragon.
3. Buat `.env` dari `.env.example` dan konfigurasi database.
4. Jalankan `composer install` dan `npm install`.
5. Jalankan migrasi dan seeder.
6. Buka `http://pawontani.test` jika auto virtual host Laragon aktif, atau gunakan `php artisan serve`.

## Struktur File

```
PawonTani/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/          # Controller admin (PPL)
│   │   │   ├── ArtikelController.php
│   │   │   ├── KelompokTaniController.php
│   │   │   ├── PanduanController.php
│   │   │   ├── PengurusController.php
│   │   │   └── TipsController.php
│   │   ├── Pengurus/       # Controller pengurus
│   │   │   └── AnggotaController.php
│   │   └── AuthController.php
│   ├── Models/
│   │   ├── KelompokTani.php
│   │   ├── Pengguna.php
│   │   └── User.php
│   ├── Services/           # Service layer
│   │   ├── CommodityService.php
│   │   └── HarvestPredictionService.php
│   └── Data/
│       └── regions.php     # Data wilayah Indonesia
├── bootstrap/
├── config/                 # Konfigurasi Laravel
├── database/
│   ├── migrations/         # Migrasi database
│   ├── seeders/            # Data awal
│   └── factories/
├── public/
│   ├── build/              # Asset production (Vite)
│   ├── css/
│   └── images/
├── resources/
│   ├── views/
│   │   ├── Admin/          # View admin (PPL)
│   │   │   ├── Content/
│   │   │   └── Layout/
│   │   ├── Pengurus/       # View pengurus
│   │   │   ├── Content/
│   │   │   └── Layout/
│   │   ├── Auth/
│   │   │   └── Login.blade.php
│   │   └── home.blade.php
│   ├── css/
│   ── js/
├── routes/
│   └── web.php             # Semua route aplikasi
├── storage/
── tests/
├── .env.example            # Template environment (aman di-commit)
── .gitignore              # .env sudah di-ignore
├── composer.json
├── package.json
├── vite.config.js
└── README.md
```

## Struktur Route

### Publik
| URL | Deskripsi |
|-----|-----------|
| `/` | Beranda publik |
| `/login` | Halaman login |

### Pengurus (Kelompok Tani)
| URL | Deskripsi |
|-----|-----------|
| `/dashboard` | Dashboard pengurus |
| `/anggota` | Kelola anggota kelompok tani |
| `/lahan` | Kelola lahan pertanian |
| `/monitoring` | Monitoring pertanian |
| `/panen` | Data panen |
| `/penjualan` | Transaksi penjualan |
| `/laporan` | Laporan aktivitas |
| `/informasi` | Cuaca, harga komoditas & prediksi |
| `/edukasi` | Materi edukasi pertanian |
| `/notifikasi` | Notifikasi |
| `/profil` | Profil pengguna |

### Admin (PPL)
| URL | Deskripsi |
|-----|-----------|
| `/admin/dashboard` | Dashboard admin |
| `/admin/pengurus` | Kelola pengurus |
| `/admin/kelompok` | Kelola kelompok tani |
| `/admin/verifikasi-lapangan` | Verifikasi lapangan |
| `/admin/aktivitas` | Aktivitas pertanian |
| `/admin/edukasi/tips` | Kelola tips pertanian |
| `/admin/edukasi/artikel` | Kelola artikel |
| `/admin/edukasi/panduan` | Kelola panduan |
| `/admin/notifikasi` | Notifikasi admin |
| `/admin/profil` | Profil admin |

### API
| URL | Deskripsi |
|-----|-----------|
| `/api/lokasi/kabupaten` | Daftar kabupaten per provinsi |
| `/api/lokasi/kecamatan` | Daftar kecamatan per kabupaten |
| `/api/lokasi/cuaca` | Data cuaca Open-Meteo |
| `/api/lokasi/simpan` | Simpan lokasi pengguna |
| `/ajax/check-username` | Cek ketersediaan username |

## Fitur Utama

### Cuaca Pertanian
- Prediksi cuaca 7 hari dari Open-Meteo API
- Pemilihan lokasi bertahap: Provinsi → Kabupaten → Kecamatan
- Auto-geocode koordinat dari nama lokasi
- Rekomendasi pertanian berdasarkan kondisi cuaca
- Tampilan responsive (mobile scroll, desktop grid)

### Pengelolaan Data
- CRUD anggota kelompok tani
- CRUD pengurus dan kelompok tani (admin)
- Data wilayah Indonesia (34 provinsi, 514 kab/kota, 7,215 kecamatan)

### Edukasi
- Tips pertanian
- Artikel pertanian
- Panduan pertanian

Navigasi antarhalaman menggunakan pemuatan content secara dinamis agar sidebar dan header tidak perlu dimuat ulang setiap berpindah menu.

## Pengujian

Jalankan test Laravel:

```bash
php artisan test
```

Atau gunakan script Composer:

```bash
composer test
```

## Keamanan

File `.env` **sudah ada di `.gitignore`** dan tidak akan ter-commit ke repository. File ini berisi informasi rahasia seperti:

- Kredensial database (host, username, password)
- Application key (`APP_KEY`)
- Token dan secret layanan eksternal

Jangan pernah:
- Commit atau upload `.env` ke repository
- Share credential, token, dan secret
- Commit private key dan certificate
- Commit file upload pengguna
- Commit log aplikasi

Gunakan `.env.example` sebagai template konfigurasi dan isi nilai rahasia hanya pada environment lokal atau server deployment.

## Dokumentasi Fitur Siklus Tanam (November 2026)

Pembaruan besar ini menghadirkan sistem pengelolaan dan digitalisasi **Siklus Tanam** secara terintegrasi antara Web Backend Laravel dan Flutter Mobile.

### 1. Arsitektur & Model Data
- **`App\Models\SiklusTanam` (`siklus_tanam`):**
  - Mengelola siklus berjalan (`isBerjalan()`) dan riwayat siklus tanam suatu petak lahan.
  - Atribut utama: `id_siklus` (SKL-YYYYMMDD-XXXX), `id_lahan`, `id_pengguna`, `id_kelompok`, `id_komoditas`, `komoditas_nama`, `varietas`, `musim_tanam`, `tanggal_mulai`, `estimasi_panen`, `status` (`aktif`, `menunggu_selesai`, `menunggu_gagal`, `selesai`, `gagal`, `dibatalkan`).
- **`App\Models\RiwayatPengakhiranSiklus` (`riwayat_pengakhiran_siklus`):**
  - Audit trail komprehensif saat pengakhiran diajukan dan diputuskan (snapshot realisasi panen, alasan, foto bukti, verifikator, catatan verifikasi, tanggal keputusan).
- **Relasi Entitas Terikat:**
  - `Lahan` memiliki `hasOne` `siklusBerjalan` dan `hasMany` `riwayatSiklus`.
  - `AktivitasTanam` dan `Panen` memiliki `belongsTo` `siklusTanam` via foreign key `id_siklus`.
  - Dukungan data lama/legacy (`id_siklus` nullable) tanpa memutus data historis yang sudah ada.

### 2. Status Operasional Lahan & Siklus
Status lahan disinkronkan secara ketat:
- **`aktif`:** Lahan memiliki siklus tanam berjalan.
- **`persiapan`:** Lahan sedang dalam masa olah tanah / persiapan sebelum mulai tanam.
- **`istirahat`:** Pasca pengakhiran siklus (selesai/gagal) lahan masuk masa istirahat sebelum siklus berikutnya.

### 3. Aturan Bisnis (Business Rules)
1. **Satu Siklus Berjalan per Lahan:** Satu petak lahan hanya boleh memiliki tepat 1 siklus tanam aktif atau menunggu keputusan verifikasi dalam satu waktu.
2. **Keterikatan Budidaya:** Pencatatan aktivitas dan panen baru pada mobile secara otomatis mewajibkan binding `id_siklus` siklus berjalan.
3. **Persyaratan Pengakhiran Berhasil (Selesai):** Wajib memiliki minimal 1 catatan panen, dan seluruh catatan panen pada siklus tersebut harus telah berstatus `disetujui` oleh pengurus.
4. **Persyaratan Pengakhiran Gagal:** Tanaman puso/rusak, wajib menyertakan alasan kegagalan dan unggah foto bukti lapangan.
5. **Pembatalan Siklus:** Hanya dapat dilakukan jika siklus belum memiliki catatan aktivitas budidaya maupun catatan panen.

### 4. Alur Verifikasi & Otorisasi
- **Pengajuan oleh Anggota:** Anggota mengajukan pengakhiran selesai atau gagal via Mobile / API. Status siklus berubah menjadi `menunggu_selesai` atau `menunggu_gagal`. Status operasional lahan tetap `aktif` selama masa tunggu.
- **Keputusan oleh Pengurus:** Pengurus memeriksa pengajuan pada halaman Web Kelola Lahan (`/pengurus/lahan/{id}`).
  - Jika **Disetujui:** Status siklus berubah menjadi `selesai` atau `gagal`, dan status operasional lahan berubah menjadi `istirahat`.
  - Jika **Ditolak:** Wajib menyertakan catatan penolakan. Status siklus dipulihkan kembali menjadi `aktif` agar anggota dapat memperbaiki data atau melanjutkan masa tanam.

### 5. Rute & Endpoint API
- `POST /api/v1/siklus-tanam` — Mulai siklus tanam baru.
- `GET /api/v1/siklus-tanam/{id}` — Detail siklus tanam.
- `GET /api/v1/lahan/{id}/siklus/berjalan` — Mengambil siklus yang sedang berjalan pada lahan.
- `GET /api/v1/lahan/{id}/siklus` — Mengambil daftar riwayat siklus tanam lahan.
- `POST /api/v1/siklus-tanam/{id}/ajukan-selesai` — Pengajuan pengakhiran berhasil oleh anggota.
- `POST /api/v1/siklus-tanam/{id}/ajukan-gagal` — Pelaporan kegagalan panen oleh anggota.
- `POST /api/v1/siklus-tanam/{id}/batalkan` — Pembatalan siklus oleh anggota.
- `POST /api/v1/siklus-tanam/{id}/verifikasi` — Verifikasi keputusan oleh Pengurus.
- `GET /api/v1/notifikasi` — Mengambil daftar notifikasi status siklus dan lahan.

---

## Lisensi

Project ini menggunakan lisensi MIT.
