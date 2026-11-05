<?php

require_once 'c:/laragon/www/PawonTani/vendor/autoload.php';
$app = require_once 'c:/laragon/www/PawonTani/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Resources\LahanResource;
use App\Http\Resources\RiwayatVerifikasiLahanResource;
use App\Models\KelompokTani;
use App\Models\Komoditas;
use App\Models\Lahan;
use App\Models\Pengguna;
use App\Models\RiwayatVerifikasiLahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

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
echo "=== PAWONTANI LAHANRESOURCE ISOLATED TEST SUITE (SQLITE) ===\n";
echo "=========================================================\n\n";

// Seed data testing
$poktan = KelompokTani::create([
    'id_kelompok' => 'KT-001',
    'nama_kelompok' => 'Kelompok Tani Maju',
    'alamat' => 'Desa Sukamaju',
    'status' => 'Aktif',
]);

$komoditas = Komoditas::create([
    'id_komoditas' => 'KMD-001',
    'nama_komoditas' => 'Padi Ciherang',
    'kategori' => 'Pangan',
    'gambar' => 'https://res.cloudinary.com/demo/padi.jpg',
]);

$anggota = Pengguna::create([
    'id_pengguna' => 'USR-001',
    'nama' => 'Pak Tani Joko',
    'username' => 'joko_tani',
    'role' => 'Anggota',
    'status' => 'Aktif',
    'id_kelompok' => 'KT-001',
    'nik' => '3302000000000001',
    'email' => 'joko@gmail.com',
    'password' => 'secret_hash',
    'no_telepon' => '081234567890',
]);

$ketua = Pengguna::create([
    'id_pengguna' => 'USR-002',
    'nama' => 'Pak Ketua Budi',
    'username' => 'budi_ketua',
    'role' => 'Pengurus',
    'status' => 'Aktif',
    'jabatan' => 'Ketua',
    'id_kelompok' => 'KT-001',
    'nik' => '3302000000000002',
    'email' => 'budi@gmail.com',
    'password' => 'secret_hash',
    'no_telepon' => '081234567891',
]);

$anggotaLain = Pengguna::create([
    'id_pengguna' => 'USR-003',
    'nama' => 'Pak Tani Santoso',
    'username' => 'santoso_tani',
    'role' => 'Anggota',
    'status' => 'Aktif',
    'id_kelompok' => 'KT-001',
    'password' => 'secret_hash',
]);

$lahanDraft = Lahan::create([
    'id_lahan' => 'LHN-20261103-ABCD',
    'id_pengguna' => 'USR-001',
    'id_kelompok' => 'KT-001',
    'id_komoditas' => 'KMD-001',
    'nama_lahan' => 'Petak Sawah Barat',
    'luas' => 1.2500,
    'provinsi_id' => '33',
    'kabupaten_id' => '3302',
    'kecamatan_id' => '330201',
    'desa_id' => '3302012001',
    'alamat_lahan' => 'Jl. Sawah Indah No. 12',
    'latitude' => -7.4243120,
    'longitude' => 109.2301450,
    'musim_tanam' => 'Musim Hujan 2026',
    'foto_lahan' => 'https://res.cloudinary.com/demo/sawah.jpg',
    'keterangan' => 'Lahan subur dekat irigasi',
    'status_operasional' => 'persiapan',
    'status_verifikasi' => 'menunggu_verifikasi',
    'nomor_revisi' => 1,
]);

$riwayat = RiwayatVerifikasiLahan::create([
    'id_lahan' => $lahanDraft->id_lahan,
    'id_pengguna' => 'USR-001',
    'aksi' => 'pengajuan_baru',
    'status_sebelumnya' => null,
    'status_sesudahnya' => 'menunggu_verifikasi',
    'catatan' => 'Pengajuan lahan pertama kali',
    'nomor_revisi' => 1,
    'snapshot_data' => ['secret_system_data' => 'DO_NOT_EXPOSE', 'raw_dump' => true],
    'created_at' => now(),
]);

echo "--- 1. Testing RiwayatVerifikasiLahanResource ---\n";
// Request mock
$reqAnggota = Request::create('/api/v1/lahan', 'GET');
$reqAnggota->setUserResolver(fn () => $anggota);

// Test tanpa eager loading relasi pengguna (menggunakan resolve() untuk mengevaluasi MissingValue)
$riwayatResourceUnloaded = (new RiwayatVerifikasiLahanResource($riwayat))->resolve($reqAnggota);
assertTest($riwayatResourceUnloaded['id'] === $riwayat->id_riwayat, "Resource memiliki alias field 'id'");
assertTest($riwayatResourceUnloaded['id_riwayat'] === $riwayat->id_riwayat, "Resource memiliki field baku 'id_riwayat'");
assertTest($riwayatResourceUnloaded['id_lahan'] === 'LHN-20261103-ABCD', "ID lahan sesuai");
assertTest($riwayatResourceUnloaded['aksi'] === 'pengajuan_baru', "Aksi audit trail sesuai");
assertTest($riwayatResourceUnloaded['nomor_revisi'] === 1, "Nomor revisi berupa integer");
assertTest(!array_key_exists('snapshot_data', $riwayatResourceUnloaded), "snapshot_data TIDAK diekspos di Resource (Keamanan payload)");
assertTest(!array_key_exists('aktor', $riwayatResourceUnloaded), "Relasi aktor tidak dimuat jika pengguna belum dieager-load (whenLoaded aktif, anti N+1)");

// Test dengan eager loading relasi pengguna
$riwayatWithUser = RiwayatVerifikasiLahan::with('pengguna')->find($riwayat->id_riwayat);
$riwayatResourceLoaded = (new RiwayatVerifikasiLahanResource($riwayatWithUser))->resolve($reqAnggota);
assertTest(isset($riwayatResourceLoaded['aktor']), "Relasi aktor tampil ketika pengguna dieager-load");
assertTest($riwayatResourceLoaded['aktor']['nama'] === 'Pak Tani Joko', "Nama aktor tampil akurat");
assertTest(!array_key_exists('password', $riwayatResourceLoaded['aktor']), "Password hash aktor TIDAK bocor");
assertTest(!array_key_exists('nik', $riwayatResourceLoaded['aktor']), "NIK aktor TIDAK bocor");
assertTest(!array_key_exists('no_telepon', $riwayatResourceLoaded['aktor']), "No telepon aktor TIDAK bocor");

echo "\n--- 2. Testing LahanResource (Field Baku, Alias Flutter, Tipe Data) ---\n";
// Lahan tanpa eager loading (menggunakan resolve() untuk mengevaluasi output JSON aktual)
$lahanResourceUnloaded = (new LahanResource($lahanDraft))->resolve($reqAnggota);

assertTest($lahanResourceUnloaded['id'] === 'LHN-20261103-ABCD', "Alias Flutter 'id' tersedia");
assertTest($lahanResourceUnloaded['id_lahan'] === 'LHN-20261103-ABCD', "Field baku 'id_lahan' tersedia");
assertTest($lahanResourceUnloaded['nama'] === 'Petak Sawah Barat', "Alias Flutter 'nama' tersedia");
assertTest($lahanResourceUnloaded['nama_lahan'] === 'Petak Sawah Barat', "Field baku 'nama_lahan' tersedia");
assertTest(is_float($lahanResourceUnloaded['luas']) && $lahanResourceUnloaded['luas'] === 1.25, "Luas dikonversi menjadi numeric/float (1.25)");
assertTest($lahanResourceUnloaded['luas_formatted'] === '1,25 ha', "Format teks luas_formatted rapi ('1,25 ha')");

assertTest(is_string($lahanResourceUnloaded['lokasi']) && $lahanResourceUnloaded['lokasi'] === 'Jl. Sawah Indah No. 12', "Alias Flutter 'lokasi' berupa string alamat");
assertTest(is_array($lahanResourceUnloaded['detail_lokasi']), "Struktur detail_lokasi berupa objek terstruktur");
assertTest($lahanResourceUnloaded['detail_lokasi']['alamat_lahan'] === 'Jl. Sawah Indah No. 12', "Alamat lahan sesuai pada detail_lokasi");
assertTest(is_float($lahanResourceUnloaded['detail_lokasi']['latitude']), "Latitude berupa float pada detail_lokasi");
assertTest(is_float($lahanResourceUnloaded['detail_lokasi']['longitude']), "Longitude berupa float pada detail_lokasi");
assertTest(is_float($lahanResourceUnloaded['latitude']) && is_float($lahanResourceUnloaded['longitude']), "Top-level latitude dan longitude berupa float");
assertTest($lahanResourceUnloaded['status'] === 'persiapan', "Alias Flutter 'status' operasional string sesuai");

assertTest($lahanResourceUnloaded['status_operasional'] === 'persiapan', "Status operasional read-only tersedia");
assertTest($lahanResourceUnloaded['status_verifikasi'] === 'menunggu_verifikasi', "Status verifikasi read-only tersedia");
assertTest($lahanResourceUnloaded['nomor_revisi'] === 1, "Nomor revisi integer");

echo "\n--- 3. Testing whenLoaded() pada LahanResource (Anti N+1 Query) ---\n";
assertTest(!array_key_exists('komoditas', $lahanResourceUnloaded), "Relasi komoditas TIDAK dimuat jika un-eager loaded");
assertTest(!array_key_exists('verifikator', $lahanResourceUnloaded), "Relasi verifikator TIDAK dimuat jika un-eager loaded");
assertTest(!array_key_exists('kelompok_tani', $lahanResourceUnloaded), "Relasi kelompok_tani TIDAK dimuat jika un-eager loaded");
assertTest(!array_key_exists('riwayat_verifikasi', $lahanResourceUnloaded), "Relasi riwayat_verifikasi TIDAK dimuat jika un-eager loaded");

// Lahan dengan eager loading
$lahanWithRelations = Lahan::with(['komoditas', 'kelompokTani', 'riwayatVerifikasi.pengguna'])->find($lahanDraft->id_lahan);
$lahanResourceLoaded = (new LahanResource($lahanWithRelations))->resolve($reqAnggota);

assertTest(isset($lahanResourceLoaded['komoditas']) && $lahanResourceLoaded['komoditas'] === 'Padi Ciherang', "Alias Flutter 'komoditas' berupa string nama komoditas");
assertTest(isset($lahanResourceLoaded['detail_komoditas']) && $lahanResourceLoaded['detail_komoditas']['nama_komoditas'] === 'Padi Ciherang', "Detail komoditas berupa objek terstruktur terpetakan benar");
assertTest(isset($lahanResourceLoaded['kelompok_tani']), "Relasi kelompok_tani muncul saat dimuat");
assertTest(isset($lahanResourceLoaded['riwayat_verifikasi']), "Relasi riwayat_verifikasi muncul saat dimuat");
assertTest(count($lahanResourceLoaded['riwayat_verifikasi']) === 1, "Koleksi riwayat verifikasi terisi");

// Lahan dengan eager loading tetapi relasi komoditas bernilai null (tanpa komoditas / id_komoditas null)
$lahanTanpaKomoditas = Lahan::create([
    'id_lahan' => 'LHN-20261103-NOKMD',
    'id_pengguna' => 'USR-001',
    'id_kelompok' => 'KT-001',
    'id_komoditas' => null,
    'nama_lahan' => 'Petak Belum Ditanami',
    'luas' => 0.5,
    'alamat_lahan' => 'Jl. Kebun Kosong No. 1',
    'status_operasional' => 'persiapan',
    'status_verifikasi' => 'menunggu_verifikasi',
]);
$lahanLoadedNullKmd = Lahan::with(['komoditas'])->find($lahanTanpaKomoditas->id_lahan);
$resNullKmd = (new LahanResource($lahanLoadedNullKmd))->resolve($reqAnggota);

assertTest(array_key_exists('komoditas', $resNullKmd) && $resNullKmd['komoditas'] === null, "Relasi komoditas bernilai null menghasilkan field 'komoditas' = null (tidak mengasumsikan fallback)");
assertTest(array_key_exists('detail_komoditas', $resNullKmd) && $resNullKmd['detail_komoditas'] === null, "Relasi komoditas bernilai null menghasilkan 'detail_komoditas' = null");

echo "\n--- 4. Testing Policy Delegation pada is_can_edit dan is_can_delete ---\n";
// Skenario A: Anggota Pemilik pada Lahan Menunggu Verifikasi (Draft)
$resOwnerDraft = (new LahanResource($lahanDraft))->resolve($reqAnggota);
assertTest($resOwnerDraft['is_can_edit'] === true, "Pemilik aktif BISA mengedit lahan draft (is_can_edit = true)");
assertTest($resOwnerDraft['is_can_delete'] === true, "Pemilik aktif BISA menghapus lahan draft (is_can_delete = true)");

// Skenario B: Anggota Pemilik pada Lahan Terverifikasi
$lahanTerverifikasi = Lahan::create([
    'id_lahan' => 'LHN-20261103-VERI',
    'id_pengguna' => 'USR-001',
    'id_kelompok' => 'KT-001',
    'nama_lahan' => 'Sawah Terverifikasi',
    'luas' => 2.0,
    'alamat_lahan' => 'Jl. Padi Makmur No. 5',
    'status_verifikasi' => 'terverifikasi',
    'verifikator_id' => 'USR-002',
    'tanggal_verifikasi' => now(),
]);
$resOwnerVerified = (new LahanResource($lahanTerverifikasi))->resolve($reqAnggota);
assertTest($resOwnerVerified['is_can_edit'] === true, "Pemilik aktif BISA mengedit lahan terverifikasi (memicu revisi)");
assertTest($resOwnerVerified['is_can_delete'] === false, "Pemilik DILARANG menghapus lahan terverifikasi (is_can_delete = false via Policy)");

// Skenario C: Anggota Lain (Bukan Pemilik)
$reqAnggotaLain = Request::create('/api/v1/lahan', 'GET');
$reqAnggotaLain->setUserResolver(fn () => $anggotaLain);
$resNonOwner = (new LahanResource($lahanDraft))->resolve($reqAnggotaLain);
assertTest($resNonOwner['is_can_edit'] === false, "Bukan pemilik DITOLAK mengedit lahan (is_can_edit = false)");
assertTest($resNonOwner['is_can_delete'] === false, "Bukan pemilik DITOLAK menghapus lahan (is_can_delete = false)");

// Skenario D: User Unauthenticated (Null)
$reqGuest = Request::create('/api/v1/lahan', 'GET');
$resGuest = (new LahanResource($lahanDraft))->resolve($reqGuest);
assertTest($resGuest['is_can_edit'] === false, "Guest ditolak edit (is_can_edit = false)");
assertTest($resGuest['is_can_delete'] === false, "Guest ditolak delete (is_can_delete = false)");

echo "\n=========================================================\n";
echo "SUMMARY: Passed = {$passed}, Failed = {$failed}\n";
echo "=========================================================\n";

exit($failed === 0 ? 0 : 1);
