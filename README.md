# PawonTani

PawonTani adalah aplikasi informasi dan administrasi kelompok tani berbasis Laravel. Aplikasi ini menyediakan pengelolaan data kelompok tani dan pengurus, termasuk tambah, lihat, edit, hapus, pencarian, filter, dan status data.

## Teknologi

- PHP 8.3 atau lebih baru
- Laravel 13
- SQLite atau MySQL/MariaDB
- Node.js dan npm
- Vite dan Tailwind CSS

## Persyaratan Sistem

Pastikan perangkat sudah memiliki PHP 8.3+, Composer, Node.js 20.19+ atau 22.12+, npm, Git, dan database SQLite, MySQL, atau MariaDB. Pada Windows, Laragon dapat digunakan sebagai web server lokal.

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

### MySQL atau MariaDB

Buat database baru, kemudian sesuaikan `.env`:

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

Seeder kelompok tani dan pengurus dijalankan terpisah:

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

## Struktur Fitur

- Beranda publik: `/`
- Halaman login: `/login`
- Dashboard admin: `/admin/dashboard`
- Kelompok tani: `/admin/kelompok`
- Pengurus: `/admin/pengurus`

Navigasi antarhalaman admin menggunakan pemuatan content secara dinamis agar sidebar dan header tidak perlu dimuat ulang setiap berpindah menu.

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

Jangan commit atau upload file berikut ke repository:

- `.env`
- credential, token, dan secret
- private key dan certificate
- file upload pengguna
- log aplikasi

Gunakan `.env.example` sebagai template konfigurasi dan isi nilai rahasia hanya pada environment lokal atau server deployment.

## Lisensi

Project ini menggunakan lisensi MIT.
