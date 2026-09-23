# PawonTani — Design Document

> Platform Ekosistem Pertanian Digital
> Framework: Laravel 12 + Tailwind CSS 4 + Vite

---

## 1. Gambaran Umum

PawonTani adalah platform digital untuk mengelola ekosistem pertanian, khususnya kelompok tani. Platform ini memiliki dua role utama:

| Role | Deskripsi | URL Prefix |
|------|-----------|------------|
| **Admin** | PPL / Administrator yang mengelola seluruh data kelompok tani dan pengurus | `/admin` |
| **Pengurus** | Ketua/Sekretaris kelompok tani yang mengelola anggota dan aktivitas kelompok | `/pengurus` |

---

## 2. Arsitektur

```
PawonTani/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/
│   │   │   ├── KelompokTaniController.php   # CRUD Kelompok Tani
│   │   │   └── PengurusController.php        # CRUD Pengurus
│   │   └── Controller.php
│   ├── Models/
│   │   ├── KelompokTani.php                  # Model kelompok tani
│   │   ├── Pengguna.php                      # Model user (Authenticatable)
│   │   └── User.php
│   └── Providers/
├── database/migrations/
│   ├── ..._create_kelompok_tani_table.php
│   ├── ..._create_pengguna_table.php
│   ├── ..._add_jabatan_and_id_kelompok_to_pengguna_table.php
│   ├── ..._enable_rls_on_public_tables.php
│   └── ..._create_anggota_table.php
├── resources/views/
│   ├── Admin/
│   │   ├── Content/          # Halaman konten Admin
│   │   └── Layout/           # Layout Admin (_layout, _sidebar, _header)
│   ├── Pengurus/
│   │   ├── Content/          # Halaman konten Pengurus
│   │   └── Layout/           # Layout Pengurus (_layout, _sidebar, _header)
│   ├── Auth/Login.blade.php
│   ├── Error.blade.php       # Halaman "Dalam Pengembangan" (shared)
│   └── home.blade.php
├── routes/web.php
── public/
    ├── images/               # Logo, aset statis
    └── build/                # Output Vite (CSS/JS compiled)
```

---

## 3. Database Schema

### 3.1 Tabel `kelompok_tani`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_kelompok` | varchar(50) PK | Format: `PokTan-XXXX` (auto-generate) |
| `nama_kelompok` | varchar | Nama kelompok tani |
| `alamat` | text | Alamat kelompok |
| `status` | varchar(50) | `Aktif` / `Tidak Aktif` |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### 3.2 Tabel `pengguna`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_pengguna` | varchar(50) PK | Format: `PGR-XXXX` (auto-generate) |
| `nama` | varchar | Nama lengkap |
| `nik` | varchar(50) unique | Nomor Induk Kependudukan |
| `username` | varchar(100) unique | Username login |
| `password` | varchar | Hashed (bcrypt) |
| `email` | varchar unique | Email (nullable) |
| `no_telepon` | varchar(50) | Nomor telepon (nullable) |
| `alamat` | text | Alamat (nullable) |
| `foto_profil` | varchar | Path foto (nullable) |
| `role` | varchar(50) | `PPL` / `Pengurus` / `Anggota` / `Pembeli` |
| `jabatan` | varchar(50) | `Ketua` / `Sekretaris` / `Bendahara` / dll (nullable) |
| `id_kelompok` | varchar(50) FK → kelompok_tani | Kelompok tani asal (nullable) |
| `status` | varchar(50) | `Aktif` / `Tidak Aktif` |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### 3.3 Tabel `anggota`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_anggota` | bigint PK (auto increment) | |
| `id_pengguna` | varchar(50) FK → pengguna | Referensi ke pengguna |
| `status_keanggotaan` | varchar(50) | `Aktif` / `Tidak Aktif` |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### 3.4 Relasi

```
kelompok_tani 1 ──── N pengguna  (via id_kelompok)
pengguna       1 ──── N anggota   (via id_pengguna)
```

---

## 4. Design System

### 4.1 Color Palette

| Token | Hex | Penggunaan |
|-------|-----|------------|
| `pawon-50` | `#F5F8F1` | Background utama |
| `pawon-100` | `#EBF6E0` | Active state, highlight |
| `pawon-600` | `#72BE4A` | Gradient accent |
| `pawon-700` | `#4D9830` | Primary button, active icon |
| `pawon-800` | `#3D8024` | Hover state |
| `pawon-900` | `#1A2D10` | Heading text |

**Warna pendukung:**
- Border: `#E4F0D6`, `#C5DFB0`, `#B8D99B`
- Text secondary: `#9AB880`, `#6B7F5B`, `#4A6030`
- Danger: `red-600`, `red-50`, `red-200`
- Warning: `amber-50`, `amber-600`, `#F4A020`

### 4.2 Typography

- **Font family:** Plus Jakarta Sans (400, 500, 600, 700, 800)
- **Heading:** `font-extrabold`, `tracking-tight`, `text-[#1A2D10]`
- **Body:** `text-sm`, `text-slate-800`
- **Label:** `text-xs`, `font-semibold`, `uppercase`, `tracking-wider`, `text-[#9AB880]`

### 4.3 Border Radius

| Element | Radius |
|---------|--------|
| Card besar (banner, table container) | `rounded-[20px]` – `rounded-[24px]` |
| Card metric | `rounded-[18px]` |
| Button, input, dropdown | `rounded-xl` |
| Icon container | `rounded-2xl` |
| Badge, tag | `rounded-full` |

### 4.4 Spacing & Layout

- **Sidebar width:** 230px (fixed, `lg:translate-x-0`)
- **Header height:** 64px (sticky)
- **Content padding:** `p-4 sm:p-6 lg:p-8`
- **Gap antar section:** `space-y-6`
- **Content margin-left (desktop):** 230px (via CSS `.admin-shell-content` / `.pengurus-shell-content`)

### 4.5 Komponen UI

#### Metric Card
```
┌─────────────────────────────────────────┐
│  [Icon Box]   LABEL (uppercase, muted)  │
│  w-12 h-12    VALUE (2xl, extrabold)    │
│  rounded-2xl  Subtitle (11px, muted)    │
└─────────────────────────────────────────
```

#### Data Table
- Header: `bg-[#F5F8F1]`, uppercase label, `text-[11px]`
- Row hover: `hover:bg-[#F5F8F1]/50`
- Border: `divide-[#E4F0D6]/60`
- Container: `rounded-[20px]`, `border border-[#E4F0D6]`

#### Welcome Banner
- Gradient: `from-[#4D9830] to-[#72BE4A]`
- Decorative blur circle di kanan-bawah
- Badge: `bg-white/20`, `backdrop-blur-xs`

---

## 5. Layout Structure

Setiap role (Admin/Pengurus) memiliki 3 file layout:

```
{Role}/Layout/
├── _layout.blade.php    # Master layout (HTML shell, scripts, AJAX nav)
├── _sidebar.blade.php   # Sidebar navigasi (fixed, 230px)
└── _header.blade.php    # Top header bar (sticky, 64px)
```

### 5.1 Fitur Layout

| Fitur | Deskripsi |
|-------|-----------|
| **AJAX Navigation** | Klik menu sidebar → load konten via fetch (SPA-like), tanpa reload penuh |
| **Loading Indicator** | Overlay "Memuat halaman..." saat navigasi |
| **Mobile Drawer** | Sidebar jadi drawer di mobile (< lg), dengan backdrop |
| **Active State** | Menu aktif di-highlight otomatis via `request()->routeIs()` |
| **Dropdown** | Notifikasi & Profil dropdown di header |
| **Partial Rendering** | Header `X-{Role}-Partial: true` → hanya return konten (untuk AJAX) |

### 5.2 Sidebar Navigation

**Admin:**
- Dashboard
- Kelompok Tani
- Pengurus
- Verifikasi Lapangan *(dalam pengembangan)*
- Edukasi → Tips / Artikel / Panduan
- Aktivitas *(dalam pengembangan)*

**Pengurus:**
- Dashboard
- Kelola Anggota
- Kelola Lahan *(dalam pengembangan)*
- Monitoring Pertanian *(dalam pengembangan)*
- Panen *(dalam pengembangan)*
- Penjualan *(dalam pengembangan)*
- Laporan *(dalam pengembangan)*
- Informasi & Prediksi *(dalam pengembangan)*
- Edukasi *(dalam pengembangan)*
- Notifikasi *(dalam pengembangan)*
- Profil *(dalam pengembangan)*

---

## 6. Routing

### 6.1 Admin Routes (`/admin`)

| Method | URI | Route Name | Handler |
|--------|-----|------------|---------|
| GET | `/admin/dashboard` | `admin.dashboard` | View |
| GET | `/admin/pengurus` | `admin.pengurus` | PengurusController@index |
| POST | `/admin/pengurus` | `admin.pengurus.store` | PengurusController@store |
| PUT | `/admin/pengurus/{id}` | `admin.pengurus.update` | PengurusController@update |
| DELETE | `/admin/pengurus/{id}` | `admin.pengurus.destroy` | PengurusController@destroy |
| GET | `/admin/kelompok` | `admin.kelompok` | KelompokTaniController@index |
| POST | `/admin/kelompok` | `admin.kelompok.store` | KelompokTaniController@store |
| PUT | `/admin/kelompok/{id}` | `admin.kelompok.update` | KelompokTaniController@update |
| DELETE | `/admin/kelompok/{id}` | `admin.kelompok.destroy` | KelompokTaniController@destroy |
| GET | `/admin/verifikasi-lapangan` | `admin.verifikasi` | Error page |
| GET | `/admin/aktivitas` | `admin.aktivitas` | Error page |
| GET | `/admin/edukasi/tips` | `admin.edukasi.tips` | View (Tips) |
| GET | `/admin/edukasi/artikel` | `admin.edukasi.artikel` | View (Artikel) |
| GET | `/admin/edukasi/panduan` | `admin.edukasi.panduan` | View (Panduan) |
| GET | `/admin/notifikasi` | `admin.notifikasi` | View (Notifications) |
| GET | `/admin/profil` | `admin.profil` | Error page |

### 6.2 Pengurus Routes (`/pengurus`)

| Method | URI | Route Name | Handler |
|--------|-----|------------|---------|
| GET | `/pengurus/dashboard` | `pengurus.dashboard` | View |
| GET | `/pengurus/anggota` | `pengurus.anggota` | View |
| GET | `/pengurus/lahan` | `pengurus.lahan` | Error page |
| GET | `/pengurus/monitoring` | `pengurus.monitoring` | Error page |
| GET | `/pengurus/panen` | `pengurus.panen` | Error page |
| GET | `/pengurus/penjualan` | `pengurus.penjualan` | Error page |
| GET | `/pengurus/laporan` | `pengurus.laporan` | Error page |
| GET | `/pengurus/informasi` | `pengurus.informasi` | Error page |
| GET | `/pengurus/edukasi` | `pengurus.edukasi` | Error page |
| GET | `/pengurus/notifikasi` | `pengurus.notifikasi` | Error page |
| GET | `/pengurus/profil` | `pengurus.profil` | Error page |

### 6.3 Error Page (Shared)

Halaman `Error.blade.php` digunakan untuk semua route yang belum diimplementasi.
- **Layout:** Dinamis via variabel `$layout` (default: `Admin.Layout._layout`)
- **Props:** `$pageTitle`, `$description`, `$layout`
- **Tampilan:** Icon hard-hat + badge "Dalam Pengembangan" + judul + deskripsi

---

## 7. Controller Pattern

Semua controller Admin mengikuti pola yang sama:

1. **index()** — Query dengan filter (search, status, dll) → return view atau JSON
2. **store()** — Validasi → create → return redirect atau JSON
3. **update()** — Find → validasi → update → return redirect atau JSON
4. **destroy()** — Find → delete (cleanup file) → return redirect atau JSON

**Dual response:** Setiap action mendukung HTML (redirect + flash) dan JSON (untuk AJAX).

---

## 8. Build & Development

```bash
# Install dependencies
npm install
composer install

# Development (Vite dev server)
npm run dev

# Production build
npm run build

# Database migration
php artisan migrate --force

# Run Laravel server
php artisan serve
```

**Output build:** `public/build/assets/app-*.css` dan `app-*.js`

---

## 9. Konvensi Kode

- **Commit message:** Bahasa Indonesia
- **Blade partial:** Prefix `_` (contoh: `_layout.blade.php`, `_sidebar.blade.php`)
- **Route naming:** `{role}.{fitur}` (contoh: `admin.pengurus`, `pengurus.anggota`)
- **ID format:** `{PREFIX}-XXXX` dengan 4 digit random (contoh: `PGR-1234`, `PokTan-5678`)
- **CSS custom class:** `.admin-shell-content` dan `.pengurus-shell-content` untuk margin sidebar
- **File upload:** Disimpan di `public/uploads/profil/`

---

## 10. Rencana Pengembangan (Fitur Baru & UI/UX)

### 10.1 Konsep Fitur Baru
- **Marketplace / Penjualan Hasil Tani:** Memfasilitasi kelompok tani menjual hasil panen langsung ke pembeli.
- **Smart Farming / IoT Integration:** Pemantauan sensor tanah, suhu, dan kelembapan secara real-time.
- **Forum Diskusi:** Wadah interaksi dan tanya jawab antar sesama pengurus/anggota kelompok tani.
- **Push Notification & Alert:** Notifikasi instan mengenai prediksi cuaca ekstrem atau jadwal panen.

### 10.2 Pembaruan Struktur Warna UI
- **Dark Mode Palette:** Menambahkan struktur warna khusus untuk mode gelap (Dark Mode) seperti `pawon-dark-800: #121A0C`.
- **Semantic Colors:** Memperjelas penggunaan warna peringatan (*Danger/Error*, *Warning*, *Success*, *Info*).
- **Secondary/Accent Colors:** Menambahkan warna sekunder (misal kuning tanah atau biru air) untuk melengkapi warna utama hijau (`pawon-100` hingga `pawon-900`).
