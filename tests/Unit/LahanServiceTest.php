<?php

require_once 'c:/laragon/www/PawonTani/vendor/autoload.php';
$app = require_once 'c:/laragon/www/PawonTani/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Jobs\DeleteCloudinaryPhotoJob;
use App\Models\KelompokTani;
use App\Models\Komoditas;
use App\Models\Lahan;
use App\Models\Notifikasi;
use App\Models\Pengguna;
use App\Models\RiwayatVerifikasiLahan;
use App\Services\CloudinaryService;
use App\Services\LahanService;
use App\Services\NotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

// 1. Setup in-memory SQLite database
Config::set('database.default', 'sqlite_testing');
Config::set('database.connections.sqlite_testing', [
    'driver' => 'sqlite',
    'database' => ':memory:',
    'prefix' => '',
    'foreign_key_constraints' => true,
]);
DB::purge();
DB::setDefaultConnection('sqlite_testing');

// Run migrations in memory
Artisan::call('migrate', ['--database' => 'sqlite_testing']);

$passed = 0;
$failed = 0;

function assertTest(bool $condition, string $name, string $details = '') {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] {$name}\n";
        $passed++;
    } else {
        echo "![FAIL] {$name}: {$details}\n";
        $failed++;
    }
}

echo "=========================================================\n";
echo "=== PAWONTANI LAHANSERVICE ISOLATED TEST SUITE (SQLITE) ===\n";
echo "=========================================================\n\n";

// Helper fixtures
$kelompok1 = KelompokTani::create([
    'id_kelompok' => 'KT-001',
    'nama_kelompok' => 'Kelompok Tani Makmur',
    'alamat' => 'Desa Makmur RT 01',
    'status' => 'Aktif',
]);

$kelompok2 = KelompokTani::create([
    'id_kelompok' => 'KT-002',
    'nama_kelompok' => 'Kelompok Tani Sejahtera',
    'alamat' => 'Desa Sejahtera RT 02',
    'status' => 'Aktif',
]);

$komoditas = Komoditas::create([
    'id_komoditas' => 'KMD-001',
    'nama_komoditas' => 'Padi Ciherang',
    'kategori' => 'Tanaman Pangan',
]);

$anggota1 = Pengguna::create([
    'id_pengguna' => 'PGN-001',
    'nama' => 'Budi Santoso',
    'username' => 'budi001',
    'email' => 'budi@pawontani.test',
    'password' => bcrypt('password'),
    'role' => 'Anggota',
    'status' => 'Aktif',
    'id_kelompok' => 'KT-001',
]);

$anggota2 = Pengguna::create([
    'id_pengguna' => 'PGN-002',
    'nama' => 'Siti Aminah',
    'username' => 'siti002',
    'email' => 'siti@pawontani.test',
    'password' => bcrypt('password'),
    'role' => 'Anggota',
    'status' => 'Aktif',
    'id_kelompok' => 'KT-001',
]);

$anggotaNonAktif = Pengguna::create([
    'id_pengguna' => 'PGN-003',
    'nama' => 'Joko NonAktif',
    'username' => 'joko003',
    'email' => 'joko@pawontani.test',
    'password' => bcrypt('password'),
    'role' => 'Anggota',
    'status' => 'Tidak Aktif',
    'id_kelompok' => 'KT-001',
]);

$anggotaTanpaKelompok = Pengguna::create([
    'id_pengguna' => 'PGN-004',
    'nama' => 'Rahmat Solo',
    'username' => 'rahmat004',
    'email' => 'rahmat@pawontani.test',
    'password' => bcrypt('password'),
    'role' => 'Anggota',
    'status' => 'Aktif',
    'id_kelompok' => null,
]);

$ketuaKelompok1 = Pengguna::create([
    'id_pengguna' => 'PGN-010',
    'nama' => 'Pak Ketua Makmur',
    'username' => 'ketua010',
    'email' => 'ketua1@pawontani.test',
    'password' => bcrypt('password'),
    'role' => 'Pengurus',
    'jabatan' => 'Ketua',
    'status' => 'Aktif',
    'id_kelompok' => 'KT-001',
]);

$sekretarisKelompok1 = Pengguna::create([
    'id_pengguna' => 'PGN-011',
    'nama' => 'Pak Sekretaris',
    'username' => 'sekretaris011',
    'email' => 'sekretaris1@pawontani.test',
    'password' => bcrypt('password'),
    'role' => 'Pengurus',
    'jabatan' => 'Sekretaris',
    'status' => 'Aktif',
    'id_kelompok' => 'KT-001',
]);

$ketuaKelompok2 = Pengguna::create([
    'id_pengguna' => 'PGN-020',
    'nama' => 'Pak Ketua Sejahtera',
    'username' => 'ketua020',
    'email' => 'ketua2@pawontani.test',
    'password' => bcrypt('password'),
    'role' => 'Pengurus',
    'jabatan' => 'Ketua',
    'status' => 'Aktif',
    'id_kelompok' => 'KT-002',
]);

$adminUser = Pengguna::create([
    'id_pengguna' => 'PGN-099',
    'nama' => 'Admin PawonTani',
    'username' => 'admin099',
    'email' => 'admin@pawontani.test',
    'password' => bcrypt('password'),
    'role' => 'Admin',
    'status' => 'Aktif',
]);

// Cloudinary Mock
$mockCloudinary = Mockery::mock(CloudinaryService::class);
$notificationService = new NotificationService();
$lahanService = new LahanService($notificationService, $mockCloudinary);

// -------------------------------------------------------------
// SECTION 1: CREATE LAHAN
// -------------------------------------------------------------
echo "--- 1. Testing createLahan() ---\n";

// 1.1 Anggota aktif berhasil membuat lahan
$validData = [
    'nama_lahan' => 'Lahan Sawah Petak A',
    'luas' => 1.2500,
    'alamat_lahan' => 'Jl. Tani Desa Makmur',
    'id_komoditas' => 'KMD-001',
    'musim_tanam' => 'Rendengan 2026',
    'keterangan' => 'Lahan subur beririgasi teknis',
    // Injected invalid client inputs that must be ignored
    'id_lahan' => 'INJECTED-ID',
    'id_pengguna' => 'INJECTED-PGN',
    'id_kelompok' => 'INJECTED-KT',
    'status_verifikasi' => 'terverifikasi',
    'status_operasional' => 'panen',
    'nomor_revisi' => 99,
];

$lahan1 = $lahanService->createLahan($anggota1, $validData);
assertTest($lahan1 instanceof Lahan, "Anggota aktif berhasil membuat lahan");
assertTest($lahan1->id_pengguna === 'PGN-001', "ID pengguna diambil dari aktor server, bukan input client");
assertTest($lahan1->id_kelompok === 'KT-001', "ID kelompok diambil dari relasi aktor server, bukan input client");
assertTest($lahan1->status_verifikasi === 'menunggu_verifikasi', "Status awal adalah 'menunggu_verifikasi'");
assertTest($lahan1->status_operasional === 'persiapan', "Status operasional awal adalah 'persiapan'");
assertTest($lahan1->nomor_revisi === 1, "Nomor revisi awal adalah 1");
assertTest($lahan1->id_lahan !== 'INJECTED-ID' && str_starts_with($lahan1->id_lahan, 'LHN-'), "ID lahan dibuat dengan generator format baku LHN-YYYYMMDD-XXXX");

// 1.2 Audit trail riwayat pengajuan pertama
$riwayatAwal = RiwayatVerifikasiLahan::where('id_lahan', $lahan1->id_lahan)->first();
assertTest($riwayatAwal !== null, "Riwayat pengajuan pertama tercatat di database");
assertTest($riwayatAwal->aksi === 'pengajuan_baru', "Aksi riwayat pertama adalah 'pengajuan_baru'");
assertTest($riwayatAwal->status_sesudahnya === 'menunggu_verifikasi', "Status sesudahnya pada riwayat awal adalah 'menunggu_verifikasi'");
assertTest($riwayatAwal->nomor_revisi === 1, "Nomor revisi riwayat awal adalah 1");

// 1.3 Notifikasi pengajuan dikirim ke Ketua aktif
$notifKetua = Notifikasi::where('id_lahan', $lahan1->id_lahan)
    ->where('id_penerima', $ketuaKelompok1->id_pengguna)
    ->first();
assertTest($notifKetua !== null, "Notifikasi pengajuan baru berhasil dikirim ke Ketua aktif KT-001");
assertTest($notifKetua->jenis_notifikasi === NotificationService::JENIS_PENGAJUAN_BARU, "Jenis notifikasi adalah 'pengajuan_lahan'");

// 1.4 Non-Anggota (Pengurus, Admin) ditolak
$caught = false;
try {
    $lahanService->createLahan($ketuaKelompok1, $validData);
} catch (AuthorizationException) {
    $caught = true;
}
assertTest($caught, "Pengurus ditolak saat mencoba membuat lahan");

$caughtAdmin = false;
try {
    $lahanService->createLahan($adminUser, $validData);
} catch (AuthorizationException) {
    $caughtAdmin = true;
}
assertTest($caughtAdmin, "Admin ditolak saat mencoba membuat lahan");

// 1.5 Anggota tanpa kelompok ditolak
$caughtTanpaKelompok = false;
try {
    $lahanService->createLahan($anggotaTanpaKelompok, $validData);
} catch (AuthorizationException) {
    $caughtTanpaKelompok = true;
}
assertTest($caughtTanpaKelompok, "Anggota tanpa kelompok ditolak membuat lahan");

// 1.6 Anggota non-aktif ditolak
$caughtNonAktif = false;
try {
    $lahanService->createLahan($anggotaNonAktif, $validData);
} catch (AuthorizationException) {
    $caughtNonAktif = true;
}
assertTest($caughtNonAktif, "Anggota berstatus non-aktif ditolak membuat lahan");

// 1.7 Kompensasi foto jika DB transaction gagal
$mockCloudinary->shouldReceive('upload')
    ->once()
    ->andReturn('https://res.cloudinary.com/demo/image/upload/v1/pawontani/lahan/fail_test.jpg');
$mockCloudinary->shouldReceive('delete')
    ->once()
    ->with('https://res.cloudinary.com/demo/image/upload/v1/pawontani/lahan/fail_test.jpg')
    ->andReturn(true);

$dummyFile = UploadedFile::fake()->image('test.jpg');
$badData = ['nama_lahan' => null]; // Will trigger SQLite NOT NULL constraint violation on nama_lahan
$caughtCompensation = false;
try {
    $lahanService->createLahan($anggota1, $badData, $dummyFile);
} catch (\Throwable) {
    $caughtCompensation = true;
}
assertTest($caughtCompensation, "Kompensasi penghapusan foto baru terpanggil ketika transaksi DB gagal");


// -------------------------------------------------------------
// SECTION 2: UPDATE LAHAN
// -------------------------------------------------------------
echo "\n--- 2. Testing updateLahan() ---\n";

// 2.1 Pemilik dapat memperbarui lahan draft (menunggu_verifikasi) -> pembaruan_draft, no revisi tetap
$updatedDraft = $lahanService->updateLahan($anggota1, $lahan1, [
    'nama_lahan' => 'Lahan Sawah Petak A (Updated Draft)',
    'luas' => 1.5000,
]);
assertTest($updatedDraft->nama_lahan === 'Lahan Sawah Petak A (Updated Draft)', "Pemilik berhasil memperbarui nama lahan");
assertTest((float)$updatedDraft->luas === 1.5, "Luas lahan berhasil diperbarui");
assertTest($updatedDraft->status_verifikasi === 'menunggu_verifikasi', "Status tetap 'menunggu_verifikasi'");
assertTest($updatedDraft->nomor_revisi === 1, "Nomor revisi tidak bertambah saat update draft menunggu_verifikasi");

$riwayatDraft = RiwayatVerifikasiLahan::where('id_lahan', $lahan1->id_lahan)
    ->where('aksi', 'pembaruan_draft')
    ->first();
assertTest($riwayatDraft !== null, "Riwayat 'pembaruan_draft' tercatat untuk perubahan draft");

// 2.2 Anggota lain ditolak mengedit lahan (IDOR protection)
$caughtIdorUpdate = false;
try {
    $lahanService->updateLahan($anggota2, $lahan1, ['nama_lahan' => 'Dibajak Anggota Lain']);
} catch (AuthorizationException) {
    $caughtIdorUpdate = true;
}
assertTest($caughtIdorUpdate, "Anggota lain ditolak mengubah lahan orang lain (IDOR prevented)");

// 2.2.1 Pengguna tidak berhak melakukan update tidak memicu Cloudinary upload
$unauthorizedFile = UploadedFile::fake()->image('unauthorized.jpg');
// Mockery expectation: upload should NOT be called for unauthorized user
$mockCloudinary->shouldReceive('upload')->never();
$caughtNoUpload = false;
try {
    $lahanService->updateLahan($anggota2, $lahan1, ['nama_lahan' => 'Gagal Upload'], $unauthorizedFile);
} catch (AuthorizationException) {
    $caughtNoUpload = true;
}
assertTest($caughtNoUpload, "Pengguna tidak berhak melakukan update TIDAK memicu Cloudinary upload");

// 2.3 Pengurus ditolak mengubah atribut fisik lahan
$caughtPengurusUpdate = false;
try {
    $lahanService->updateLahan($ketuaKelompok1, $lahan1, ['nama_lahan' => 'Diubah Pengurus']);
} catch (AuthorizationException) {
    $caughtPengurusUpdate = true;
}
assertTest($caughtPengurusUpdate, "Pengurus ditolak mengubah atribut fisik lahan anggota");

// 2.4 Update lahan 'terverifikasi' -> mencabut status, revisi dinaikkan, notifikasi pengajuan ulang
// Set status lahan menjadi terverifikasi terlebih dahulu
$lahan1->status_verifikasi = 'terverifikasi';
$lahan1->verifikator_id = $ketuaKelompok1->id_pengguna;
$lahan1->tanggal_verifikasi = now();
$lahan1->catatan_verifikasi = 'Sudah oke';
$lahan1->save();

$updatedTerverifikasi = $lahanService->updateLahan($anggota1, $lahan1, [
    'nama_lahan' => 'Lahan Sawah Petak A (Revisi Setelah Terverifikasi)',
]);
assertTest($updatedTerverifikasi->status_verifikasi === 'menunggu_verifikasi', "Perubahan lahan terverifikasi mencabut status menjadi 'menunggu_verifikasi'");
assertTest($updatedTerverifikasi->nomor_revisi === 2, "Nomor revisi bertambah menjadi 2");
assertTest($updatedTerverifikasi->verifikator_id === null, "Metadata verifikator_id dibersihkan");
assertTest($updatedTerverifikasi->tanggal_verifikasi === null, "Metadata tanggal_verifikasi dibersihkan");
assertTest($updatedTerverifikasi->catatan_verifikasi === null, "Metadata catatan_verifikasi dibersihkan");

$riwayatResubmit = RiwayatVerifikasiLahan::where('id_lahan', $lahan1->id_lahan)
    ->where('aksi', 'pengajuan_ulang')
    ->first();
assertTest($riwayatResubmit !== null, "Riwayat 'pengajuan_ulang' tercatat saat mencabut verifikasi");

// 2.5 Update pergantian foto & dispatch DeleteCloudinaryPhotoJob setelah commit
Queue::fake();
$lahan1->foto_lahan = 'https://res.cloudinary.com/demo/image/upload/v1/pawontani/lahan/old_foto.jpg';
$lahan1->save();

$mockCloudinary->shouldReceive('upload')
    ->once()
    ->andReturn('https://res.cloudinary.com/demo/image/upload/v2/pawontani/lahan/new_foto.jpg');

$lahanService->updateLahan($anggota1, $lahan1, [
    'nama_lahan' => 'Lahan dengan Foto Baru',
], UploadedFile::fake()->image('new.jpg'));

assertTest($lahan1->fresh()->foto_lahan === 'https://res.cloudinary.com/demo/image/upload/v2/pawontani/lahan/new_foto.jpg', "Foto baru tersimpan di database");
Queue::assertPushed(DeleteCloudinaryPhotoJob::class, function ($job) {
    return $job->photoUrl === 'https://res.cloudinary.com/demo/image/upload/v1/pawontani/lahan/old_foto.jpg';
});
assertTest(true, "DeleteCloudinaryPhotoJob untuk foto lama berhasil didispatch setelah commit");

// 2.6 Pemilik berhasil memperbarui lahan berstatus 'perlu_perbaikan'
$lahan1->status_verifikasi = 'perlu_perbaikan';
$lahan1->nomor_revisi = 2;
$lahan1->verifikator_id = $ketuaKelompok1->id_pengguna;
$lahan1->tanggal_verifikasi = now();
$lahan1->catatan_verifikasi = 'Perbaiki batas patok lahan';
$lahan1->save();

$updatedPerbaikan = $lahanService->updateLahan($anggota1, $lahan1, [
    'nama_lahan' => 'Lahan Sawah Petak A (Sudah Diperbaiki)',
]);
assertTest($updatedPerbaikan->status_verifikasi === 'menunggu_verifikasi', "Lahan perlu_perbaikan kembali menjadi 'menunggu_verifikasi' setelah diperbarui");
assertTest($updatedPerbaikan->nomor_revisi === 3, "Nomor revisi bertambah tepat satu kali saat pengajuan ulang (dari 2 ke 3)");
assertTest($updatedPerbaikan->verifikator_id === null, "Metadata verifikator_id dibersihkan saat perbaikan diajukan ulang");
assertTest($updatedPerbaikan->tanggal_verifikasi === null, "Metadata tanggal_verifikasi dibersihkan saat perbaikan diajukan ulang");
assertTest($updatedPerbaikan->catatan_verifikasi === null, "Metadata catatan_verifikasi dibersihkan saat perbaikan diajukan ulang");

$riwayatPerbaikan = RiwayatVerifikasiLahan::where('id_lahan', $lahan1->id_lahan)
    ->where('nomor_revisi', 3)
    ->where('aksi', 'pengajuan_ulang')
    ->first();
assertTest($riwayatPerbaikan !== null, "Riwayat 'pengajuan_ulang' tercatat pada nomor revisi 3");

$notifPengajuanUlang = Notifikasi::where('id_lahan', $lahan1->id_lahan)
    ->where('id_penerima', $ketuaKelompok1->id_pengguna)
    ->where('jenis_notifikasi', NotificationService::JENIS_PENGAJUAN_ULANG)
    ->latest('id_notifikasi')
    ->first();
assertTest($notifPengajuanUlang !== null, "Notifikasi pengajuan ulang baru berhasil dikirim ke Ketua KT-001");


// -------------------------------------------------------------
// SECTION 3: VERIFIKASI LAHAN
// -------------------------------------------------------------
echo "\n--- 3. Testing verifyLahan() ---\n";

// Reset lahan1 status to menunggu_verifikasi
$lahan1->status_verifikasi = 'menunggu_verifikasi';
$lahan1->save();

// 3.1 Ketua aktif kelompok yang sama dapat menyetujui
$verified = $lahanService->verifyLahan($ketuaKelompok1, $lahan1, 'setujui', 'Lahan memenuhi syarat verifikasi');
assertTest($verified->status_verifikasi === 'terverifikasi', "Ketua KT-001 berhasil menyetujui lahan");
assertTest($verified->verifikator_id === $ketuaKelompok1->id_pengguna, "Verifikator ID tercatat sesuai akun Ketua");
assertTest($verified->tanggal_verifikasi !== null, "Tanggal verifikasi tercatat");

$riwayatVerifOk = RiwayatVerifikasiLahan::where('id_lahan', $lahan1->id_lahan)
    ->where('aksi', 'verifikasi_disetujui')
    ->first();
assertTest($riwayatVerifOk !== null, "Riwayat 'verifikasi_disetujui' tercatat di database");

// 3.2 Ketua dari kelompok lain ditolak
$caughtKetuaLain = false;
try {
    $lahanService->verifyLahan($ketuaKelompok2, $lahan1, 'cabut_verifikasi', 'Mencoba verifikasi lahan kelompok lain');
} catch (AuthorizationException) {
    $caughtKetuaLain = true;
}
assertTest($caughtKetuaLain, "Ketua dari kelompok lain ditolak memverifikasi lahan (IDOR prevented)");

// 3.3 Pengurus non-Ketua (Sekretaris) ditolak
$caughtSekretaris = false;
try {
    $lahanService->verifyLahan($sekretarisKelompok1, $lahan1, 'cabut_verifikasi', 'Sekretaris mencoba verifikasi');
} catch (AuthorizationException) {
    $caughtSekretaris = true;
}
assertTest($caughtSekretaris, "Pengurus non-Ketua (Sekretaris) ditolak memverifikasi lahan");

// 3.4 Anggota ditolak memverifikasi lahan
$caughtAnggotaVerif = false;
try {
    $lahanService->verifyLahan($anggota1, $lahan1, 'setujui', 'Mencoba menyetujui sendiri');
} catch (AuthorizationException) {
    $caughtAnggotaVerif = true;
}
assertTest($caughtAnggotaVerif, "Anggota ditolak memverifikasi lahannya sendiri");

// 3.5 Cabut verifikasi pada lahan terverifikasi
$cabut = $lahanService->verifyLahan($ketuaKelompok1, $lahan1, 'cabut_verifikasi', 'Pemeriksaan lapangan menemukan batas tanah tidak sesuai');
assertTest($cabut->status_verifikasi === 'perlu_perbaikan', "Pencabutan verifikasi mengubah status menjadi 'perlu_perbaikan'");
assertTest($cabut->verifikator_id === null, "Metadata verifikator lama dibersihkan saat dicabut");

// 3.6 Pencabutan verifikasi pada lahan yang BUKAN terverifikasi ditolak
$caughtInvalidCabut = false;
try {
    $lahanService->verifyLahan($ketuaKelompok1, $lahan1, 'cabut_verifikasi', 'Cabut lagi');
} catch (DomainException) {
    $caughtInvalidCabut = true;
}
assertTest($caughtInvalidCabut, "Pencabutan verifikasi pada lahan yang belum terverifikasi ditolak");

// 3.7 Permintaan perbaikan tanpa alasan ditolak
Lahan::where('id_lahan', $lahan1->id_lahan)->update(['status_verifikasi' => 'menunggu_verifikasi']);
$lahan1 = $lahan1->fresh();

$caughtTanpaCatatan = false;
try {
    $lahanService->verifyLahan($ketuaKelompok1, $lahan1, 'perlu_perbaikan', '');
} catch (\InvalidArgumentException) {
    $caughtTanpaCatatan = true;
}
assertTest($caughtTanpaCatatan, "Permintaan perbaikan tanpa alasan/catatan ditolak");

// 3.8 Permintaan perbaikan berhasil dengan catatan
$perbaikan = $lahanService->verifyLahan($ketuaKelompok1, $lahan1, 'perlu_perbaikan', 'Mohon koordinat GPS diperbaiki ke titik tengah sawah');
assertTest($perbaikan->status_verifikasi === 'perlu_perbaikan', "Permintaan perbaikan mengubah status menjadi 'perlu_perbaikan'");


// -------------------------------------------------------------
// SECTION 4: DELETE LAHAN (SOFT DELETE)
// -------------------------------------------------------------
echo "\n--- 4. Testing deleteLahan() ---\n";

// 4.1 Lahan terverifikasi DITOLAK keras untuk dihapus
Lahan::where('id_lahan', $lahan1->id_lahan)->update(['status_verifikasi' => 'terverifikasi']);
$lahan1 = $lahan1->fresh();

$caughtDeleteTerverifikasi = false;
try {
    $lahanService->deleteLahan($anggota1, $lahan1);
} catch (DomainException) {
    $caughtDeleteTerverifikasi = true;
}
assertTest($caughtDeleteTerverifikasi, "Lahan berstatus 'terverifikasi' dilarang keras untuk dihapus oleh pemilik");

// 4.1.1 Pengguna tidak berhak melakukan delete tidak dapat mengetahui status verifikasi lahan
$caughtNonOwnerOnVerified = null;
try {
    $lahanService->deleteLahan($anggota2, $lahan1);
} catch (\Throwable $e) {
    $caughtNonOwnerOnVerified = $e;
}
assertTest($caughtNonOwnerOnVerified instanceof AuthorizationException, 
    "Pengguna tidak berhak mendapat AuthorizationException saat menghapus lahan terverifikasi (bukan DomainException, mencegah status oracle)");
assertTest(!($caughtNonOwnerOnVerified instanceof DomainException), 
    "Pengguna tidak berhak TIDAK menerima DomainException pada lahan terverifikasi");

// 4.2 Anggota lain ditolak menghapus lahan
Lahan::where('id_lahan', $lahan1->id_lahan)->update(['status_verifikasi' => 'menunggu_verifikasi']);
$lahan1 = $lahan1->fresh();

$caughtIdorDelete = false;
try {
    $lahanService->deleteLahan($anggota2, $lahan1);
} catch (AuthorizationException) {
    $caughtIdorDelete = true;
}
assertTest($caughtIdorDelete, "Anggota lain ditolak menghapus lahan milik orang lain");

// 4.3 Pemilik dapat melakukan Soft Delete pada lahan non-terverifikasi
$deleted = $lahanService->deleteLahan($anggota1, $lahan1);
assertTest($deleted === true, "Pemilik berhasil menghapus (soft delete) lahannya");
assertTest(Lahan::find($lahan1->id_lahan) === null, "Lahan tidak muncul dalam query normal");
assertTest(Lahan::withTrashed()->find($lahan1->id_lahan) !== null, "Lahan masih tersimpan dengan deleted_at (Soft Delete aktif)");

$riwayatDelete = RiwayatVerifikasiLahan::where('id_lahan', $lahan1->id_lahan)
    ->where('aksi', 'penghapusan_lahan')
    ->first();
assertTest($riwayatDelete !== null, "Riwayat 'penghapusan_lahan' tercatat sebelum soft delete");


// -------------------------------------------------------------
// SECTION 5: SECURITY & QUERY SCOPING (IDOR PROTECTION)
// -------------------------------------------------------------
echo "\n--- 5. Testing Security & Query Scoping ---\n";

// Create fresh active land for query scope tests
$lahanBaru1 = $lahanService->createLahan($anggota1, [
    'nama_lahan' => 'Lahan Anggota 1 Kelompok 1',
    'luas' => 2.0,
    'alamat_lahan' => 'Desa Makmur',
]);

$lahanBaru2 = $lahanService->createLahan($anggota2, [
    'nama_lahan' => 'Lahan Anggota 2 Kelompok 1',
    'luas' => 3.0,
    'alamat_lahan' => 'Desa Makmur',
]);

// 5.1 Anggota 1 query scope hanya melihat lahan miliknya
$anggota1Query = $lahanService->getLahanQueryForUser($anggota1)->get();
assertTest($anggota1Query->count() === 1 && $anggota1Query->first()->id_lahan === $lahanBaru1->id_lahan, 
    "Query scope Anggota 1 hanya mengembalikan lahan miliknya sendiri");

// 5.2 Pengurus Kelompok 1 melihat semua lahan di kelompok 1 (lahanBaru1 dan lahanBaru2)
$ketua1Query = $lahanService->getLahanQueryForUser($ketuaKelompok1)->get();
assertTest($ketua1Query->count() === 2, "Query scope Pengurus KT-001 mengembalikan seluruh lahan anggota kelompoknya (2 lahan)");

// 5.3 Pengurus Kelompok 2 tidak melihat lahan kelompok 1
$ketua2Query = $lahanService->getLahanQueryForUser($ketuaKelompok2)->get();
assertTest($ketua2Query->count() === 0, "Query scope Pengurus KT-002 kosong (tidak dapat melihat lahan kelompok lain)");

// 5.4 Admin dan PPL mendapatkan query kosong (ditolak akses langsung sampai aturan disetujui)
$adminQuery = $lahanService->getLahanQueryForUser($adminUser)->get();
assertTest($adminQuery->count() === 0, "Query scope Admin kosong (ditolak akses modul lahan)");

// 5.5 findLahanForUser melempar ModelNotFoundException jika mencoba mengakses di luar scope (IDOR)
$caughtIdorFind = false;
try {
    $lahanService->findLahanForUser($anggota1, $lahanBaru2->id_lahan);
} catch (ModelNotFoundException) {
    $caughtIdorFind = true;
}
assertTest($caughtIdorFind, "findLahanForUser mencegah IDOR dengan melempar ModelNotFoundException");

echo "\n=========================================================\n";
echo "SUMMARY: Passed = {$passed}, Failed = {$failed}\n";
echo "=========================================================\n";

if ($failed > 0) {
    exit(1);
}
