# PAWONTANI – TECHNICAL DESIGN DOCUMENT (VERSION 3.2)
## Fitur Verifikasi Lahan & Sistem Notifikasi Terpadu
### Final Security, Database & Implementation Readiness Review

> **Versi Dokumen:** 3.2 (Final Pre-Implementation Baseline)  
> **Tanggal Pembaruan:** 1 November 2026  
> **Status Dokumen:** FINAL & READY FOR FINAL REVIEW (Menunggu Persetujuan Stakeholder)  
> **Target Arsitektur:** Laravel 12 (Web Dashboard & REST API V1) + Flutter 3 (Mobile Anggota)  
> **Target Basis Data:** PostgreSQL 17.6 (Supabase Cloud - Region `ap-southeast-1`) + Cloudinary Storage  
> **Target Autentikasi:** Web Session Cookie (Pengurus) & Laravel Sanctum Bearer Token (Mobile Anggota)

---

## 1. Ringkasan Eksekutif & Keputusan Stakeholder Final

Dokumen ini merupakan spesifikasi teknis final (**Versi 3.2**) yang menyatukan seluruh hasil audit database aktual ([DATABASE_AUDIT_LAHAN.md](DATABASE_AUDIT_LAHAN.md)), arsitektur keamanan berlapis, dan klarifikasi keputusan operasional dari Ketua Kelompok Tani serta tim pengembang PawonTani.

### Ringkasan Keputusan & Kebijakan Operasional:
1. **Pengelolaan Lahan Mandiri (Self-Service Ownership):**
   - Anggota memiliki kendali penuh atas petak garapan miliknya sendiri (`id_pengguna == auth()->id()`).
   - Nilai `id_pengguna` dan `id_kelompok` **wajib diambil dari kredensial autentikasi aktif server (`auth()->user()`)**, dilarang mempercayai input ID kepemilikan/kelompok yang dikirim langsung dari client (pencegahan *parameter tampering*).
   - Jika Anggota mengedit data lahan yang **sudah terverifikasi**, statusnya **otomatis gugur dan kembali menjadi `menunggu_verifikasi`**, serta nomor revisi bertambah (`nomor_revisi++`).
2. **Kewenangan Terbatas Pengurus (Reviewer Role):**
   - Pengurus **hanya dapat melihat dan memverifikasi** data lahan milik anggota dalam kelompoknya (`id_kelompok`).
   - Pengurus **DILARANG KERAS** menambah, mengubah atribut fisik (luas, koordinat, komoditas), maupun menghapus lahan anggota.
   - Pengurus **berhak mencabut status `terverifikasi`** kembali menjadi `perlu_perbaikan` apabila ditemukan ketidaksesuaian saat peninjauan fisik di lapangan (dengan catatan alasan wajib).
3. **Penerima Notifikasi Terarah (Strict: Khusus Ketua Kelompok):**
   - Notifikasi pengajuan lahan baru dan pengajuan ulang perbaikan **HANYA dikirim kepada Ketua Kelompok Tani terkait**.
   - **TIDAK ADA pengalihan otomatis (*auto-forward*) ke Sekretaris atau Pengurus lain.** Ketiadaan Ketua di database merupakan anomali integritas data/konfigurasi, bukan alur operasional normal.
   - Jika akun Ketua tidak ditemukan, proses penyimpanan lahan **tetap berhasil (tidak membatalkan transaksi)**, sistem mencatat *error log* kegagalan notifikasi, dan menerbitkan peringatan sistem internal.
4. **Mekanisme Notifikasi REST API + In-App (Tanpa FCM):**
   - Notifikasi disimpan di tabel `notifikasi` Supabase dan dikonsumsi oleh Flutter via REST API.
   - **FCM (Firebase Cloud Messaging), Web Push, dan background push notification TIDAK digunakan pada tahap ini.**
5. **Preservasi Audit Trail & Integritas Relasional:**
   - Riwayat verifikasi disimpan dalam tabel terpisah `riwayat_verifikasi_lahan` (`ON DELETE NO ACTION`).
   - Seluruh foreign key diselaraskan menggunakan tipe data aktual PostgreSQL: **`VARCHAR(20)`**.

---

## 2. Arsitektur Web, Mobile, Laravel, dan Supabase

```
┌──────────────────────────────────────┐       ┌──────────────────────────────────────┐
│       FLUTTER MOBILE (Anggota)       │       │        LARAVEL WEB (Pengurus)        │
│ - Layar Lahan & Detail Lahan         │       │ - Dashboard Verifikasi Kelompok      │
│ - Form Tambah/Edit (Multipart Form)  │       │ - Detail Pemeriksaan & Peta Leaflet  │
│ - Layar Notifikasi (Polling/API)     │       │ - Modal Setujui & Minta Perbaikan    │
└──────────────────┬───────────────────┘       └──────────────────┬───────────────────┘
                   │                                              │
    Sanctum Bearer Token (/api/v1/*)                Session Auth Cookie (/pengurus/*)
                   │                                              │
                   ▼                                              ▼
┌─────────────────────────────────────────────────────────────────────────────────────┐
│                            LARAVEL 12 BACKEND CORE SERVICE                          │
│ ┌────────────────────────┐ ┌────────────────────────┐ ┌───────────────────────────┐ │
│ │   LahanPolicy (Auth)   │ │  LahanService (Tx)     │ │  NotifikasiService        │ │
│ └────────────────────────┘ └────────────────────────┘ └───────────────────────────┘ │
│ ┌─────────────────────────────────────────────────────────────────────────────────┐ │
│ │ CloudinaryService: Unggah foto baru -> Commit DB -> Hapus foto lama (Safe Flow) │ │
│ └─────────────────────────────────────────────────────────────────────────────────┘ │
└──────────────────────────────────────────┬──────────────────────────────────────────┘
                                           │
                         PostgreSQL Database (Supabase)
                       (Koneksi: postgres / Pooler 6543)
                                           │
       ┌───────────────────────────────────┼──────────────────────────────────┐
       ▼                                   ▼                                  ▼
┌──────────────┐         ┌─────────────────────────────────┐        ┌────────────────────┐
│ tabel: lahan │         │ tabel: riwayat_verifikasi_lahan │        │ tabel: notifikasi  │
│(SoftDeletes) │         │ (Audit Trail - ON DELETE NO ACT)│        │(ON DEL SET NULL FK)│
└──────────────┘         └─────────────────────────────────┘        └────────────────────┘
```

### Pemisahan Arsitektur:
1. **Laravel Web (Pengurus):**
   - Menggunakan autentikasi sesi bawaan Laravel (`middleware(['auth', 'role:Pengurus'])`).
   - Bertindak sebagai peninjau (*reviewer*): melihat daftar lahan kelompok, memeriksa rincian, menyetujui, meminta perbaikan, atau mencabut verifikasi.
   - Tidak memiliki endpoint atau tombol untuk menambah, mengedit data fisik, atau menghapus lahan anggota.
2. **Laravel REST API V1 (Mobile Anggota):**
   - Menggunakan token autentikasi Laravel Sanctum (`middleware('auth:sanctum')`).
   - Menyediakan endpoint lengkap pendaftaran, pembaruan, soft-delete, detail lahan, dan pembacaan notifikasi untuk Anggota.
   - Pembaruan formulir yang menyertakan berkas gambar menggunakan HTTP `POST` dengan parameter `_method: 'PUT'` (*Method Spoofing*) guna menghindari kegagalan parsing multipart bawaan PHP native.
3. **Database Supabase PostgreSQL 17.6:**
   - Single Source of Truth bagi Web dan Mobile. Terhubung melalui koneksi pooler port 6543.

---

## 3. Skema Basis Data & Integritas Relasional

Mengacu langsung pada hasil audit database riil ([DATABASE_AUDIT_LAHAN.md](DATABASE_AUDIT_LAHAN.md)), seluruh tipe data foreign key diselaraskan menggunakan **`VARCHAR(20)`**:

### 3.1 Entity Relationship Diagram (ERD) Final

```mermaid
erDiagram
    KELOMPOK_TANI ||--o{ PENGGUNA : "menaungi"
    KELOMPOK_TANI ||--o{ LAHAN : "mewadahi"
    KOMODITAS ||--o{ LAHAN : "ditanam_pada"
    
    PENGGUNA ||--o{ LAHAN : "memiliki (anggota)"
    PENGGUNA ||--o{ LAHAN : "memverifikasi (pengurus)"
    
    LAHAN ||--o{ RIWAYAT_VERIFIKASI_LAHAN : "memiliki audit trail (NO ACTION FK)"
    PENGGUNA ||--o{ RIWAYAT_VERIFIKASI_LAHAN : "mencatat aktor tindakan"
    
    PENGGUNA ||--o{ NOTIFIKASI : "menerima notifikasi"
    PENGGUNA ||--o{ NOTIFIKASI : "mengirim notifikasi"
    LAHAN ||--o{ NOTIFIKASI : "konteks referensi (SET NULL FK)"
    RIWAYAT_VERIFIKASI_LAHAN ||--o{ NOTIFIKASI : "siklus referensi (SET NULL FK)"

    KELOMPOK_TANI {
        varchar(20) id_kelompok PK "PokTan-XXXX"
        varchar(150) nama_kelompok UK
        text alamat
        varchar(20) status
    }

    PENGGUNA {
        varchar(20) id_pengguna PK "PGR-XXXX"
        varchar(100) nama
        varchar(16) nik UK
        varchar(50) username UK
        varchar(20) role "Pengurus, Anggota, PPL"
        varchar(30) jabatan "Ketua, Sekretaris, Bendahara, dll"
        varchar(20) id_kelompok FK "Kelompok asal"
        varchar(20) status "Aktif, Tidak Aktif"
    }

    KOMODITAS {
        varchar(20) id_komoditas PK "kmdt-XXXX"
        varchar(100) nama_komoditas UK
        varchar(100) kategori
    }

    LAHAN {
        varchar(30) id_lahan PK "LHN-YYYY-XXXX"
        varchar(20) id_pengguna FK "Pemilik lahan"
        varchar(20) id_kelompok FK "Denormalisasi query kelompok"
        varchar(150) nama_lahan "Nama petak"
        decimal(10,4) luas "Baku Hektare (ha)"
        text alamat_lahan "Detail lokasi fisik"
        numeric(10,7) latitude "GPS Latitude"
        numeric(10,7) longitude "GPS Longitude"
        varchar(20) id_komoditas FK "kmdt-XXXX"
        varchar(50) musim_tanam "MT-1, MT-2, dsb"
        varchar(255) foto_lahan "URL Cloudinary"
        text keterangan "Catatan fisik anggota"
        varchar(30) status_operasional "aktif, istirahat, persiapan"
        varchar(30) status_verifikasi "menunggu_verifikasi, terverifikasi, perlu_perbaikan"
        text catatan_verifikasi "Instruksi perbaikan terakhir"
        timestamp tanggal_verifikasi "Waktu verifikasi terakhir"
        varchar(20) verifikator_id FK "Pengurus verifikator"
        integer nomor_revisi "Siklus pengajuan (1..N)"
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at "Soft Deletes"
    }

    RIWAYAT_VERIFIKASI_LAHAN {
        bigserial id_riwayat PK
        varchar(30) id_lahan FK "ON DELETE NO ACTION"
        varchar(20) id_pengguna FK "Aktor: Pengurus/Anggota"
        varchar(40) aksi "pengajuan_awal, minta_perbaikan, pengajuan_ulang, disetujui, pencabutan_verifikasi"
        varchar(30) status_sebelumnya
        varchar(30) status_sesudahnya
        text catatan "Instruksi/alasan wajib"
        integer nomor_revisi
        jsonb snapshot_data "Rekaman data saat aksi"
        timestamp created_at
    }

    NOTIFIKASI {
        varchar(30) id_notifikasi PK "NTF-YYYYMMDD-XXXX"
        varchar(20) id_pengirim FK "NULL jika sistem"
        varchar(20) id_penerima FK "Hanya Ketua PokTan atau Anggota"
        varchar(30) id_lahan FK "ON DELETE SET NULL"
        bigint id_riwayat FK "ON DELETE SET NULL"
        varchar(50) jenis_notifikasi "lahan_baru, lahan_perbaikan_dikirim, lahan_perlu_perbaikan, lahan_terverifikasi"
        varchar(150) judul
        text pesan
        jsonb data_payload "Payload navigasi mobile"
        boolean sudah_dibaca "Default false"
        timestamp dibaca_pada
        timestamp created_at
        timestamp updated_at
    }
```

---

### 3.2 Spesifikasi Kolom, Constraint & Foreign Key

#### A. Tabel `lahan`
- **Primary Key:** `id_lahan` bertipe `VARCHAR(30)` dengan **UNIQUE & PRIMARY KEY constraint**.
- **Standar Satuan Baku Luas:** Kolom `luas` bertipe `DECIMAL(10,4)` dan mutlak menggunakan satuan **Hektare (`ha`)**. Presisi 4 digit desimal memungkinkan pencatatan hingga $1\text{ m}^2$ ($0.0001\text{ ha}$). Konversi dari $m^2$ dilakukan di Form Request Laravel (`$luas_ha = $request->input('luas_m2') / 10000`).
- **Soft Deletes:** Menggunakan `deleted_at` (`SoftDeletes` trait). Data fisik tidak terhapus dari tabel saat anggota menekan hapus.

| Nama Kolom | Tipe Data PostgreSQL | Nullable | Default | Definisi Constraint & Foreign Key |
|---|---|:---:|---|---|
| `id_lahan` | `varchar(30)` | NO | - | **PRIMARY KEY** (Format: `LHN-YYYY-XXXX`) |
| `id_pengguna` | `varchar(20)` | NO | - | **FK** `pengguna(id_pengguna)` ON DELETE RESTRICT (INDEX) |
| `id_kelompok` | `varchar(20)` | NO | - | **FK** `kelompok_tani(id_kelompok)` ON DELETE RESTRICT (INDEX) |
| `nama_lahan` | `varchar(150)` | NO | - | - |
| `luas` | `decimal(10,4)` | NO | - | Satuan baku Hektare (`ha`). Contoh: `0.8500` |
| `provinsi_id` | `varchar(10)` | YES | NULL | Kode BPS Provinsi |
| `kabupaten_id` | `varchar(10)` | YES | NULL | Kode BPS Kabupaten/Kota |
| `kecamatan_id` | `varchar(10)` | YES | NULL | Kode BPS Kecamatan |
| `desa_id` | `varchar(20)` | YES | NULL | Kode BPS Desa/Kelurahan |
| `alamat_lahan` | `text` | NO | - | Rincian alamat fisik petak |
| `latitude` | `numeric(10,7)` | YES | NULL | Titik lintang GPS |
| `longitude` | `numeric(10,7)` | YES | NULL | Titik bujur GPS |
| `id_komoditas` | `varchar(20)` | YES | NULL | **FK** `komoditas(id_komoditas)` ON DELETE SET NULL (INDEX) |
| `musim_tanam` | `varchar(50)` | YES | NULL | Misal: `'MT-1 (Musim Hujan)'` |
| `foto_lahan` | `varchar(255)` | YES | NULL | URL publik Cloudinary |
| `keterangan` | `text` | YES | NULL | Catatan kondisi fisik anggota |
| `status_operasional`| `varchar(30)` | NO | `'persiapan'` | `'aktif'`, `'istirahat'`, `'persiapan'` |
| `status_verifikasi` | `varchar(30)` | NO | `'menunggu_verifikasi'`| `'menunggu_verifikasi'`, `'terverifikasi'`, `'perlu_perbaikan'` (INDEX) |
| `catatan_verifikasi`| `text` | YES | NULL | Instruksi/alasan perbaikan terakhir |
| `tanggal_verifikasi`| `timestamp` | YES | NULL | Waktu verifikasi terakhir |
| `verifikator_id` | `varchar(20)` | YES | NULL | **FK** `pengguna(id_pengguna)` ON DELETE SET NULL |
| `nomor_revisi` | `integer` | NO | `1` | Nomor versi pengajuan aktif |
| `created_at` | `timestamp` | NO | CURRENT | INDEX |
| `updated_at` | `timestamp` | NO | CURRENT | - |
| `deleted_at` | `timestamp` | YES | NULL | INDEX (Soft Delete) |

#### B. Tabel `riwayat_verifikasi_lahan`
- **Proteksi Hapus Permanen (No Cascade):** Foreign key `id_lahan` menggunakan **`ON DELETE NO ACTION`** (atau `RESTRICT`). Ini menjamin data audit trail hukum verifikasi tidak akan pernah hilang meskipun terjadi pembersihan data fisik lahan.

| Nama Kolom | Tipe Data PostgreSQL | Nullable | Default | Definisi Constraint & Foreign Key |
|---|---|:---:|---|---|
| `id_riwayat` | `bigserial` | NO | - | **PRIMARY KEY** |
| `id_lahan` | `varchar(30)` | NO | - | **FK** `lahan(id_lahan)` ON DELETE NO ACTION (INDEX) |
| `id_pengguna` | `varchar(20)` | NO | - | **FK** `pengguna(id_pengguna)` ON DELETE RESTRICT (INDEX) |
| `aksi` | `varchar(40)` | NO | - | `'pengajuan_awal'`, `'minta_perbaikan'`, `'pengajuan_ulang'`, `'disetujui'`, `'pencabutan_verifikasi'` |
| `status_sebelumnya` | `varchar(30)` | YES | NULL | Status sebelum tindakan dilakukan |
| `status_sesudahnya` | `varchar(30)` | NO | - | Status setelah tindakan diterapkan |
| `catatan` | `text` | YES | NULL | Catatan alasan/instruksi pengurus atau penjelasan anggota |
| `nomor_revisi` | `integer` | NO | `1` | Siklus revisi terkait |
| `snapshot_data` | `jsonb` | YES | NULL | Snapshot atribut data lahan saat kejadian |
| `created_at` | `timestamp` | NO | CURRENT | INDEX |

#### C. Tabel `notifikasi`
- **Relasi Bebas Risiko:** Foreign key `id_lahan` dan `id_riwayat` menggunakan **`ON DELETE SET NULL`**. Notifikasi tetap tersimpan dan dapat dibaca oleh pengguna meskipun entitas lahan di-soft-delete.

| Nama Kolom | Tipe Data PostgreSQL | Nullable | Default | Definisi Constraint & Foreign Key |
|---|---|:---:|---|---|
| `id_notifikasi` | `varchar(30)` | NO | - | **PRIMARY KEY** (Format: `NTF-YYYYMMDD-XXXX`) |
| `id_pengirim` | `varchar(20)` | YES | NULL | **FK** `pengguna(id_pengguna)` ON DELETE SET NULL |
| `id_penerima` | `varchar(20)` | NO | - | **FK** `pengguna(id_pengguna)` ON DELETE CASCADE (INDEX) |
| `id_lahan` | `varchar(30)` | YES | NULL | **FK** `lahan(id_lahan)` ON DELETE SET NULL (INDEX) |
| `id_riwayat` | `bigint` | YES | NULL | **FK** `riwayat_verifikasi_lahan(id_riwayat)` ON DELETE SET NULL |
| `jenis_notifikasi` | `varchar(50)` | NO | - | `'lahan_baru'`, `'lahan_perbaikan_dikirim'`, `'lahan_perlu_perbaikan'`, `'lahan_terverifikasi'` |
| `judul` | `varchar(150)` | NO | - | Ringkasan judul notifikasi |
| `pesan` | `text` | NO | - | Isi lengkap pesan notifikasi |
| `data_payload` | `jsonb` | YES | NULL | Contoh: `{"id_lahan": "LHN-2026-8941", "route": "detail_lahan"}` |
| `sudah_dibaca` | `boolean` | NO | `false` | INDEX (query unread count) |
| `dibaca_pada` | `timestamp` | YES | NULL | Waktu pengguna membaca notifikasi |
| `created_at` | `timestamp` | NO | CURRENT | INDEX |
| `updated_at` | `timestamp` | NO | CURRENT | - |

---

### 3.3 Mekanisme ID Generation & Penanganan Concurrency Collision

Untuk menjamin keunikan ID tanpa celah *Race Condition* (*Time-of-Check to Time-of-Use* / TOCTOU) saat banyak request bersamaan:

1. **Format ID:**
   - Lahan: `LHN-` + Tahun (4 digit) + `-` + 4 Karakter Alfanumerik Acak (Contoh: `LHN-2026-8X9A`).
   - Notifikasi: `NTF-` + Tanggal (YYYYMMDD) + `-` + 4 Karakter Alfanumerik Acak (Contoh: `NTF-20261101-7K2M`).
2. **Pertahanan Berlapis (Aplikasi & Database Level):**
   - **Tingkat Database:** Kolom `id_lahan` dan `id_notifikasi` memiliki **PRIMARY KEY / UNIQUE CONSTRAINT**.
   - **Tingkat Aplikasi:** Jangan hanya mengandalkan pengecekan `where()->exists()`. Generator dibungkus dalam blok penanganan *retry loop* yang secara eksplisit menangani `Illuminate\Database\QueryException` dengan kode SQLSTATE `23505` (*unique_violation* pada PostgreSQL):

```php
public static function createWithCollisionHandling(array $attributes, int $maxRetries = 5): self
{
    $attempts = 0;
    while ($attempts < $maxRetries) {
        try {
            $attributes['id_lahan'] = 'LHN-' . date('Y') . '-' . strtoupper(Str::random(4));
            return self::create($attributes);
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangkap kode SQLSTATE 23505 (PostgreSQL Unique Constraint Violation)
            if ($e->getCode() === '23505' || str_contains($e->getMessage(), 'lahan_pkey')) {
                $attempts++;
                if ($attempts >= $maxRetries) {
                    Log::critical("ID Generation Collision gagal setelah {$maxRetries} percobaan untuk tabel lahan.");
                    throw new \RuntimeException("Gagal mengalokasikan ID Lahan yang unik, silakan coba kembali.", 500, $e);
                }
                continue;
            }
            throw $e;
        }
    }
    throw new \RuntimeException("Gagal membuat data lahan.");
}
```

---

## 4. Aturan Bisnis & Penanganan Notifikasi Ketua Kelompok

### 4.1 Klarifikasi Kondisi Kelompok Tanpa Ketua
Berdasarkan klarifikasi resmi stakeholder, keberadaan 3 kelompok tani tanpa akun Ketua saat audit basis data merupakan **data sementara uji coba (*test data*)**, bukan desain operasional normal. Dalam operasional sebenarnya:
- **Setiap kelompok tani aktif wajib memiliki 1 orang Ketua.**
- **Ketua adalah SATU-SATUNYA penerima notifikasi pengajuan baru dan pengajuan ulang.**

### 4.2 Kriteria Identifikasi Ketua Kelompok
Ketua Kelompok Tani diidentifikasi secara ketat menggunakan kombinasi 4 kriteria:
1. `id_kelompok = :id_kelompok` (Sesuai kelompok asal anggota pemilik lahan)
2. `role = 'Pengurus'`
3. `jabatan = 'Ketua'`
4. `status = 'Aktif'`

### 4.3 Alur Kerja & Penanganan Jika Ketua Tidak Ditemukan
```
Anggota Simpan Lahan
       │
       ▼
Simpan ke DB Lahan (Commit Sukses)
       │
       ▼
Query Ketua Kelompok (4 Kriteria)
       │
       ├─────────────────────────────────────────┐
       ▼ (Ketua Ditemukan)                       ▼ (Ketua Tidak Ada / Anomali Data)
Kirim Notifikasi ke Ketua               1. JANGAN kirim ke Sekretaris / Pengurus lain!
                                        2. JANGAN membatalkan (rollback) data lahan!
                                        3. Catat Log Error Sistem (Integritas Data)
                                        4. Buat System Warning Alert Internal
```

- **Ketua Ditemukan:** Notifikasi dibuat dengan `id_penerima = ketua->id_pengguna` dan disimpan ke tabel `notifikasi`.
- **Ketua Tidak Ditemukan:**
  - **TIDAK ADA PENGALIHAN OTOMATIS:** Sistem **DILARANG** mengalihkan notifikasi ke Sekretaris, Bendahara, atau pengurus lainnya tanpa persetujuan stakeholder.
  - **Data Lahan Aman:** Pendaftaran/perbaikan lahan **tetap berhasil disimpan** ke database.
  - **Pencatatan Anomali:** Sistem mencatat error melalui:
    ```php
    Log::error("ANOMALI INTEGRITAS DATA: Kelompok {$id_kelompok} tidak memiliki Ketua aktif. Notifikasi lahan {$id_lahan} tidak dapat dikirim.");
    ```
  - **Tampilan Peringatan:** Sistem menyediakan penanda (*flag*) peringatan internal pada dashboard Pengurus bahwa kelompok tersebut belum memiliki Ketua definitif.

---

## 5. Arsitektur Keamanan: Autentikasi, Otorisasi & RLS

### 5.1 Kondisi Koneksi Database Saat Ini
- **Web Pengurus:** Menggunakan autentikasi berbasis Sesi Web Laravel (`session cookie`).
- **Mobile Anggota:** Menggunakan token otorisasi Bearer via Laravel Sanctum.
- **Koneksi Database:** Laravel terhubung ke PostgreSQL Supabase menggunakan user `postgres.taoixctvqvmombctuygv` melalui Supabase Connection Pooler (port 6543).
- **Atribut `BYPASSRLS`:** User `postgres` adalah *table owner / superuser* yang memiliki atribut **`BYPASSRLS`** di mesin PostgreSQL.
- **Konsekuensi Teknis:** **Seluruh query yang dikirimkan oleh Laravel secara otomatis mem-bypass evaluasi RLS pada PostgreSQL.** Kebijakan RLS di level PostgreSQL tidak otomatis membatasi query Laravel.

### 5.2 Pertahanan Primer: Layer Aplikasi Laravel (Enforcement Aktif)
Karena koneksi database mem-bypass RLS, **keamanan dan isolasi data ditegakkan 100% pada lapisan aplikasi Laravel**:

1. **`App\Policies\LahanPolicy`:**
   - **`view(User $user, Lahan $lahan)`:**
     - Anggota hanya boleh melihat lahannya sendiri: `$user->id_pengguna === $lahan->id_pengguna`.
     - Pengurus hanya boleh melihat lahan dalam kelompoknya: `$user->role === 'Pengurus' && $user->id_kelompok === $lahan->id_kelompok`.
   - **`create(User $user)`:**
     - Hanya pengguna dengan `$user->role === 'Anggota' && $user->status === 'Aktif'`.
     - Pengurus **DITOLAK (Unauthorized 403)**.
   - **`update(User $user, Lahan $lahan)`:**
     - Hanya Anggota pemilik lahan: `$user->id_pengguna === $lahan->id_pengguna`.
     - Pengurus **DITOLAK (Unauthorized 403)** untuk mengubah atribut fisik lahan.
   - **`delete(User $user, Lahan $lahan)`:**
     - Hanya Anggota pemilik lahan: `$user->id_pengguna === $lahan->id_pengguna`.
     - Pengurus **DITOLAK (Unauthorized 403)**.
   - **`verify(User $user, Lahan $lahan)`:**
     - Hanya Pengurus dari kelompok yang sama: `$user->role === 'Pengurus' && $user->id_kelompok === $lahan->id_kelompok`.
     - Anggota **DITOLAK (Unauthorized 403)**.
2. **Validasi Kepemilikan Mutlak di Form Request:**
   - Nilai `id_pengguna` dan `id_kelompok` tidak diambil dari input form client, melainkan disuntikkan secara aman oleh server:
     ```php
     $data['id_pengguna'] = auth()->user()->id_pengguna;
     $data['id_kelompok'] = auth()->user()->id_kelompok;
     ```
3. **Penundaan Hak Akses Admin / PPL:**
   - Hak akses Admin dan PPL **belum diputuskan oleh stakeholder** dan **TIDAK diimplementasikan pada sprint pertama**.

### 5.3 Layer Sekunder: RLS Supabase (Future-Proofing Direct Access)
Meskipun query Laravel mem-bypass RLS, **tabel baru tetap wajib mengaktifkan RLS (`ALTER TABLE ... ENABLE ROW LEVEL SECURITY`)** untuk konsistensi dengan 10 tabel yang ada dan mengantisipasi jika di masa depan terdapat akses langsung client via Supabase PostgREST:

```sql
-- Aktivasi RLS
ALTER TABLE public.lahan ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.riwayat_verifikasi_lahan ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.notifikasi ENABLE ROW LEVEL SECURITY;

-- Kebijakan Akses PostgREST / Supabase Client (Jika diaktifkan di masa depan)
CREATE POLICY "lahan_owner_policy" ON public.lahan
    FOR ALL TO authenticated
    USING (id_pengguna = auth.jwt() ->> 'id_pengguna');

CREATE POLICY "lahan_pengurus_select_policy" ON public.lahan
    FOR SELECT TO authenticated
    USING (
        id_kelompok IN (
            SELECT id_kelompok FROM public.pengguna 
            WHERE id_pengguna = (auth.jwt() ->> 'id_pengguna') AND role = 'Pengurus'
        )
    );

CREATE POLICY "notifikasi_user_policy" ON public.notifikasi
    FOR ALL TO authenticated
    USING (id_penerima = auth.jwt() ->> 'id_pengguna');
```

> [!CAUTION]
> **Peringatan Keamanan Database:**  
> Kredensial database Supabase atau *service role key* **DILARANG KERAS diekspos ke aplikasi mobile Flutter**. Aplikasi Flutter hanya berkomunikasi secara eksklusif dengan endpoint Laravel REST API menggunakan Bearer Token Sanctum.

---

## 6. Penanganan Kegagalan Media Cloudinary (Safe 6-Step Lifecycle)

Untuk menjamin tidak ada foto lama yang hilang saat pembaruan gagal, dan tidak ada file sampah (*orphan media*) yang tertimbun di Cloudinary, diterapkan **Protokol 6 Tahap Penggantian Media**:

```
[Tahap 1: Upload Foto Baru] ──> Dapatkan $fotoBaruUrl & $fotoBaruPublicId
             │
             ▼
[Tahap 2: Transaksi DB] ──> UPDATE lahan SET foto_lahan = $fotoBaruUrl
             │
             ├────────────────────────────────────────────────┐
             ▼ (DB Commit Sukses)                             ▼ (DB Exception / Rollback)
[Tahap 3: Hapus Foto Lama]                       [Tahap 4: Hapus Kompensasi Foto Baru]
Hapus $fotoLamaUrl dari Cloudinary               Hapus $fotoBaruUrl dari Cloudinary
             │                                                │
             ├──────────────────────────┐                     ├──────────────────────────┐
             ▼ (Hapus Sukses)           ▼ (Hapus Gagal)       ▼ (Hapus Sukses)           ▼ (Hapus Gagal)
        [SELESAI]               [Tahap 5: Orphan Log]     [Foto Lama Aman]       [Tahap 6: Orphan Log]
                                Catat ke tabel antrean                           Catat ke tabel antrean
                                retry cleanup Cloudinary                         retry cleanup Cloudinary
```

### Penanganan 3 Skenario Kegagalan Spesifik:
1. **Skenario 1: Upload foto baru berhasil, tetapi transaksi database gagal.**
   - *Tindakan:* Transaksi database di-rollback. Foto lama tetap aman di Cloudinary dan database. Sistem mengeksekusi kompensasi dengan menghapus foto baru dari Cloudinary (`CloudinaryService::delete($fotoBaruUrl)`).
2. **Skenario 2: Transaksi database berhasil, tetapi penghapusan foto lama gagal.**
   - *Tindakan:* **DILARANG membatalkan transaksi database yang sudah commit.** Data database tetap sah menggunakan foto baru. Kegagalan penghapusan foto lama dicatat ke log error sistem / tabel antrean `orphan_media_cleanups` untuk dicoba ulang (*retry job*) secara otomatis.
3. **Skenario 3: Penghapusan foto baru sebagai kompensasi gagal.**
   - *Tindakan:* Identitas media foto baru (`public_id`) dicatat permanen ke log error sistem / antrean cleanup. Informasi kegagalan tidak boleh diabaikan (*never swallow exception info*) agar storage tetap efisien.

---

## 7. Workflow Pengelolaan, Verifikasi & Notifikasi

### 7.1 Alur Verifikasi Data Lahan Baru
1. **Anggota (Flutter):** Mengisi data lahan, mengunggah foto petak, lalu menekan Simpan.
2. **Backend API:** Mengunggah foto ke Cloudinary, memulai transaksi DB, menyimpan lahan dengan status `menunggu_verifikasi` (revisi: 1), mencatat audit `riwayat_verifikasi_lahan` (`aksi: pengajuan_awal`).
3. **Notifikasi:** Backend memeriksa Ketua Kelompok aktif. Jika ada, simpan notifikasi untuk Ketua. Jika tidak ada, catat error log integritas data, lewati notifikasi, dan transaksi lahan tetap di-commit.
4. **Ketua (Web):** Melihat daftar antrean di `/pengurus/lahan`. Membuka rincian di `/pengurus/lahan/{id}`.
   - **Jika Disetujui:** Ketua klik *Setujui*. Status menjadi `terverifikasi`, catat riwayat (`aksi: disetujui`), kirim notifikasi ke Anggota.
   - **Jika Ditolak:** Ketua klik *Minta Perbaikan* (wajib mengisi alasan). Status menjadi `perlu_perbaikan`, catat riwayat (`aksi: minta_perbaikan`), kirim notifikasi instruksi ke Anggota.

### 7.2 Alur Perbaikan Data oleh Anggota
1. **Anggota (Flutter):** Menerima notifikasi perbaikan. Membuka detail lahan, membaca catatan instruksi perbaikan dari Ketua pada *Warning Banner*.
2. **Anggota (Flutter):** Mengedit data/mengunggah foto koreksi, lalu menekan *Kirim Ulang*.
3. **Backend API:** Menerapkan Protokol 6 Tahap Cloudinary, memperbarui data lahan, otomatis mengubah status kembali ke `menunggu_verifikasi`, menaikkan nomor revisi (`nomor_revisi++`), mencatat riwayat (`aksi: pengajuan_ulang`).
4. **Notifikasi:** Mengirim notifikasi pengajuan ulang ke Ketua Kelompok terkait.

### 7.3 Alur Perubahan Mandiri Pasca-Verifikasi oleh Anggota
- Jika Anggota mengedit lahan yang berstatus `terverifikasi`, sistem secara otomatis **membatalkan status terverifikasi dan mengembalikannya ke `menunggu_verifikasi`** (`nomor_revisi++`).
- Tercatat pada riwayat verifikasi sebagai `aksi: pengajuan_ulang` dengan catatan "Perubahan mandiri data lahan oleh pemilik pasca-verifikasi".
- Notifikasi pembaruan dikirimkan kembali ke Ketua Kelompok.

### 7.4 Alur Pencabutan Status Verifikasi oleh Pengurus
- Jika saat inspeksi fisik berkala Pengurus menemukan ketidaksesuaian data pada lahan yang berstatus `terverifikasi`, Pengurus menekan tombol *Cabut Verifikasi & Minta Perbaikan*.
- Pengurus **wajib mengisi alasan pencabutan** (minimal 10 karakter).
- Backend memperbarui status lahan menjadi `perlu_perbaikan`, mencatat riwayat verifikasi (`aksi: pencabutan_verifikasi`), dan mengirimkan notifikasi pencabutan beserta instruksi ke Anggota pemilik lahan.

---

## 8. Desain Endpoint API Mobile & Route Web Pengurus

### 8.1 API Mobile Anggota (`routes/api.php` - Prefix: `/api/v1`)
Terproteksi `middleware('auth:sanctum')`.

| Method | Endpoint | Form Request | Deskripsi & Validasi |
|---|---|---|---|
| `GET` | `/lahan` | - | Mengambil daftar lahan milik anggota login. Filter: `status_verifikasi`, `search`, pagination. |
| `POST` | `/lahan` | `StoreLahanRequest` | Pendaftaran petak lahan baru. Input: `nama_lahan`, `luas_m2` (dikonversi ke ha), `alamat_lahan`, `latitude`, `longitude`, `id_komoditas`, `musim_tanam`, `foto_lahan` (max 5MB). Set status `menunggu_verifikasi`. |
| `GET` | `/lahan/{id}` | - | Rincian lengkap petak, foto, instruksi perbaikan terakhir, dan audit trail riwayat verifikasi. |
| `POST` | `/lahan/{id}` | `UpdateLahanRequest` | **Multipart Update:** Form perbaikan/edit dengan field `_method: 'PUT'`. Menjalankan Protokol Cloudinary Aman. Mereset status ke `menunggu_verifikasi` dan menaikkan nomor revisi. |
| `DELETE` | `/lahan/{id}` | - | Soft delete petak lahan milik anggota. |
| `GET` | `/notifikasi` | - | Mengambil daftar riwayat notifikasi milik anggota login. Filter: `unread_only=true`. |
| `GET` | `/notifikasi/unread-count`| - | Mengambil jumlah notifikasi belum dibaca untuk badge lonceng. |
| `PUT` | `/notifikasi/{id}/baca` | - | Menandai satu notifikasi telah dibaca (`sudah_dibaca = true`, `dibaca_pada = now()`). |
| `PUT` | `/notifikasi/baca-semua` | - | Menandai seluruh notifikasi anggota telah dibaca. |

### 8.2 Web Dashboard Pengurus (`routes/web.php` - Prefix: `/pengurus`)
Terproteksi `middleware(['auth', 'role:Pengurus'])`.

| Method | URI | Route Name | Action Controller | Deskripsi Tampilan & Logika |
|---|---|---|---|---|
| `GET` | `/pengurus/lahan` | `pengurus.lahan` | `Pengurus\LahanController@index` | Menampilkan ringkasan metrik dan tabel daftar lahan anggota kelompok dengan tab: *Semua*, *Menunggu Verifikasi*, *Terverifikasi*, *Perlu Perbaikan*. |
| `GET` | `/pengurus/lahan/{id}` | `pengurus.lahan.show` | `Pengurus\LahanController@show` | Menampilkan profil lahan, peta Leaflet koordinat petak, lightbox foto Cloudinary, serta timeline audit trail riwayat verifikasi. |
| `POST` | `/pengurus/lahan/{id}/verifikasi` | `pengurus.lahan.verifikasi` | `Pengurus\LahanController@verifikasi` | Menyetujui lahan: status berubah menjadi `terverifikasi`, mencatat log riwayat, dan men-dispatch notifikasi ke Anggota pemilik. |
| `POST` | `/pengurus/lahan/{id}/minta-perbaikan`| `pengurus.lahan.mintaPerbaikan`| `Pengurus\LahanController@mintaPerbaikan`| Meminta perbaikan / mencabut verifikasi: memvalidasi textarea `catatan_perbaikan` (wajib, min. 10 karakter), status berubah menjadi `perlu_perbaikan`, mencatat riwayat, dan men-dispatch notifikasi ke Anggota pemilik. |

---

## 9. Checklist Pengujian Menyeluruh (Testing Requirements)

Seluruh pengujian di bawah ini berstatus **BELUM DILAKUKAN** dan wajib dieksekusi secara ketat pada tahapan QA sebelum rilis produksi:

### A. Authorization & IDOR Protection
- [ ] Anggota A tidak dapat membaca data lahan milik Anggota B (**BELUM DILAKUKAN**)
- [ ] Anggota A tidak dapat mengubah/mengedit lahan milik Anggota B (**BELUM DILAKUKAN**)
- [ ] Anggota A tidak dapat menghapus lahan milik Anggota B (**BELUM DILAKUKAN**)
- [ ] Anggota tidak dapat mengakses data lahan dari kelompok tani lain (**BELUM DILAKUKAN**)
- [ ] Pengurus Kelompok A tidak dapat melihat atau memverifikasi lahan kelompok B (**BELUM DILAKUKAN**)
- [ ] Pengurus tidak dapat menambah data lahan baru (**BELUM DILAKUKAN**)
- [ ] Pengurus tidak dapat mengubah atribut fisik (luas, koordinat, komoditas) lahan anggota (**BELUM DILAKUKAN**)
- [ ] Pengurus tidak dapat menghapus lahan milik anggota (**BELUM DILAKUKAN**)
- [ ] Anggota tidak dapat memanipulasi status verifikasi secara langsung melalui request payload (**BELUM DILAKUKAN**)

### B. Verification Workflow & Lifecycle
- [ ] Pendaftaran lahan baru menghasilkan status `menunggu_verifikasi` dan revisi 1 (**BELUM DILAKUKAN**)
- [ ] Persetujuan oleh Ketua mengubah status menjadi `terverifikasi` dan mencatat verifikator (**BELUM DILAKUKAN**)
- [ ] Permintaan perbaikan tanpa mengisi alasan ditolak oleh validasi backend (**BELUM DILAKUKAN**)
- [ ] Permintaan perbaikan yang valid mengubah status menjadi `perlu_perbaikan` (**BELUM DILAKUKAN**)
- [ ] Pengiriman perbaikan oleh Anggota otomatis mereset status ke `menunggu_verifikasi` dan revisi bertambah (**BELUM DILAKUKAN**)
- [ ] Pengeditan pada lahan berstatus `terverifikasi` otomatis menggugurkan status kembali ke `menunggu_verifikasi` (**BELUM DILAKUKAN**)
- [ ] Pencabutan verifikasi oleh Pengurus pada lahan `terverifikasi` berhasil mengubah status ke `perlu_perbaikan` (**BELUM DILAKUKAN**)
- [ ] Setiap transisi status menghasilkan record baru pada `riwayat_verifikasi_lahan` (**BELUM DILAKUKAN**)

### C. Targeted Notification System
- [ ] Pendaftaran lahan baru HANYA mengirim notifikasi ke Ketua Kelompok terkait (**BELUM DILAKUKAN**)
- [ ] Pengajuan ulang perbaikan HANYA mengirim notifikasi ke Ketua Kelompok terkait (**BELUM DILAKUKAN**)
- [ ] Notifikasi tidak terkirim ganda ke Sekretaris, Bendahara, atau pengurus lainnya (**BELUM DILAKUKAN**)
- [ ] Permintaan perbaikan mengirim notifikasi secara langsung ke Anggota pemilik (**BELUM DILAKUKAN**)
- [ ] Persetujuan verifikasi mengirim notifikasi secara langsung ke Anggota pemilik (**BELUM DILAKUKAN**)
- [ ] Pengguna tidak dapat membaca notifikasi milik pengguna lain via API (**BELUM DILAKUKAN**)
- [ ] Ketiadaan Ketua Kelompok pada data kelompok tidak menyebabkan error 500, data lahan tetap tersimpan, dan error log tercatat (**BELUM DILAKUKAN**)

### D. Database Integrity & Media Lifecycle
- [ ] Seluruh foreign key (`id_pengguna`, `id_kelompok`, `id_komoditas`, dll) valid bertipe `VARCHAR(20)` (**BELUM DILAKUKAN**)
- [ ] Pendaftaran lahan bersamaan di detik yang sama tidak menghasilkan duplikasi ID (`SQLSTATE 23505` tertangani) (**BELUM DILAKUKAN**)
- [ ] Soft Delete pada lahan tidak menghapus atau merusak record pada `riwayat_verifikasi_lahan` (**BELUM DILAKUKAN**)
- [ ] Foreign key `id_lahan` pada tabel notifikasi berubah menjadi `NULL` jika lahan dibersihkan permanen (**BELUM DILAKUKAN**)
- [ ] Kegagalan transaksi database saat ganti foto tidak menghapus foto lama di Cloudinary (**BELUM DILAKUKAN**)
- [ ] Kegagalan penghapusan foto lama pasca-commit DB tidak membatalkan pembaruan data dan tercatat di antrean cleanup (**BELUM DILAKUKAN**)

---

## 10. Analisis Risiko & Batasan yang Masih Tersisa

1. **Anomali Integritas Data Kelompok Tanpa Ketua:**
   - *Kondisi:* Audit membuktikan terdapat kelompok yang belum memiliki Ketua.
   - *Mitigasi:* Backend memvalidasi ketiadaan Ketua sebagai *data anomaly*, mencatat error log, dan tidak membatalkan pendaftaran lahan anggota.
2. **Keterbatasan Sistem Notifikasi In-App:**
   - *Kondisi:* Belum mendukung *system tray push notification* saat aplikasi Flutter tertutup.
   - *Mitigasi:* Pengguna menerima notifikasi saat aplikasi dibuka atau di-refresh. Migrasi ke FCM dijadwalkan pada fase rilis v2.0.
3. **Hak Akses Admin / PPL yang Masih Tertunda:**
   - *Kondisi:* Belum ada ketetapan peran verifikasi dari Admin/PPL dari stakeholder.
   - *Mitigasi:* Seluruh hak akses Admin/PPL diparkir (*pending*) dan tidak diimplementasikan pada sprint pertama.

---

## 11. Status Kesiapan Dokumen

Dokumen Technical Design Versi 3.2 ini telah berstatus:  
**READY FOR FINAL REVIEW — WAITING FOR USER APPROVAL**

Tidak ada implementasi kode, pembuatan file migration, maupun modifikasi basis data yang dijalankan sebelum adanya persetujuan eksplisit dari pengguna.
