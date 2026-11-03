<?php

require_once 'c:/laragon/www/PawonTani/vendor/autoload.php';
$app = require_once 'c:/laragon/www/PawonTani/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Pengurus\LahanController as PengurusLahanController;
use App\Http\Requests\Pengurus\VerifikasiLahanRequest;
use App\Models\KelompokTani;
use App\Models\Komoditas;
use App\Models\Lahan;
use App\Models\Pengguna;
use App\Models\RiwayatVerifikasiLahan;
use App\Services\CloudinaryService;
use App\Services\LahanService;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

// Setup in-memory SQLite database
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

// Register temporary named route for redirect testing if not already registered
if (!app('router')->getRoutes()->hasNamedRoute('pengurus.lahan.show')) {
    app('router')->get('/pengurus/lahan/{lahan}', fn () => 'show')->name('pengurus.lahan.show');
    app('router')->getRoutes()->refreshNameLookups();
}

// Share ViewErrorBag for isolated view rendering outside web middleware stack
view()->share('errors', new \Illuminate\Support\ViewErrorBag());

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
echo "=== PAWONTANI PENGURUS LAHANCONTROLLER TEST SUITE ===\n";
echo "=========================================================\n\n";

use App\Models\Notifikasi;

// Mock Services
$cloudinaryMock = Mockery::mock(CloudinaryService::class);
$cloudinaryMock->shouldReceive('upload')->andReturn('https://res.cloudinary.com/demo/test.jpg');
$cloudinaryMock->shouldReceive('delete')->andReturn(true);

$dummyNotif = new Notifikasi([
    'id_pengguna' => 'USR-001',
    'judul' => 'Dummy Notif',
    'pesan' => 'Dummy Message',
    'jenis' => 'verifikasi_lahan',
]);

$notificationMock = Mockery::mock(NotificationService::class);
$notificationMock->shouldReceive('notifyLahanDiajukan')->andReturn($dummyNotif);
$notificationMock->shouldReceive('notifyLahanTerverifikasi')->andReturn($dummyNotif);
$notificationMock->shouldReceive('notifyPerluPerbaikan')->andReturn($dummyNotif);

$lahanService = new LahanService($notificationMock, $cloudinaryMock);
$controller = new PengurusLahanController($lahanService);

// 1. Fixture Kelompok Tani
$poktan1 = KelompokTani::create([
    'id_kelompok' => 'KT-001',
    'nama_kelompok' => 'Poktan Maju Jaya',
    'alamat' => 'Desa Sukamaju RT 01',
    'status' => 'Aktif',
]);

$poktan2 = KelompokTani::create([
    'id_kelompok' => 'KT-002',
    'nama_kelompok' => 'Poktan Makmur Sentosa',
    'alamat' => 'Desa Makmur RT 02',
    'status' => 'Aktif',
]);

// 2. Fixture Komoditas
$kmdPadi = Komoditas::create([
    'id_komoditas' => 'KMD-001',
    'nama_komoditas' => 'Padi Pandan Wangi',
    'kategori' => 'Pangan',
]);

$kmdJagung = Komoditas::create([
    'id_komoditas' => 'KMD-002',
    'nama_komoditas' => 'Jagung Manis',
    'kategori' => 'Pangan',
]);

// 3. Fixture Pengguna
// Ketua Poktan 1
$ketuaPoktan1 = Pengguna::create([
    'id_pengguna' => 'USR-KETUA-1',
    'nama' => 'Bpk. Ahmad Ketua',
    'username' => 'ahmad_ketua',
    'role' => 'Pengurus',
    'jabatan' => 'Ketua',
    'status' => 'Aktif',
    'id_kelompok' => 'KT-001',
    'password' => 'secret123',
]);

// Sekretaris Poktan 1 (Pengurus biasa)
$sekretarisPoktan1 = Pengguna::create([
    'id_pengguna' => 'USR-SEKRETARIS-1',
    'nama' => 'Ibu Siti Sekretaris',
    'username' => 'siti_sekre',
    'role' => 'Pengurus',
    'jabatan' => 'Sekretaris',
    'status' => 'Aktif',
    'id_kelompok' => 'KT-001',
    'password' => 'secret123',
]);

// Ketua Poktan 2 (Kelompok Lain)
$ketuaPoktan2 = Pengguna::create([
    'id_pengguna' => 'USR-KETUA-2',
    'nama' => 'Bpk. Bambang Ketua 2',
    'username' => 'bambang_ketua',
    'role' => 'Pengurus',
    'jabatan' => 'Ketua',
    'status' => 'Aktif',
    'id_kelompok' => 'KT-002',
    'password' => 'secret123',
]);

// Anggota Poktan 1
$anggotaPoktan1 = Pengguna::create([
    'id_pengguna' => 'USR-ANGGOTA-1',
    'nama' => 'Pak Tani Joko',
    'nik' => '3301234567890001',
    'username' => 'joko_anggota',
    'role' => 'Anggota',
    'status' => 'Aktif',
    'id_kelompok' => 'KT-001',
    'password' => 'secret123',
]);

// Anggota Poktan 2
$anggotaPoktan2 = Pengguna::create([
    'id_pengguna' => 'USR-ANGGOTA-2',
    'nama' => 'Pak Tani Wawan',
    'username' => 'wawan_anggota',
    'role' => 'Anggota',
    'status' => 'Aktif',
    'id_kelompok' => 'KT-002',
    'password' => 'secret123',
]);

// 4. Fixture Lahan
// Lahan Poktan 1 - Menunggu Verifikasi
$lahan1 = Lahan::create([
    'id_lahan' => 'LHN-20261103-0001',
    'id_pengguna' => 'USR-ANGGOTA-1',
    'id_kelompok' => 'KT-001',
    'id_komoditas' => 'KMD-001',
    'nama_lahan' => 'Sawah Petak Lembah',
    'luas' => 1.5,
    'alamat_lahan' => 'Jl. Lembah RT 01',
    'status_operasional' => 'persiapan',
    'status_verifikasi' => 'menunggu_verifikasi',
    'nomor_revisi' => 1,
]);

// Lahan Poktan 1 - Terverifikasi
$lahan2 = Lahan::create([
    'id_lahan' => 'LHN-20261103-0002',
    'id_pengguna' => 'USR-ANGGOTA-1',
    'id_kelompok' => 'KT-001',
    'id_komoditas' => 'KMD-002',
    'nama_lahan' => 'Kebun Jagung Lereng',
    'luas' => 0.8,
    'alamat_lahan' => 'Lereng Indah RT 02',
    'status_operasional' => 'tanam',
    'status_verifikasi' => 'terverifikasi',
    'verifikator_id' => 'USR-KETUA-1',
    'tanggal_verifikasi' => now(),
    'nomor_revisi' => 1,
]);

// Lahan Poktan 1 - Perlu Perbaikan
$lahan3 = Lahan::create([
    'id_lahan' => 'LHN-20261103-0003',
    'id_pengguna' => 'USR-ANGGOTA-1',
    'id_kelompok' => 'KT-001',
    'id_komoditas' => 'KMD-001',
    'nama_lahan' => 'Sawah Irigasi Timur',
    'luas' => 2.0,
    'alamat_lahan' => 'Area Irigasi RT 03',
    'status_operasional' => 'persiapan',
    'status_verifikasi' => 'perlu_perbaikan',
    'catatan_verifikasi' => 'Batas patok barat perlu diperjelas fotonya',
    'nomor_revisi' => 1,
]);

// Lahan Poktan 2 (Lahan Kelompok Lain)
$lahanPoktan2 = Lahan::create([
    'id_lahan' => 'LHN-20261103-0004',
    'id_pengguna' => 'USR-ANGGOTA-2',
    'id_kelompok' => 'KT-002',
    'id_komoditas' => 'KMD-001',
    'nama_lahan' => 'Sawah Makmur Poktan 2',
    'luas' => 3.5,
    'alamat_lahan' => 'Dusun Seberang RT 09',
    'status_operasional' => 'persiapan',
    'status_verifikasi' => 'menunggu_verifikasi',
    'nomor_revisi' => 1,
]);

echo "--- 1. Testing index() Method & Scoping ---\n";
// Skenario A: Ketua Poktan 1 mengakses index
$reqIndexKetua1 = Request::create('/pengurus/lahan', 'GET');
$reqIndexKetua1->setUserResolver(fn () => $ketuaPoktan1);

$viewIndex = $controller->index($reqIndexKetua1);
$viewData = $viewIndex->getData();

assertTest($viewIndex->name() === 'Pengurus.Content.Lahan.Index', "Mengembalikan view 'Pengurus.Content.Lahan.Index'");
assertTest($viewData['lahans']->total() === 3, "Hanya menampilkan 3 lahan anggota Poktan 1 (lahan Poktan 2 TIDAK bocor)");
assertTest($viewData['stats']['total'] === 3, "Metrik total lahan bernilai 3 (hanya kelompok pengurus)");
assertTest($viewData['stats']['menunggu_verifikasi'] === 1, "Metrik menunggu_verifikasi = 1");
assertTest($viewData['stats']['terverifikasi'] === 1, "Metrik terverifikasi = 1");
assertTest($viewData['stats']['perlu_perbaikan'] === 1, "Metrik perlu_perbaikan = 1");

// Skenario B: Sekretaris Poktan 1 (Pengurus biasa) juga melihat data yang sama di kelompoknya
$reqIndexSekre1 = Request::create('/pengurus/lahan', 'GET');
$reqIndexSekre1->setUserResolver(fn () => $sekretarisPoktan1);
$viewIndexSekre = $controller->index($reqIndexSekre1);
assertTest($viewIndexSekre->getData()['lahans']->total() === 3, "Sekretaris melihat seluruh lahan kelompoknya (3 lahan)");

// Skenario C1: Filter status verifikasi = menunggu_verifikasi
$reqFilterMenunggu = Request::create('/pengurus/lahan', 'GET', ['status' => 'menunggu_verifikasi']);
$reqFilterMenunggu->setUserResolver(fn () => $ketuaPoktan1);
$viewFilterMenunggu = $controller->index($reqFilterMenunggu);
assertTest($viewFilterMenunggu->getData()['lahans']->total() === 1, "Filter status=menunggu_verifikasi mengembalikan 1 lahan");
assertTest($viewFilterMenunggu->getData()['lahans']->first()->id_lahan === 'LHN-20261103-0001', "Lahan menunggu_verifikasi terfilter sesuai");

// Skenario C2: Filter status verifikasi = terverifikasi
$reqFilterStatus = Request::create('/pengurus/lahan', 'GET', ['status' => 'terverifikasi']);
$reqFilterStatus->setUserResolver(fn () => $ketuaPoktan1);
$viewFilterStatus = $controller->index($reqFilterStatus);
assertTest($viewFilterStatus->getData()['lahans']->total() === 1, "Filter status=terverifikasi mengembalikan 1 lahan");
assertTest($viewFilterStatus->getData()['lahans']->first()->id_lahan === 'LHN-20261103-0002', "Lahan terverifikasi terfilter sesuai");
assertTest($viewFilterStatus->getData()['stats']['total'] === 3, "Statistik total kelompok tidak terpengaruh filter status");
assertTest($viewFilterStatus->getData()['stats']['menunggu_verifikasi'] === 1, "Statistik menunggu_verifikasi tidak terpengaruh filter status");

// Skenario C3: Filter status verifikasi = perlu_perbaikan
$reqFilterPerbaikan = Request::create('/pengurus/lahan', 'GET', ['status' => 'perlu_perbaikan']);
$reqFilterPerbaikan->setUserResolver(fn () => $ketuaPoktan1);
$viewFilterPerbaikan = $controller->index($reqFilterPerbaikan);
assertTest($viewFilterPerbaikan->getData()['lahans']->total() === 1, "Filter status=perlu_perbaikan mengembalikan 1 lahan");
assertTest($viewFilterPerbaikan->getData()['lahans']->first()->id_lahan === 'LHN-20261103-0003', "Lahan perlu_perbaikan terfilter sesuai");

// Skenario D: Filter komoditas = KMD-002 (Jagung Manis)
$reqFilterKmd = Request::create('/pengurus/lahan', 'GET', ['id_komoditas' => 'KMD-002']);
$reqFilterKmd->setUserResolver(fn () => $ketuaPoktan1);
$viewFilterKmd = $controller->index($reqFilterKmd);
assertTest($viewFilterKmd->getData()['lahans']->total() === 1, "Filter id_komoditas mengembalikan 1 lahan");

// Skenario E: Pencarian nama anggota = 'Joko'
$reqSearchAnggota = Request::create('/pengurus/lahan', 'GET', ['q' => 'Joko']);
$reqSearchAnggota->setUserResolver(fn () => $ketuaPoktan1);
$viewSearchAnggota = $controller->index($reqSearchAnggota);
assertTest($viewSearchAnggota->getData()['lahans']->total() === 3, "Pencarian nama pemilik 'Joko' menemukan 3 lahan");
assertTest($viewSearchAnggota->getData()['stats']['total'] === 3, "Statistik total tidak berubah karena filter pencarian q");

// Skenario F: Pencarian anggota kelompok lain = 'Wawan' (TIDAK BOLEH MUNCUL)
$reqSearchWawan = Request::create('/pengurus/lahan', 'GET', ['q' => 'Wawan']);
$reqSearchWawan->setUserResolver(fn () => $ketuaPoktan1);
$viewSearchWawan = $controller->index($reqSearchWawan);
assertTest($viewSearchWawan->getData()['lahans']->total() === 0, "Pencarian tidak mengambil data anggota kelompok lain (0 hasil)");
assertTest($viewSearchWawan->getData()['stats']['total'] === 3, "Statistik tetap utuh meskipun hasil pencarian 0 data");

// Skenario G: Parameter Pagination tidak merusak statistik
$reqPagination = Request::create('/pengurus/lahan', 'GET', ['page' => 2]);
$reqPagination->setUserResolver(fn () => $ketuaPoktan1);
$viewPagination = $controller->index($reqPagination);
assertTest($viewPagination->getData()['stats']['total'] === 3, "Statistik total tidak berubah karena pagination");

// Skenario H: Isolasi Data Kelompok Tani 2
$reqIndexKetua2 = Request::create('/pengurus/lahan', 'GET');
$reqIndexKetua2->setUserResolver(fn () => $ketuaPoktan2);
$viewIndexKetua2 = $controller->index($reqIndexKetua2);
assertTest($viewIndexKetua2->getData()['lahans']->total() === 1, "Ketua Poktan 2 hanya melihat 1 lahan kelompoknya");
assertTest($viewIndexKetua2->getData()['stats']['total'] === 1, "Metrik Poktan 2 terisolasi (total = 1)");
assertTest($viewIndexKetua2->getData()['stats']['menunggu_verifikasi'] === 1, "Metrik Poktan 2 menunggu_verifikasi = 1");
assertTest($viewIndexKetua2->getData()['stats']['terverifikasi'] === 0, "Metrik Poktan 2 terverifikasi = 0");

// Skenario I: Eager loading NIK tidak bocor pada index()
$firstLahanIndex = $viewIndex->getData()['lahans']->first();
assertTest(!isset($firstLahanIndex->pengguna->nik) || $firstLahanIndex->pengguna->nik === null, "Kolom NIK tidak dimuat pada eager loading index()");

echo "\n--- 2. Testing show() Method (Detail & isKetua Evaluation) ---\n";
// Skenario A: Ketua Poktan 1 melihat detail lahan Poktan 1
$reqShowKetua1 = Request::create('/pengurus/lahan/' . $lahan1->id_lahan, 'GET');
$reqShowKetua1->setUserResolver(fn () => $ketuaPoktan1);

$viewShowKetua1 = $controller->show($reqShowKetua1, $lahan1->id_lahan);
$dataShowKetua1 = $viewShowKetua1->getData();

assertTest($viewShowKetua1->name() === 'Pengurus.Content.Lahan.Show', "Mengembalikan view 'Pengurus.Content.Lahan.Show'");
assertTest($dataShowKetua1['lahan']->id_lahan === $lahan1->id_lahan, "Data lahan sesuai");
assertTest($dataShowKetua1['isKetua'] === true, "Ketua aktif mendapatkan status isKetua = true");
assertTest($dataShowKetua1['lahan']->pengguna->nik === '3301234567890001', "Kolom NIK tetap dimuat pada halaman show()");

// Skenario B: Sekretaris Poktan 1 melihat detail lahan Poktan 1
$reqShowSekre1 = Request::create('/pengurus/lahan/' . $lahan1->id_lahan, 'GET');
$reqShowSekre1->setUserResolver(fn () => $sekretarisPoktan1);
$viewShowSekre1 = $controller->show($reqShowSekre1, $lahan1->id_lahan);
assertTest($viewShowSekre1->getData()['isKetua'] === false, "Sekretaris (pengurus biasa) mendapatkan status isKetua = false");

// Skenario C: IDOR Prevention - Pengurus Poktan 1 mencoba melihat lahan milik Poktan 2
$isModelNotFound = false;
try {
    $reqShowIDOR = Request::create('/pengurus/lahan/' . $lahanPoktan2->id_lahan, 'GET');
    $reqShowIDOR->setUserResolver(fn () => $ketuaPoktan1);
    $controller->show($reqShowIDOR, $lahanPoktan2->id_lahan);
} catch (ModelNotFoundException $e) {
    $isModelNotFound = true;
}
assertTest($isModelNotFound === true, "Akses ke lahan milik kelompok lain menghasilkan ModelNotFoundException (HTTP 404)");

echo "\n--- 3. Testing verifikasi() Method (Authorization & Service Delegation) ---\n";
// Skenario A: Ketua Poktan 1 menyetujui lahan 1
$reqVerifSetujui = VerifikasiLahanRequest::create('/pengurus/lahan/' . $lahan1->id_lahan . '/verifikasi', 'POST', [
    'aksi' => 'setujui',
]);
$reqVerifSetujui->setUserResolver(fn () => $ketuaPoktan1);
// Simulasikan route parameter 'lahan'
$reqVerifSetujui->setRouteResolver(function () use ($lahan1) {
    return new class($lahan1) {
        public function __construct(private $lahan) {}
        public function parameter($name) { return $name === 'lahan' ? $this->lahan->id_lahan : null; }
    };
});
$validatorSetujui = app('validator')->make($reqVerifSetujui->all(), $reqVerifSetujui->rules());
$reqVerifSetujui->setValidator($validatorSetujui);

$respVerifSetujui = $controller->verifikasi($reqVerifSetujui, $lahan1->id_lahan);
assertTest($respVerifSetujui->isRedirect(), "Aksi verifikasi sukses melakukan redirect");
assertTest(session('success') === 'Lahan berhasil disetujui dan status telah diverifikasi.', "Flash message sukses persetujuan sesuai");

// Verifikasi database: status lahan berubah menjadi terverifikasi melalui Service
$lahan1->refresh();
assertTest($lahan1->status_verifikasi === 'terverifikasi', "Status lahan berhasil berubah menjadi terverifikasi");
assertTest($lahan1->verifikator_id === 'USR-KETUA-1', "Verifikator tercatat atas nama Ketua");

// Skenario B: Ketua Poktan 1 mencabut verifikasi lahan 1 (dengan alasan wajib)
$reqCabut = VerifikasiLahanRequest::create('/pengurus/lahan/' . $lahan1->id_lahan . '/verifikasi', 'POST', [
    'aksi' => 'cabut_verifikasi',
    'catatan' => 'Ditemukan ketidaksesuaian batas patok tanah',
]);
$reqCabut->setUserResolver(fn () => $ketuaPoktan1);
$reqCabut->setRouteResolver(function () use ($lahan1) {
    return new class($lahan1) {
        public function __construct(private $lahan) {}
        public function parameter($name) { return $name === 'lahan' ? $this->lahan->id_lahan : null; }
    };
});
$validatorCabut = app('validator')->make($reqCabut->all(), $reqCabut->rules());
$reqCabut->setValidator($validatorCabut);

$respCabut = $controller->verifikasi($reqCabut, $lahan1->id_lahan);
assertTest($respCabut->isRedirect(), "Pencabutan verifikasi sukses melakukan redirect");
$lahan1->refresh();
assertTest($lahan1->status_verifikasi === 'perlu_perbaikan', "Status lahan berubah kembali menjadi perlu_perbaikan setelah dicabut");

// Skenario C: Sekretaris mencoba melakukan verifikasi (Ditolak AuthorizationException / 403)
$isSekreBlocked = false;
try {
    $reqVerifSekre = VerifikasiLahanRequest::create('/pengurus/lahan/' . $lahan1->id_lahan . '/verifikasi', 'POST', [
        'aksi' => 'setujui',
    ]);
    $reqVerifSekre->setUserResolver(fn () => $sekretarisPoktan1);
    $reqVerifSekre->setRouteResolver(function () use ($lahan1) {
        return new class($lahan1) {
            public function __construct(private $lahan) {}
            public function parameter($name) { return $name === 'lahan' ? $this->lahan->id_lahan : null; }
        };
    });
    $validatorSekre = app('validator')->make($reqVerifSekre->all(), $reqVerifSekre->rules());
    $reqVerifSekre->setValidator($validatorSekre);

    $controller->verifikasi($reqVerifSekre, $lahan1->id_lahan);
} catch (HttpException $e) {
    if ($e->getStatusCode() === 403) {
        $isSekreBlocked = true;
    }
}
assertTest($isSekreBlocked === true, "Sekretaris (Pengurus non-Ketua) DITOLAK memverifikasi lahan dengan HTTP 403");

// Skenario D: Ketua Poktan 2 mencoba memverifikasi lahan Poktan 1 (IDOR)
$isKetua2Blocked = false;
try {
    $reqVerifKetua2 = VerifikasiLahanRequest::create('/pengurus/lahan/' . $lahan1->id_lahan . '/verifikasi', 'POST', [
        'aksi' => 'setujui',
    ]);
    $reqVerifKetua2->setUserResolver(fn () => $ketuaPoktan2);
    $reqVerifKetua2->setRouteResolver(function () use ($lahan1) {
        return new class($lahan1) {
            public function __construct(private $lahan) {}
            public function parameter($name) { return $name === 'lahan' ? $this->lahan->id_lahan : null; }
        };
    });
    $validatorKetua2 = app('validator')->make($reqVerifKetua2->all(), $reqVerifKetua2->rules());
    $reqVerifKetua2->setValidator($validatorKetua2);

    $controller->verifikasi($reqVerifKetua2, $lahan1->id_lahan);
} catch (HttpException $e) {
    if ($e->getStatusCode() === 403) {
        $isKetua2Blocked = true;
    }
}
assertTest($isKetua2Blocked === true, "Ketua Poktan 2 DITOLAK memverifikasi lahan kelompok lain dengan HTTP 403");

echo "\n--- 4. Testing Non-Pengurus Access Rejection ---\n";
// Skenario A: Anggota mencoba mengakses controller Pengurus
$isAnggotaRejected = false;
try {
    $reqAnggota = Request::create('/pengurus/lahan', 'GET');
    $reqAnggota->setUserResolver(fn () => $anggotaPoktan1);
    $controller->index($reqAnggota);
} catch (HttpException $e) {
    if ($e->getStatusCode() === 403) {
        $isAnggotaRejected = true;
    }
}
assertTest($isAnggotaRejected === true, "Anggota ditolak mengakses Web Controller Pengurus dengan HTTP 403");

echo "\n--- 5. Testing Blade Views Rendering (Stage 3D-4 Verification) ---\n";
// Render Index View
$renderedIndex = $viewIndex->render();
assertTest(str_contains($renderedIndex, 'Kelola Lahan'), "Index View merender judul 'Kelola Lahan'");
assertTest(str_contains($renderedIndex, 'Total Lahan'), "Index View merender metrik 'Total Lahan'");
assertTest(str_contains($renderedIndex, 'Sawah Petak Lembah'), "Index View merender data baris lahan");
assertTest(!str_contains($renderedIndex, '3301234567890001'), "Index View TIDAK merender NIK anggota pada tabel");

// Render Show View as Ketua
$renderedShowKetua = $viewShowKetua1->render();
assertTest(str_contains($renderedShowKetua, 'Panel Verifikasi Ketua'), "Show View untuk Ketua merender Panel Verifikasi");
assertTest(str_contains($renderedShowKetua, 'name="aksi"'), "Show View untuk Ketua memiliki input aksi verifikasi");
assertTest(str_contains($renderedShowKetua, 'name="catatan"'), "Show View untuk Ketua memiliki textarea catatan");
assertTest(str_contains($renderedShowKetua, '3301234567890001'), "Ketua melihat NIK lengkap secara sah");
assertTest(str_contains($renderedShowKetua, "confirm('Apakah Anda yakin ingin menyimpan keputusan verifikasi untuk lahan ini?')"), "Pesan konfirmasi inline JS statis tanpa interpolasi dinamis nama lahan");
assertTest(!str_contains($renderedShowKetua, 'addslashes'), "Tidak ada interpolasi addslashes pada inline JS");

// Render Show View as Pengurus biasa (Sekretaris)
$renderedShowSekre = $viewShowSekre1->render();
assertTest(!str_contains($renderedShowSekre, 'Panel Verifikasi Ketua'), "Show View untuk Pengurus biasa TIDAK merender Panel Verifikasi");
assertTest(!str_contains($renderedShowSekre, 'name="aksi"'), "Show View untuk Pengurus biasa TIDAK memiliki input form verifikasi");
assertTest(str_contains($renderedShowSekre, 'Akses Verifikasi Khusus Ketua'), "Show View untuk Pengurus biasa merender banner peringatan read-only");
assertTest(!str_contains($renderedShowSekre, '3301234567890001'), "Pengurus biasa TIDAK melihat NIK lengkap");
assertTest(str_contains($renderedShowSekre, '330123******0001'), "Pengurus biasa melihat NIK yang telah dimasking");

// Skenario 5B: NIK Kosong Tidak Menyebabkan Error
$anggotaNoNik = Pengguna::create([
    'id_pengguna' => 'USR-NO-NIK',
    'nama' => 'Petani Tanpa NIK',
    'username' => 'nonik_user',
    'role' => 'Anggota',
    'status' => 'Aktif',
    'id_kelompok' => 'KT-001',
    'password' => 'secret123',
]);
$lahanNoNik = Lahan::create([
    'id_lahan' => 'LHN-20261103-9999',
    'id_pengguna' => 'USR-NO-NIK',
    'id_kelompok' => 'KT-001',
    'nama_lahan' => 'Lahan Uji NIK Null',
    'luas' => 1.0,
    'alamat_lahan' => 'Dusun Uji',
    'status_operasional' => 'persiapan',
    'status_verifikasi' => 'menunggu_verifikasi',
]);
$reqShowNoNik = Request::create('/pengurus/lahan/' . $lahanNoNik->id_lahan, 'GET');
$reqShowNoNik->setUserResolver(fn () => $sekretarisPoktan1);
$renderedNoNik = $controller->show($reqShowNoNik, $lahanNoNik->id_lahan)->render();
assertTest(str_contains($renderedNoNik, '-'), "NIK kosong/null merender '-' tanpa error");

// Skenario 5C: URL Foto HTTP/HTTPS valid vs Skema Berbahaya
$lahanPhotoHttps = Lahan::create([
    'id_lahan' => 'LHN-20261103-8888',
    'id_pengguna' => 'USR-ANGGOTA-1',
    'id_kelompok' => 'KT-001',
    'nama_lahan' => 'Lahan Foto HTTPS',
    'luas' => 1.0,
    'alamat_lahan' => 'Dusun Foto',
    'status_operasional' => 'persiapan',
    'status_verifikasi' => 'menunggu_verifikasi',
    'foto_lahan' => 'https://res.cloudinary.com/demo/image/upload/sample.jpg',
]);
$reqPhotoHttps = Request::create('/pengurus/lahan/' . $lahanPhotoHttps->id_lahan, 'GET');
$reqPhotoHttps->setUserResolver(fn () => $ketuaPoktan1);
$renderedPhotoHttps = $controller->show($reqPhotoHttps, $lahanPhotoHttps->id_lahan)->render();
assertTest(str_contains($renderedPhotoHttps, 'href="https://res.cloudinary.com/demo/image/upload/sample.jpg"'), "URL foto HTTPS menghasilkan tautan aktif");
assertTest(str_contains($renderedPhotoHttps, 'target="_blank" rel="noopener noreferrer"'), "Tautan foto memiliki target _blank dan rel noopener noreferrer");

// Foto dengan skema berbahaya (javascript:)
$lahanPhotoEvil = Lahan::create([
    'id_lahan' => 'LHN-20261103-7777',
    'id_pengguna' => 'USR-ANGGOTA-1',
    'id_kelompok' => 'KT-001',
    'nama_lahan' => 'Lahan Foto Malicious',
    'luas' => 1.0,
    'alamat_lahan' => 'Dusun XSS',
    'status_operasional' => 'persiapan',
    'status_verifikasi' => 'menunggu_verifikasi',
    'foto_lahan' => 'javascript:alert(1)',
]);
$reqPhotoEvil = Request::create('/pengurus/lahan/' . $lahanPhotoEvil->id_lahan, 'GET');
$reqPhotoEvil->setUserResolver(fn () => $ketuaPoktan1);
$renderedPhotoEvil = $controller->show($reqPhotoEvil, $lahanPhotoEvil->id_lahan)->render();
assertTest(!str_contains($renderedPhotoEvil, 'href="javascript:alert(1)"'), "URL skema berbahaya TIDAK menghasilkan tautan aktif");

echo "\n=========================================================\n";
echo "SUMMARY: Passed = {$passed}, Failed = {$failed}\n";
echo "=========================================================\n";

exit($failed === 0 ? 0 : 1);
