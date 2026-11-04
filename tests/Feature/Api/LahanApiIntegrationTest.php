<?php

namespace Tests\Feature\Api;

use App\Jobs\DeleteCloudinaryPhotoJob;
use App\Models\KelompokTani;
use App\Models\Komoditas;
use App\Models\Lahan;
use App\Models\Pengguna;
use App\Models\RiwayatVerifikasiLahan;
use App\Services\CloudinaryService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class LahanApiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected KelompokTani $poktan1;
    protected KelompokTani $poktan2;
    protected Komoditas $komoditasPadi;
    protected Komoditas $komoditasJagung;

    protected Pengguna $anggota1;
    protected Pengguna $anggota2;
    protected Pengguna $anggotaTanpaKelompok;
    protected Pengguna $anggotaNonaktif;
    protected Pengguna $pengurusKetua;
    protected Pengguna $adminUser;
    protected Pengguna $pplUser;

    protected Lahan $lahanMenunggu;
    protected Lahan $lahanTerverifikasi;
    protected Lahan $lahanPerluPerbaikan;
    protected Lahan $lahanAnggota2;

    protected $cloudinaryMock;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Mock CloudinaryService agar tidak pernah menghubungi jaringan eksternal
        $this->cloudinaryMock = Mockery::mock(CloudinaryService::class);
        $this->cloudinaryMock->shouldReceive('upload')
            ->andReturn('https://res.cloudinary.com/demo/image/upload/sample.jpg')
            ->byDefault();
        $this->cloudinaryMock->shouldReceive('delete')
            ->andReturn(true)
            ->byDefault();
        $this->app->instance(CloudinaryService::class, $this->cloudinaryMock);

        // 2. Setup Fixture Kelompok Tani
        $this->poktan1 = KelompokTani::create([
            'id_kelompok' => 'KT-API-001',
            'nama_kelompok' => 'Kelompok Tani Subur Makmur',
            'alamat' => 'Desa Sukamaju No. 10',
            'status' => 'Aktif',
        ]);

        $this->poktan2 = KelompokTani::create([
            'id_kelompok' => 'KT-API-002',
            'nama_kelompok' => 'Kelompok Tani Tani Jaya',
            'alamat' => 'Desa Tani Makmur No. 20',
            'status' => 'Aktif',
        ]);

        // 3. Setup Fixture Komoditas
        $this->komoditasPadi = Komoditas::create([
            'id_komoditas' => 'KMD-001',
            'nama_komoditas' => 'Padi Ciherang',
            'kategori' => 'Pangan',
        ]);

        $this->komoditasJagung = Komoditas::create([
            'id_komoditas' => 'KMD-002',
            'nama_komoditas' => 'Jagung Hibrida',
            'kategori' => 'Pangan',
        ]);

        // 4. Setup Fixture Pengguna
        $this->anggota1 = Pengguna::create([
            'id_pengguna' => 'USR-ANGGOTA-001',
            'nama' => 'Budi Santoso',
            'username' => 'budi_anggota',
            'email' => 'budi@pawontani.test',
            'no_telepon' => '081234567891',
            'role' => 'Anggota',
            'status' => 'Aktif',
            'id_kelompok' => $this->poktan1->id_kelompok,
            'password' => bcrypt('password123'),
        ]);

        $this->anggota2 = Pengguna::create([
            'id_pengguna' => 'USR-ANGGOTA-002',
            'nama' => 'Siti Rahayu',
            'username' => 'siti_anggota',
            'email' => 'siti@pawontani.test',
            'no_telepon' => '081234567892',
            'role' => 'Anggota',
            'status' => 'Aktif',
            'id_kelompok' => $this->poktan1->id_kelompok,
            'password' => bcrypt('password123'),
        ]);

        $this->anggotaTanpaKelompok = Pengguna::create([
            'id_pengguna' => 'USR-ANGGOTA-003',
            'nama' => 'Rudi Hermawan',
            'username' => 'rudi_solo',
            'email' => 'rudi@pawontani.test',
            'no_telepon' => '081234567893',
            'role' => 'Anggota',
            'status' => 'Aktif',
            'id_kelompok' => null,
            'password' => bcrypt('password123'),
        ]);

        $this->anggotaNonaktif = Pengguna::create([
            'id_pengguna' => 'USR-ANGGOTA-004',
            'nama' => 'Agus Triyono',
            'username' => 'agus_nonaktif',
            'email' => 'agus@pawontani.test',
            'no_telepon' => '081234567894',
            'role' => 'Anggota',
            'status' => 'Nonaktif',
            'id_kelompok' => $this->poktan1->id_kelompok,
            'password' => bcrypt('password123'),
        ]);

        $this->pengurusKetua = Pengguna::create([
            'id_pengguna' => 'USR-KETUA-001',
            'nama' => 'Pak RT Widodo',
            'username' => 'ketua_widodo',
            'email' => 'ketua@pawontani.test',
            'no_telepon' => '081234567895',
            'role' => 'Pengurus',
            'jabatan' => 'Ketua',
            'status' => 'Aktif',
            'id_kelompok' => $this->poktan1->id_kelompok,
            'password' => bcrypt('password123'),
        ]);

        $this->adminUser = Pengguna::create([
            'id_pengguna' => 'USR-ADMIN-001',
            'nama' => 'Super Admin',
            'username' => 'admin_super',
            'email' => 'admin@pawontani.test',
            'role' => 'Admin',
            'status' => 'Aktif',
            'id_kelompok' => null,
            'password' => bcrypt('password123'),
        ]);

        $this->pplUser = Pengguna::create([
            'id_pengguna' => 'USR-PPL-001',
            'nama' => 'PPL Lapangan',
            'username' => 'ppl_lapangan',
            'email' => 'ppl@pawontani.test',
            'role' => 'PPL',
            'status' => 'Aktif',
            'id_kelompok' => null,
            'password' => bcrypt('password123'),
        ]);

        // 5. Setup Fixture Lahan
        $this->lahanMenunggu = Lahan::create([
            'id_lahan' => 'LHN-TEST-0001',
            'nama_lahan' => 'Sawah Petak Lembah',
            'luas' => 1.50,
            'alamat_lahan' => 'Dusun Krajan RT 01',
            'latitude' => -7.123456,
            'longitude' => 110.123456,
            'id_komoditas' => $this->komoditasPadi->id_komoditas,
            'id_pengguna' => $this->anggota1->id_pengguna,
            'id_kelompok' => $this->poktan1->id_kelompok,
            'status_verifikasi' => 'menunggu_verifikasi',
            'status_operasional' => 'persiapan',
            'nomor_revisi' => 1,
            'foto_lahan' => 'https://res.cloudinary.com/demo/image/upload/foto_lama_001.jpg',
        ]);

        $this->lahanTerverifikasi = Lahan::create([
            'id_lahan' => 'LHN-TEST-0002',
            'nama_lahan' => 'Kebun Jagung Lereng',
            'luas' => 0.75,
            'alamat_lahan' => 'Dusun Krajan RT 02',
            'latitude' => -7.223456,
            'longitude' => 110.223456,
            'id_komoditas' => $this->komoditasJagung->id_komoditas,
            'id_pengguna' => $this->anggota1->id_pengguna,
            'id_kelompok' => $this->poktan1->id_kelompok,
            'status_verifikasi' => 'terverifikasi',
            'status_operasional' => 'persiapan',
            'verifikator_id' => $this->pengurusKetua->id_pengguna,
            'tanggal_verifikasi' => now()->subDay(),
            'catatan_verifikasi' => 'Lahan terverifikasi sah.',
            'nomor_revisi' => 1,
        ]);

        $this->lahanPerluPerbaikan = Lahan::create([
            'id_lahan' => 'LHN-TEST-0003',
            'nama_lahan' => 'Sawah Irigasi Timur',
            'luas' => 2.00,
            'alamat_lahan' => 'Dusun Timur RT 03',
            'latitude' => -7.323456,
            'longitude' => 110.323456,
            'id_komoditas' => $this->komoditasPadi->id_komoditas,
            'id_pengguna' => $this->anggota1->id_pengguna,
            'id_kelompok' => $this->poktan1->id_kelompok,
            'status_verifikasi' => 'perlu_perbaikan',
            'status_operasional' => 'persiapan',
            'catatan_verifikasi' => 'Foto batas lahan kurang jelas.',
            'nomor_revisi' => 1,
        ]);

        $this->lahanAnggota2 = Lahan::create([
            'id_lahan' => 'LHN-TEST-0004',
            'nama_lahan' => 'Sawah Anggota Siti',
            'luas' => 1.20,
            'alamat_lahan' => 'Dusun Krajan RT 04',
            'id_komoditas' => $this->komoditasPadi->id_komoditas,
            'id_pengguna' => $this->anggota2->id_pengguna,
            'id_kelompok' => $this->poktan1->id_kelompok,
            'status_verifikasi' => 'menunggu_verifikasi',
            'status_operasional' => 'persiapan',
            'nomor_revisi' => 1,
        ]);

        // Audit trail awal
        RiwayatVerifikasiLahan::create([
            'id_lahan' => $this->lahanMenunggu->id_lahan,
            'id_pengguna' => $this->anggota1->id_pengguna,
            'aksi' => 'pengajuan_baru',
            'status_sebelumnya' => null,
            'status_sesudahnya' => 'menunggu_verifikasi',
            'catatan' => 'Pendaftaran lahan awal',
            'nomor_revisi' => 1,
            'snapshot_data' => $this->lahanMenunggu->toArray(),
        ]);
    }

    // =========================================================================
    // KELOMPOK 1: AUTENTIKASI (HTTP 401 UNAUTHORIZED)
    // =========================================================================

    public function test_unauthenticated_request_without_token_returns_401_json(): void
    {
        $response = $this->getJson('/api/v1/lahan');

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function test_invalid_or_expired_bearer_token_returns_401_json(): void
    {
        $response = $this->withToken('palsu-token-invalid-12345')
            ->getJson('/api/v1/lahan');

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    // =========================================================================
    // KELOMPOK 2: OTORISASI ROLE DAN STATUS (HTTP 403 FORBIDDEN)
    // =========================================================================

    public function test_inactive_anggota_is_rejected_on_all_endpoints_with_403(): void
    {
        Sanctum::actingAs($this->anggotaNonaktif, ['*']);

        // 1. Index
        $resIndex = $this->getJson('/api/v1/lahan');
        $resIndex->assertStatus(403)->assertJson(['success' => false]);

        // 2. Show
        $resShow = $this->getJson("/api/v1/lahan/{$this->lahanMenunggu->id_lahan}");
        $resShow->assertStatus(403)->assertJson(['success' => false]);

        // 3. Store
        $resStore = $this->postJson('/api/v1/lahan', [
            'nama_lahan' => 'Lahan Baru',
            'luas' => 1.0,
            'alamat_lahan' => 'Alamat',
        ]);
        $resStore->assertStatus(403);

        // 4. Update
        $resUpdate = $this->putJson("/api/v1/lahan/{$this->lahanMenunggu->id_lahan}", [
            'nama_lahan' => 'Lahan Update',
        ]);
        $resUpdate->assertStatus(403);

        // 5. Destroy
        $resDestroy = $this->deleteJson("/api/v1/lahan/{$this->lahanMenunggu->id_lahan}");
        $resDestroy->assertStatus(403)->assertJson(['success' => false]);
    }

    public function test_pengurus_role_is_rejected_on_api_lahan_endpoints_with_403(): void
    {
        Sanctum::actingAs($this->pengurusKetua, ['*']);

        // Index harus ditolak (khusus anggota mobile)
        $this->getJson('/api/v1/lahan')
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Show harus ditolak
        $this->getJson("/api/v1/lahan/{$this->lahanMenunggu->id_lahan}")
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Store harus ditolak
        $this->postJson('/api/v1/lahan', [
            'nama_lahan' => 'Lahan Pengurus',
            'luas' => 1.0,
            'alamat_lahan' => 'Alamat',
        ])->assertStatus(403);
    }

    public function test_admin_and_ppl_roles_are_rejected_with_403(): void
    {
        // Admin
        Sanctum::actingAs($this->adminUser, ['*']);
        $this->getJson('/api/v1/lahan')
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // PPL
        Sanctum::actingAs($this->pplUser, ['*']);
        $this->getJson('/api/v1/lahan')
            ->assertStatus(403)
            ->assertJson(['success' => false]);
    }

    public function test_anggota_without_kelompok_tani_cannot_create_lahan_403(): void
    {
        Sanctum::actingAs($this->anggotaTanpaKelompok, ['*']);

        $response = $this->postJson('/api/v1/lahan', [
            'nama_lahan' => 'Lahan Mandiri',
            'luas' => 1.0,
            'alamat_lahan' => 'Alamat',
        ]);

        $response->assertStatus(403);
    }

    // =========================================================================
    // KELOMPOK 3: GET /api/v1/lahan (INDEX, FILTER, PENCARIAN, PAGINASI, ISOLASI)
    // =========================================================================

    public function test_active_anggota_can_get_paginated_list_of_own_lahan(): void
    {
        Sanctum::actingAs($this->anggota1, ['*']);

        $response = $this->getJson('/api/v1/lahan');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Daftar lahan berhasil diambil.',
        ]);

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertCount(3, $data);
        $this->assertEquals(3, $response->json('meta.total'));
    }

    public function test_anggota_cannot_see_other_anggota_lahan_in_index(): void
    {
        Sanctum::actingAs($this->anggota1, ['*']);

        $response = $this->getJson('/api/v1/lahan');
        $response->assertStatus(200);

        $ids = collect($response->json('data'))->pluck('id_lahan')->all();
        $this->assertContains('LHN-TEST-0001', $ids);
        $this->assertContains('LHN-TEST-0002', $ids);
        $this->assertContains('LHN-TEST-0003', $ids);
        // Lahan anggota2 tidak boleh muncul
        $this->assertNotContains('LHN-TEST-0004', $ids);
    }

    public function test_filter_by_status_verifikasi(): void
    {
        Sanctum::actingAs($this->anggota1, ['*']);

        $response = $this->getJson('/api/v1/lahan?status_verifikasi=terverifikasi');
        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('LHN-TEST-0002', $data[0]['id_lahan']);
        $this->assertEquals('terverifikasi', $data[0]['status_verifikasi']);
    }

    public function test_filter_by_id_komoditas(): void
    {
        Sanctum::actingAs($this->anggota1, ['*']);

        $response = $this->getJson('/api/v1/lahan?id_komoditas=KMD-001');
        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(2, $data);
        $ids = collect($data)->pluck('id_lahan')->all();
        $this->assertContains('LHN-TEST-0001', $ids);
        $this->assertContains('LHN-TEST-0003', $ids);
    }

    public function test_search_by_lahan_name_and_address(): void
    {
        Sanctum::actingAs($this->anggota1, ['*']);

        // Cari berdasarkan nama lahan
        $resName = $this->getJson('/api/v1/lahan?q=Lembah');
        $resName->assertStatus(200);
        $dataName = $resName->json('data');
        $this->assertCount(1, $dataName);
        $this->assertEquals('LHN-TEST-0001', $dataName[0]['id_lahan']);

        // Cari berdasarkan alamat
        $resAddr = $this->getJson('/api/v1/lahan?q=Timur');
        $resAddr->assertStatus(200);
        $dataAddr = $resAddr->json('data');
        $this->assertCount(1, $dataAddr);
        $this->assertEquals('LHN-TEST-0003', $dataAddr[0]['id_lahan']);
    }

    public function test_pagination_meta_and_links_boundary(): void
    {
        Sanctum::actingAs($this->anggota1, ['*']);

        // per_page > 50 harus dibatasi maksimal 50
        $resMax = $this->getJson('/api/v1/lahan?per_page=100');
        $resMax->assertStatus(200);
        $this->assertEquals(50, $resMax->json('meta.per_page'));

        // per_page < 1 harus diatur ke default 10
        $resMin = $this->getJson('/api/v1/lahan?per_page=0');
        $resMin->assertStatus(200);
        $this->assertEquals(10, $resMin->json('meta.per_page'));

        // Struktur meta dan links harus lengkap
        $this->assertNotNull($resMin->json('meta.current_page'));
        $this->assertNotNull($resMin->json('meta.last_page'));
        $this->assertNotNull($resMin->json('links.first'));
        $this->assertNotNull($resMin->json('links.last'));
    }

    // =========================================================================
    // KELOMPOK 4: GET /api/v1/lahan/{lahan} (DETAIL, RELASI, KEAMANAN DATA)
    // =========================================================================

    public function test_anggota_can_view_own_lahan_detail_with_relations(): void
    {
        Sanctum::actingAs($this->anggota1, ['*']);

        $response = $this->getJson("/api/v1/lahan/{$this->lahanMenunggu->id_lahan}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Detail lahan berhasil diambil.',
            'data' => [
                'id_lahan' => 'LHN-TEST-0001',
                'nama_lahan' => 'Sawah Petak Lembah',
                'luas' => 1.50,
                'status_verifikasi' => 'menunggu_verifikasi',
                'is_can_edit' => true,
                'is_can_delete' => true,
            ],
        ]);

        $data = $response->json('data');
        $this->assertArrayHasKey('komoditas', $data);
        $this->assertEquals('Padi Ciherang', $data['komoditas']['nama_komoditas']);
        $this->assertArrayHasKey('riwayat_verifikasi', $data);
        $this->assertCount(1, $data['riwayat_verifikasi']);
    }

    public function test_anggota_cannot_view_other_anggota_lahan_returns_404(): void
    {
        Sanctum::actingAs($this->anggota1, ['*']);

        // Mengakses lahan anggota2
        $response = $this->getJson("/api/v1/lahan/{$this->lahanAnggota2->id_lahan}");

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Data lahan tidak ditemukan atau Anda tidak memiliki akses.',
        ]);
    }

    public function test_non_existent_lahan_id_returns_404(): void
    {
        Sanctum::actingAs($this->anggota1, ['*']);

        $response = $this->getJson('/api/v1/lahan/LHN-999999');

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Data lahan tidak ditemukan atau Anda tidak memiliki akses.',
        ]);
    }

    public function test_riwayat_verifikasi_does_not_leak_internal_snapshot_or_credentials(): void
    {
        Sanctum::actingAs($this->anggota1, ['*']);

        $response = $this->getJson("/api/v1/lahan/{$this->lahanMenunggu->id_lahan}");
        $response->assertStatus(200);

        $riwayat = $response->json('data.riwayat_verifikasi.0');
        $this->assertNotNull($riwayat);

        // snapshot_data tidak boleh ada di JSON response
        $this->assertArrayNotHasKey('snapshot_data', $riwayat);

        // Aktor tidak boleh membocorkan password hash, NIK, atau no telepon
        if (isset($riwayat['aktor'])) {
            $this->assertArrayNotHasKey('password', $riwayat['aktor']);
            $this->assertArrayNotHasKey('nik', $riwayat['aktor']);
            $this->assertArrayNotHasKey('no_telepon', $riwayat['aktor']);
        }
    }

    // =========================================================================
    // KELOMPOK 5: POST /api/v1/lahan (PENDAFTARAN LAHAN & VALIDASI)
    // =========================================================================

    public function test_anggota_can_create_lahan_with_valid_data_and_photo(): void
    {
        Sanctum::actingAs($this->anggota1, ['*']);

        $photo = UploadedFile::fake()->image('lahan_baru.jpg', 640, 480);

        $payload = [
            'nama_lahan' => 'Sawah Baru Uji Coba',
            'luas' => 1.75,
            'alamat_lahan' => 'Desa Sukamaju Dusun 3',
            'latitude' => -7.1234,
            'longitude' => 110.5678,
            'id_komoditas' => $this->komoditasPadi->id_komoditas,
            'musim_tanam' => 'Musim Hujan 2026',
            'foto_lahan' => $photo,
            'keterangan' => 'Lahan sawah irigasi teknis',
        ];

        $response = $this->postJson('/api/v1/lahan', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Lahan berhasil didaftarkan dan menunggu proses verifikasi.',
            'data' => [
                'nama_lahan' => 'Sawah Baru Uji Coba',
                'luas' => 1.75,
                'status_verifikasi' => 'menunggu_verifikasi',
                'status_operasional' => 'persiapan',
                'nomor_revisi' => 1,
            ],
        ]);

        $this->assertDatabaseHas('lahan', [
            'nama_lahan' => 'Sawah Baru Uji Coba',
            'id_pengguna' => $this->anggota1->id_pengguna,
            'id_kelompok' => $this->poktan1->id_kelompok,
            'status_verifikasi' => 'menunggu_verifikasi',
            'nomor_revisi' => 1,
        ]);
    }

    public function test_create_lahan_validates_required_fields_and_formats_422(): void
    {
        Sanctum::actingAs($this->anggota1, ['*']);

        // Request kosong
        $response = $this->postJson('/api/v1/lahan', []);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors' => ['nama_lahan', 'luas', 'alamat_lahan'],
        ]);
        $this->assertFalse($response->json('success'));

        // Validasi format angka dan koordinat
        $resInvalid = $this->postJson('/api/v1/lahan', [
            'nama_lahan' => 'Tes',
            'luas' => -5,
            'alamat_lahan' => 'Jalan',
            'latitude' => 150, // latitude maksimal 90
            'longitude' => 200, // longitude maksimal 180
            'id_komoditas' => 'KMD-TIDAK-ADA',
        ]);

        $resInvalid->assertStatus(422);
        $resInvalid->assertJsonValidationErrors(['luas', 'latitude', 'longitude', 'id_komoditas']);
    }

    public function test_create_lahan_validates_photo_mimes_and_max_size_422(): void
    {
        Sanctum::actingAs($this->anggota1, ['*']);

        // File bukan gambar (PDF)
        $pdfFile = UploadedFile::fake()->create('dokumen.pdf', 500, 'application/pdf');
        $resPdf = $this->postJson('/api/v1/lahan', [
            'nama_lahan' => 'Tes Lahan',
            'luas' => 1.0,
            'alamat_lahan' => 'Alamat',
            'foto_lahan' => $pdfFile,
        ]);
        $resPdf->assertStatus(422);
        $resPdf->assertJsonValidationErrors(['foto_lahan']);

        // File gambar melebihi 2MB (2048 KB)
        $hugeImage = UploadedFile::fake()->image('huge.jpg')->size(3000);
        $resHuge = $this->postJson('/api/v1/lahan', [
            'nama_lahan' => 'Tes Lahan',
            'luas' => 1.0,
            'alamat_lahan' => 'Alamat',
            'foto_lahan' => $hugeImage,
        ]);
        $resHuge->assertStatus(422);
        $resHuge->assertJsonValidationErrors(['foto_lahan']);
    }

    public function test_create_lahan_protects_against_mass_assignment_and_sets_initial_workflow(): void
    {
        Sanctum::actingAs($this->anggota1, ['*']);

        // Mencoba memanipulasi field internal
        $payload = [
            'nama_lahan' => 'Lahan Eksploitasi Mass Assignment',
            'luas' => 2.50,
            'alamat_lahan' => 'Dusun Krajan',
            'status_verifikasi' => 'terverifikasi', // HACK ATTEMPT
            'nomor_revisi' => 99,                   // HACK ATTEMPT
            'status_operasional' => 'panen',        // HACK ATTEMPT
            'id_pengguna' => 'USR-HACKER',          // HACK ATTEMPT
            'id_kelompok' => 'KT-HACKER',           // HACK ATTEMPT
            'verifikator_id' => 'USR-HACKER',       // HACK ATTEMPT
        ];

        $response = $this->postJson('/api/v1/lahan', $payload);

        $response->assertStatus(201);

        $createdId = $response->json('data.id_lahan');

        // Verifikasi database: field yang diinjeksi dibersihkan oleh server
        $this->assertDatabaseHas('lahan', [
            'id_lahan' => $createdId,
            'id_pengguna' => $this->anggota1->id_pengguna,
            'id_kelompok' => $this->poktan1->id_kelompok,
            'status_verifikasi' => 'menunggu_verifikasi',
            'status_operasional' => 'persiapan',
            'nomor_revisi' => 1,
            'verifikator_id' => null,
        ]);

        // Verifikasi audit trail pengajuan baru tercatat
        $this->assertDatabaseHas('riwayat_verifikasi_lahan', [
            'id_lahan' => $createdId,
            'id_pengguna' => $this->anggota1->id_pengguna,
            'aksi' => 'pengajuan_baru',
            'status_sesudahnya' => 'menunggu_verifikasi',
            'nomor_revisi' => 1,
        ]);
    }

    // =========================================================================
    // KELOMPOK 6: PUT/PATCH & MULTIPART UPDATE (PEMBARUAN & RESUBMISI)
    // =========================================================================

    public function test_anggota_can_update_own_lahan_via_json_put(): void
    {
        Sanctum::actingAs($this->anggota1, ['*']);

        $response = $this->putJson("/api/v1/lahan/{$this->lahanMenunggu->id_lahan}", [
            'nama_lahan' => 'Sawah Petak Lembah Diperbarui',
            'luas' => 1.80,
            'alamat_lahan' => 'Dusun Krajan RT 01 Baru',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Data lahan berhasil diperbarui.',
            'data' => [
                'id_lahan' => 'LHN-TEST-0001',
                'nama_lahan' => 'Sawah Petak Lembah Diperbarui',
                'luas' => 1.80,
            ],
        ]);

        $this->assertDatabaseHas('lahan', [
            'id_lahan' => 'LHN-TEST-0001',
            'nama_lahan' => 'Sawah Petak Lembah Diperbarui',
            'luas' => 1.80,
        ]);
    }

    public function test_anggota_can_update_lahan_with_photo_via_multipart_post_method_spoofing(): void
    {
        Sanctum::actingAs($this->anggota1, ['*']);

        $updatedPhotoUrl = 'https://res.cloudinary.com/demo/image/upload/sample_updated.jpg';
        $this->cloudinaryMock->shouldReceive('upload')
            ->once()
            ->andReturn($updatedPhotoUrl);

        $newPhoto = UploadedFile::fake()->image('updated_photo.jpg', 800, 600);

        // Method spoofing: POST dengan field _method=PUT
        $response = $this->postJson("/api/v1/lahan/{$this->lahanMenunggu->id_lahan}", [
            '_method' => 'PUT',
            'nama_lahan' => 'Sawah Lembah Dengan Foto Baru',
            'foto_lahan' => $newPhoto,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'nama_lahan' => 'Sawah Lembah Dengan Foto Baru',
                'foto_lahan' => $updatedPhotoUrl,
            ],
        ]);

        $this->assertDatabaseHas('lahan', [
            'id_lahan' => 'LHN-TEST-0001',
            'nama_lahan' => 'Sawah Lembah Dengan Foto Baru',
            'foto_lahan' => $updatedPhotoUrl,
        ]);
    }

    public function test_anggota_cannot_update_other_anggota_lahan_returns_403(): void
    {
        Sanctum::actingAs($this->anggota1, ['*']);

        // Mengubah lahan milik anggota2
        $response = $this->putJson("/api/v1/lahan/{$this->lahanAnggota2->id_lahan}", [
            'nama_lahan' => 'Upaya Pembajakan Lahan',
        ]);

        $response->assertStatus(403);
    }

    public function test_updating_terverifikasi_or_perlu_perbaikan_lahan_triggers_resubmission(): void
    {
        Sanctum::actingAs($this->anggota1, ['*']);

        // 1. Mengubah lahan yang sudah terverifikasi (LHN-TEST-0002)
        $resTerverifikasi = $this->putJson("/api/v1/lahan/{$this->lahanTerverifikasi->id_lahan}", [
            'nama_lahan' => 'Kebun Jagung Lereng Revisi',
            'keterangan' => 'Mengubah batas patok timur',
        ]);

        $resTerverifikasi->assertStatus(200);
        $resTerverifikasi->assertJson([
            'success' => true,
            'data' => [
                'status_verifikasi' => 'menunggu_verifikasi',
                'nomor_revisi' => 2, // Revisi naik dari 1 ke 2
            ],
        ]);

        $this->assertDatabaseHas('lahan', [
            'id_lahan' => 'LHN-TEST-0002',
            'status_verifikasi' => 'menunggu_verifikasi',
            'verifikator_id' => null,
            'tanggal_verifikasi' => null,
            'nomor_revisi' => 2,
        ]);

        $this->assertDatabaseHas('riwayat_verifikasi_lahan', [
            'id_lahan' => 'LHN-TEST-0002',
            'aksi' => 'pengajuan_ulang',
            'status_sebelumnya' => 'terverifikasi',
            'status_sesudahnya' => 'menunggu_verifikasi',
            'nomor_revisi' => 2,
        ]);

        // 2. Mengubah lahan yang berstatus perlu_perbaikan (LHN-TEST-0003)
        $resPerbaikan = $this->putJson("/api/v1/lahan/{$this->lahanPerluPerbaikan->id_lahan}", [
            'nama_lahan' => 'Sawah Irigasi Timur Selesai Diperbaiki',
            'keterangan' => 'Foto dan batas lahan telah diperbaiki',
        ]);

        $resPerbaikan->assertStatus(200);
        $resPerbaikan->assertJson([
            'success' => true,
            'data' => [
                'status_verifikasi' => 'menunggu_verifikasi',
                'nomor_revisi' => 2,
            ],
        ]);

        $this->assertDatabaseHas('lahan', [
            'id_lahan' => 'LHN-TEST-0003',
            'status_verifikasi' => 'menunggu_verifikasi',
            'nomor_revisi' => 2,
        ]);

        $this->assertDatabaseHas('riwayat_verifikasi_lahan', [
            'id_lahan' => 'LHN-TEST-0003',
            'aksi' => 'pengajuan_ulang',
            'status_sebelumnya' => 'perlu_perbaikan',
            'status_sesudahnya' => 'menunggu_verifikasi',
            'nomor_revisi' => 2,
        ]);
    }

    // =========================================================================
    // KELOMPOK 7: DELETE /api/v1/lahan/{lahan} (PENGHAPUSAN LAHAN)
    // =========================================================================

    public function test_anggota_can_soft_delete_unverified_lahan(): void
    {
        Sanctum::actingAs($this->anggota1, ['*']);

        $response = $this->deleteJson("/api/v1/lahan/{$this->lahanMenunggu->id_lahan}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Lahan berhasil dihapus.',
        ]);

        // Baris masih ada di database tapi deleted_at terisi (Soft Delete)
        $this->assertSoftDeleted('lahan', [
            'id_lahan' => 'LHN-TEST-0001',
        ]);

        // Riwayat penghapusan tercatat
        $this->assertDatabaseHas('riwayat_verifikasi_lahan', [
            'id_lahan' => 'LHN-TEST-0001',
            'aksi' => 'penghapusan_lahan',
            'status_sesudahnya' => 'dihapus',
        ]);
    }

    public function test_anggota_cannot_delete_terverifikasi_lahan_returns_422(): void
    {
        Sanctum::actingAs($this->anggota1, ['*']);

        $response = $this->deleteJson("/api/v1/lahan/{$this->lahanTerverifikasi->id_lahan}");

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Lahan yang sudah terverifikasi tidak dapat dihapus.',
        ]);

        // Lahan tidak boleh terhapus
        $this->assertDatabaseHas('lahan', [
            'id_lahan' => 'LHN-TEST-0002',
            'deleted_at' => null,
        ]);
    }

    public function test_anggota_cannot_delete_other_anggota_lahan_returns_403(): void
    {
        Sanctum::actingAs($this->anggota1, ['*']);

        // Menghapus lahan anggota2
        $response = $this->deleteJson("/api/v1/lahan/{$this->lahanAnggota2->id_lahan}");

        $response->assertStatus(403);
    }

    // =========================================================================
    // KELOMPOK 8: CLOUDINARY RESILIENCY, KOMPENSASI & ANTREAN PENGHAPUSAN FOTO
    // =========================================================================

    public function test_cloudinary_upload_failure_aborts_creation_without_db_changes(): void
    {
        Sanctum::actingAs($this->anggota1, ['*']);

        // Simulasikan kegagalan jaringan Cloudinary
        $this->cloudinaryMock->shouldReceive('upload')
            ->once()
            ->andThrow(new Exception('Koneksi timeout ke Cloudinary'));

        $photo = UploadedFile::fake()->image('lahan_gagal.jpg');

        $response = $this->postJson('/api/v1/lahan', [
            'nama_lahan' => 'Lahan Gagal Jaringan Cloudinary',
            'luas' => 1.0,
            'alamat_lahan' => 'Alamat',
            'foto_lahan' => $photo,
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'Terjadi kesalahan sistem saat mendaftarkan lahan. Silakan coba beberapa saat lagi.',
        ]);

        // Tidak boleh ada data yang tertinggal di database
        $this->assertDatabaseMissing('lahan', [
            'nama_lahan' => 'Lahan Gagal Jaringan Cloudinary',
        ]);
    }

    public function test_db_transaction_failure_triggers_cloudinary_photo_compensation_delete(): void
    {
        Sanctum::actingAs($this->anggota1, ['*']);

        $uploadedUrl = 'https://res.cloudinary.com/demo/image/upload/kompensasi_target.jpg';
        $this->cloudinaryMock->shouldReceive('upload')
            ->once()
            ->andReturn($uploadedUrl);

        // Kompensasi delete HARUS dipanggil jika DB gagal
        $this->cloudinaryMock->shouldReceive('delete')
            ->once()
            ->with($uploadedUrl)
            ->andReturn(true);

        // Pasang event listener sementara agar save Lahan melempar exception di dalam transaksi DB
        Lahan::saving(function () {
            throw new Exception('Simulasi kegagalan database saat transaksi');
        });

        $photo = UploadedFile::fake()->image('kompensasi.jpg');

        try {
            $this->postJson('/api/v1/lahan', [
                'nama_lahan' => 'Lahan Uji Kompensasi',
                'luas' => 1.0,
                'alamat_lahan' => 'Alamat Uji',
                'foto_lahan' => $photo,
            ]);
        } finally {
            // Bersihkan listener agar tidak memengaruhi test lain
            Lahan::flushEventListeners();
        }

        // Pastikan tidak ada data tersimpan di database
        $this->assertDatabaseMissing('lahan', [
            'nama_lahan' => 'Lahan Uji Kompensasi',
        ]);
    }

    public function test_old_photo_scheduled_for_deletion_after_successful_commit(): void
    {
        Queue::fake();
        Sanctum::actingAs($this->anggota1, ['*']);

        $newPhotoUrl = 'https://res.cloudinary.com/demo/image/upload/foto_baru_commit.jpg';
        $this->cloudinaryMock->shouldReceive('upload')
            ->once()
            ->andReturn($newPhotoUrl);

        $newPhoto = UploadedFile::fake()->image('ganti_foto.jpg');

        // LHN-TEST-0001 memiliki foto lama: 'https://res.cloudinary.com/demo/image/upload/foto_lama_001.jpg'
        $response = $this->postJson("/api/v1/lahan/{$this->lahanMenunggu->id_lahan}", [
            '_method' => 'PUT',
            'nama_lahan' => 'Sawah Foto Baru Selesai',
            'foto_lahan' => $newPhoto,
        ]);

        $response->assertStatus(200);

        // Pastikan job DeleteCloudinaryPhotoJob dipush ke antrean dengan URL foto lama
        Queue::assertPushed(DeleteCloudinaryPhotoJob::class, function ($job) {
            return $job->photoUrl === 'https://res.cloudinary.com/demo/image/upload/foto_lama_001.jpg';
        });
    }
}
