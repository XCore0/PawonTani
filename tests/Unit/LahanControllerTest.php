<?php

require_once 'c:/laragon/www/PawonTani/vendor/autoload.php';
$app = require_once 'c:/laragon/www/PawonTani/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\V1\LahanController;
use App\Http\Requests\Api\StoreLahanRequest;
use App\Http\Requests\Api\UpdateLahanRequest;
use App\Models\KelompokTani;
use App\Models\Komoditas;
use App\Models\Lahan;
use App\Models\Pengguna;
use App\Models\RiwayatVerifikasiLahan;
use App\Services\CloudinaryService;
use App\Services\LahanService;
use App\Services\NotificationService;
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
echo "=== PAWONTANI LAHANCONTROLLER ISOLATED TEST SUITE (SQLITE) ===\n";
echo "=========================================================\n\n";

// Mock Services
$cloudinaryMock = Mockery::mock(CloudinaryService::class);
$cloudinaryMock->shouldReceive('upload')->andReturn('https://res.cloudinary.com/demo/test.jpg');
$cloudinaryMock->shouldReceive('delete')->andReturn(true);

$notificationMock = Mockery::mock(NotificationService::class);
$notificationMock->shouldReceive('notifyLahanDiajukan')->andReturnNull();
$notificationMock->shouldReceive('notifyLahanDiverifikasi')->andReturnNull();

$lahanService = new LahanService($notificationMock, $cloudinaryMock);
$controller = new LahanController($lahanService);

// Seed Data
$poktan = KelompokTani::create([
    'id_kelompok' => 'KT-001',
    'nama_kelompok' => 'Kelompok Tani Sejahtera',
    'alamat' => 'Desa Makmur RT 01',
    'status' => 'Aktif',
]);

$komoditas1 = Komoditas::create([
    'id_komoditas' => 'KMD-001',
    'nama_komoditas' => 'Padi Inpari 32',
    'kategori' => 'Pangan',
]);

$komoditas2 = Komoditas::create([
    'id_komoditas' => 'KMD-002',
    'nama_komoditas' => 'Jagung Manis',
    'kategori' => 'Pangan',
]);

$anggota1 = Pengguna::create([
    'id_pengguna' => 'USR-001',
    'nama' => 'Petani Satu',
    'username' => 'petani1',
    'role' => 'Anggota',
    'status' => 'Aktif',
    'id_kelompok' => 'KT-001',
    'password' => 'secret123',
]);

$anggota2 = Pengguna::create([
    'id_pengguna' => 'USR-002',
    'nama' => 'Petani Dua',
    'username' => 'petani2',
    'role' => 'Anggota',
    'status' => 'Aktif',
    'id_kelompok' => 'KT-001',
    'password' => 'secret123',
]);

$ketua = Pengguna::create([
    'id_pengguna' => 'USR-PGR',
    'nama' => 'Ketua Kelompok',
    'username' => 'ketua_poktan',
    'role' => 'Pengurus',
    'status' => 'Aktif',
    'jabatan' => 'Ketua',
    'id_kelompok' => 'KT-001',
    'password' => 'secret123',
]);

// Buat 3 lahan untuk Anggota 1
$lahan1 = Lahan::create([
    'id_lahan' => 'LHN-20261103-0001',
    'id_pengguna' => 'USR-001',
    'id_kelompok' => 'KT-001',
    'id_komoditas' => 'KMD-001',
    'nama_lahan' => 'Petak Sawah Lembah',
    'luas' => 1.5,
    'alamat_lahan' => 'Dusun Lembah RT 01',
    'status_operasional' => 'persiapan',
    'status_verifikasi' => 'menunggu_verifikasi',
    'nomor_revisi' => 1,
]);

$lahan2 = Lahan::create([
    'id_lahan' => 'LHN-20261103-0002',
    'id_pengguna' => 'USR-001',
    'id_kelompok' => 'KT-001',
    'id_komoditas' => 'KMD-002',
    'nama_lahan' => 'Kebun Jagung Lereng',
    'luas' => 0.75,
    'alamat_lahan' => 'Lereng Bukit RT 05',
    'status_operasional' => 'tanam',
    'status_verifikasi' => 'terverifikasi',
    'verifikator_id' => 'USR-PGR',
    'tanggal_verifikasi' => now(),
    'nomor_revisi' => 1,
]);

$lahan3 = Lahan::create([
    'id_lahan' => 'LHN-20261103-0003',
    'id_pengguna' => 'USR-001',
    'id_kelompok' => 'KT-001',
    'id_komoditas' => 'KMD-001',
    'nama_lahan' => 'Petak Sawah Timur',
    'luas' => 2.0,
    'alamat_lahan' => 'Area Irigasi Timur',
    'status_operasional' => 'persiapan',
    'status_verifikasi' => 'perlu_perbaikan',
    'catatan_verifikasi' => 'Foto patok batas belum jelas',
    'nomor_revisi' => 1,
]);

// Buat 1 lahan untuk Anggota 2
$lahanAnggota2 = Lahan::create([
    'id_lahan' => 'LHN-20261103-0004',
    'id_pengguna' => 'USR-002',
    'id_kelompok' => 'KT-001',
    'id_komoditas' => 'KMD-001',
    'nama_lahan' => 'Lahan Milik Anggota Dua',
    'luas' => 3.0,
    'alamat_lahan' => 'Dusun Sebelah RT 09',
    'status_operasional' => 'persiapan',
    'status_verifikasi' => 'menunggu_verifikasi',
    'nomor_revisi' => 1,
]);

// Riwayat untuk lahan 1
RiwayatVerifikasiLahan::create([
    'id_lahan' => $lahan1->id_lahan,
    'id_pengguna' => 'USR-001',
    'aksi' => 'pengajuan_baru',
    'status_sebelumnya' => null,
    'status_sesudahnya' => 'menunggu_verifikasi',
    'catatan' => 'Pendaftaran awal',
    'nomor_revisi' => 1,
    'created_at' => now(),
]);

echo "--- 1. Testing index() Method ---\n";
// Skenario A: Default index untuk Anggota 1
$reqIndex1 = Request::create('/api/v1/lahan', 'GET');
$reqIndex1->setUserResolver(fn () => $anggota1);

$respIndex1 = $controller->index($reqIndex1);
$dataIndex1 = json_decode($respIndex1->getContent(), true);

assertTest($respIndex1->getStatusCode() === 200, "HTTP Status 200 OK");
assertTest($dataIndex1['success'] === true, "Envelop success bernilai true");
assertTest($dataIndex1['meta']['total'] === 3, "Scoping query mengembalikan tepat 3 lahan milik Anggota 1 (lahan Anggota 2 tidak muncul)");
assertTest(count($dataIndex1['data']) === 3, "Data array berisi 3 item");
assertTest(isset($dataIndex1['meta']['current_page']), "Metadata pagination meta.current_page tersedia");
assertTest(isset($dataIndex1['links']['first']), "Links pagination links.first tersedia");

// Verifikasi Anti N+1: riwayat_verifikasi TIDAK dimuat pada index
assertTest(!array_key_exists('riwayat_verifikasi', $dataIndex1['data'][0]), "Riwayat verifikasi TIDAK dimuat di index (Anti N+1)");
assertTest(isset($dataIndex1['data'][0]['komoditas']), "Relasi komoditas ter-eager load");

// Skenario B: Filter status_verifikasi = terverifikasi
$reqFilterStatus = Request::create('/api/v1/lahan', 'GET', ['status_verifikasi' => 'terverifikasi']);
$reqFilterStatus->setUserResolver(fn () => $anggota1);
$dataFilterStatus = json_decode($controller->index($reqFilterStatus)->getContent(), true);
assertTest($dataFilterStatus['meta']['total'] === 1, "Filter status_verifikasi=terverifikasi mengembalikan 1 lahan");
assertTest($dataFilterStatus['data'][0]['id_lahan'] === 'LHN-20261103-0002', "ID lahan terfilter sesuai");

// Skenario C: Filter id_komoditas = KMD-002
$reqFilterKmd = Request::create('/api/v1/lahan', 'GET', ['id_komoditas' => 'KMD-002']);
$reqFilterKmd->setUserResolver(fn () => $anggota1);
$dataFilterKmd = json_decode($controller->index($reqFilterKmd)->getContent(), true);
assertTest($dataFilterKmd['meta']['total'] === 1, "Filter id_komoditas mengembalikan 1 lahan");

// Skenario D: Pencarian q = 'Lembah'
$reqSearch = Request::create('/api/v1/lahan', 'GET', ['q' => 'Lembah']);
$reqSearch->setUserResolver(fn () => $anggota1);
$dataSearch = json_decode($controller->index($reqSearch)->getContent(), true);
assertTest($dataSearch['meta']['total'] === 1, "Pencarian q='Lembah' mengembalikan 1 lahan");

// Skenario E: Pagination limit clamp (per_page > 50 dibatasi ke 50)
$reqPerPageMax = Request::create('/api/v1/lahan', 'GET', ['per_page' => 100]);
$reqPerPageMax->setUserResolver(fn () => $anggota1);
$dataPerPageMax = json_decode($controller->index($reqPerPageMax)->getContent(), true);
assertTest($dataPerPageMax['meta']['per_page'] === 50, "Parameter per_page=100 dibatasi (clamped) maksimal 50");

// Parameter per_page < 1 dibatasi ke 10
$reqPerPageMin = Request::create('/api/v1/lahan', 'GET', ['per_page' => 0]);
$reqPerPageMin->setUserResolver(fn () => $anggota1);
$dataPerPageMin = json_decode($controller->index($reqPerPageMin)->getContent(), true);
assertTest($dataPerPageMin['meta']['per_page'] === 10, "Parameter per_page=0 diatur ulang ke default 10");

echo "\n--- 2. Testing show() Method (Detail & IDOR Prevention) ---\n";
// Skenario A: Anggota 1 melihat lahannya sendiri
$reqShowValid = Request::create('/api/v1/lahan/' . $lahan1->id_lahan, 'GET');
$reqShowValid->setUserResolver(fn () => $anggota1);
$respShowValid = $controller->show($reqShowValid, $lahan1->id_lahan);
$dataShowValid = json_decode($respShowValid->getContent(), true);

assertTest($respShowValid->getStatusCode() === 200, "HTTP Status 200 untuk akses lahan sendiri");
assertTest($dataShowValid['data']['id_lahan'] === $lahan1->id_lahan, "Data lahan sesuai");
assertTest(isset($dataShowValid['data']['riwayat_verifikasi']), "Riwayat verifikasi ter-eager load pada show()");
assertTest(count($dataShowValid['data']['riwayat_verifikasi']) === 1, "Riwayat verifikasi terisi");

// Skenario B: IDOR Attempt - Anggota 1 mencoba melihat lahan milik Anggota 2
$reqShowIDOR = Request::create('/api/v1/lahan/' . $lahanAnggota2->id_lahan, 'GET');
$reqShowIDOR->setUserResolver(fn () => $anggota1);
$respShowIDOR = $controller->show($reqShowIDOR, $lahanAnggota2->id_lahan);
$dataShowIDOR = json_decode($respShowIDOR->getContent(), true);

assertTest($respShowIDOR->getStatusCode() === 404, "Akses lahan milik pengguna lain menghasilkan HTTP 404 (IDOR dicegah)");
assertTest($dataShowIDOR['success'] === false, "Envelop success bernilai false pada 404");
assertTest(str_contains($dataShowIDOR['message'], 'tidak ditemukan'), "Pesan error seragam menyembunyikan eksistensi data");

echo "\n--- 3. Testing store() Method ---\n";
// Skenario A: Pendaftaran lahan sukses oleh Anggota aktif
$reqStore = StoreLahanRequest::create('/api/v1/lahan', 'POST', [
    'nama_lahan' => 'Petak Percobaan Baru',
    'luas' => 0.5,
    'alamat_lahan' => 'Jl. Percobaan No. 1',
    'id_komoditas' => 'KMD-001',
    'keterangan' => 'Uji coba pendaftaran',
]);
$reqStore->setUserResolver(fn () => $anggota1);
$validatorStore = app('validator')->make($reqStore->all(), $reqStore->rules());
$reqStore->setValidator($validatorStore);

$respStore = $controller->store($reqStore);
$dataStore = json_decode($respStore->getContent(), true);

assertTest($respStore->getStatusCode() === 201, "Pendaftaran lahan mengembalikan HTTP 201 Created");
assertTest($dataStore['success'] === true, "Envelop success bernilai true");
assertTest($dataStore['data']['nama_lahan'] === 'Petak Percobaan Baru', "Nama lahan tersimpan benar");
assertTest($dataStore['data']['status_verifikasi'] === 'menunggu_verifikasi', "Status awal menunggu_verifikasi");
assertTest(!str_contains($dataStore['message'], 'Ketua sudah menerima notifikasi'), "Pesan respons objektif dan tidak menjanjikan ketua menerima notifikasi jika tidak ada");

// Skenario B: Pengurus mencoba mendaftar via endpoint mobile (Ditolak Policy)
$reqStorePengurus = StoreLahanRequest::create('/api/v1/lahan', 'POST', [
    'nama_lahan' => 'Lahan Ilegal Pengurus',
    'luas' => 1.0,
    'alamat_lahan' => 'Alamat Pengurus',
]);
$reqStorePengurus->setUserResolver(fn () => $ketua);
$reqStorePengurus->setValidator(app('validator')->make($reqStorePengurus->all(), []));

$respStorePengurus = $controller->store($reqStorePengurus);
assertTest($respStorePengurus->getStatusCode() === 403, "Pengurus ditolak mendaftarkan lahan via API Mobile (HTTP 403)");

echo "\n--- 4. Testing update() Method ---\n";
// Skenario A: Anggota 1 memperbarui lahan miliknya yang berstatus menunggu_verifikasi
$reqUpdate = UpdateLahanRequest::create('/api/v1/lahan/' . $lahan1->id_lahan, 'PUT', [
    'nama_lahan' => 'Petak Sawah Lembah (Updated)',
    'luas' => 1.75,
    'alamat_lahan' => 'Dusun Lembah Baru RT 02',
]);
$reqUpdate->setUserResolver(fn () => $anggota1);
$validatorUpdate = app('validator')->make($reqUpdate->all(), $reqUpdate->rules());
$reqUpdate->setValidator($validatorUpdate);

$respUpdate = $controller->update($reqUpdate, $lahan1->id_lahan);
$dataUpdate = json_decode($respUpdate->getContent(), true);

assertTest($respUpdate->getStatusCode() === 200, "Pembaruan lahan sukses mengembalikan HTTP 200 OK");
assertTest($dataUpdate['data']['nama_lahan'] === 'Petak Sawah Lembah (Updated)', "Nama lahan berhasil diperbarui");
assertTest($dataUpdate['data']['luas'] === 1.75, "Luas lahan berhasil diperbarui");

// Skenario B: Anggota 1 mencoba update lahan milik Anggota 2 (IDOR)
$reqUpdateIDOR = UpdateLahanRequest::create('/api/v1/lahan/' . $lahanAnggota2->id_lahan, 'PUT', [
    'nama_lahan' => 'Hack Nama Lahan',
]);
$reqUpdateIDOR->setUserResolver(fn () => $anggota1);
$validatorUpdateIDOR = app('validator')->make($reqUpdateIDOR->all(), $reqUpdateIDOR->rules());
$reqUpdateIDOR->setValidator($validatorUpdateIDOR);

$respUpdateIDOR = $controller->update($reqUpdateIDOR, $lahanAnggota2->id_lahan);
assertTest($respUpdateIDOR->getStatusCode() === 403, "Mencoba mengubah lahan milik orang lain menghasilkan HTTP 403 Forbidden");

echo "\n--- 5. Testing destroy() Method (Soft Delete & DomainException 422) ---\n";
// Skenario A: Menghapus lahan berstatus terverifikasi dilarang keras (DomainException -> 422)
$reqDeleteVerified = Request::create('/api/v1/lahan/' . $lahan2->id_lahan, 'DELETE');
$reqDeleteVerified->setUserResolver(fn () => $anggota1);

$respDeleteVerified = $controller->destroy($reqDeleteVerified, $lahan2->id_lahan);
$dataDeleteVerified = json_decode($respDeleteVerified->getContent(), true);

assertTest($respDeleteVerified->getStatusCode() === 422, "Menghapus lahan terverifikasi menghasilkan HTTP 422 Unprocessable Entity (Bukan 500)");
assertTest($dataDeleteVerified['success'] === false, "Envelop success bernilai false");
assertTest(str_contains($dataDeleteVerified['message'], 'tidak dapat dihapus'), "Pesan DomainException disampaikan dengan ramah ke pengguna");

// Skenario B: IDOR pada delete - Anggota 1 mencoba menghapus lahan Anggota 2
$reqDeleteIDOR = Request::create('/api/v1/lahan/' . $lahanAnggota2->id_lahan, 'DELETE');
$reqDeleteIDOR->setUserResolver(fn () => $anggota1);

$respDeleteIDOR = $controller->destroy($reqDeleteIDOR, $lahanAnggota2->id_lahan);
assertTest($respDeleteIDOR->getStatusCode() === 403, "Mencoba menghapus lahan milik orang lain menghasilkan HTTP 403 Forbidden");

// Skenario C: Menghapus lahan draft milik sendiri berhasil
$reqDeleteValid = Request::create('/api/v1/lahan/' . $lahan1->id_lahan, 'DELETE');
$reqDeleteValid->setUserResolver(fn () => $anggota1);

$respDeleteValid = $controller->destroy($reqDeleteValid, $lahan1->id_lahan);
$dataDeleteValid = json_decode($respDeleteValid->getContent(), true);

assertTest($respDeleteValid->getStatusCode() === 200, "Menghapus lahan draft berhasil mengembalikan HTTP 200 OK");
assertTest($dataDeleteValid['success'] === true, "Envelop success bernilai true");

echo "\n--- 6. Testing Error 500 Sanitization ---\n";
// Mock service yang melempar exception fatal
$failingServiceMock = Mockery::mock(LahanService::class);
$failingServiceMock->shouldReceive('deleteLahan')->andThrow(new RuntimeException("SQLSTATE[08006] Connection refused to secret-db-host:5432"));
$failingController = new LahanController($failingServiceMock);

$resp500 = $failingController->destroy($reqDeleteValid, $lahan1->id_lahan);
$data500 = json_decode($resp500->getContent(), true);

assertTest($resp500->getStatusCode() === 500, "Kesalahan sistem menghasilkan HTTP 500");
assertTest($data500['success'] === false, "Envelop success bernilai false pada 500");
assertTest(!str_contains($data500['message'], 'SQLSTATE'), "Pesan error 500 TIDAK membocorkan SQLSTATE");
assertTest(!str_contains($data500['message'], 'secret-db-host'), "Pesan error 500 TIDAK membocorkan host database");
assertTest(str_contains($data500['message'], 'Terjadi kesalahan sistem'), "Pesan error 500 bersifat umum dan aman");

echo "\n=========================================================\n";
echo "SUMMARY: Passed = {$passed}, Failed = {$failed}\n";
echo "=========================================================\n";

exit($failed === 0 ? 0 : 1);
