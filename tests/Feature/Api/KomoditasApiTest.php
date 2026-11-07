<?php

namespace Tests\Feature\Api;

use App\Models\KelompokTani;
use App\Models\Komoditas;
use App\Models\Lahan;
use App\Models\Pengguna;
use App\Services\CloudinaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class KomoditasApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $cloudinaryMock = Mockery::mock(CloudinaryService::class);
        $cloudinaryMock->shouldReceive('upload')->andReturn('https://res.cloudinary.com/demo/image/upload/sample.jpg')->byDefault();
        $cloudinaryMock->shouldReceive('delete')->andReturn(true)->byDefault();
        $this->app->instance(CloudinaryService::class, $cloudinaryMock);
    }

    public function test_get_komoditas_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/komoditas');

        $response->assertStatus(401);
    }

    public function test_get_komoditas_returns_sorted_list_for_authenticated_user(): void
    {
        $user = Pengguna::create([
            'id_pengguna' => 'USR-TEST-001',
            'nama' => 'Test Anggota',
            'username' => 'test_anggota',
            'email' => 'test@pawontani.test',
            'role' => 'Anggota',
            'status' => 'Aktif',
            'password' => bcrypt('password123'),
        ]);

        Komoditas::create([
            'id_komoditas' => 'kmdt-0002',
            'nama_komoditas' => 'Padi Ciherang',
            'kategori' => 'Tanaman Pangan',
        ]);

        Komoditas::create([
            'id_komoditas' => 'kmdt-0001',
            'nama_komoditas' => 'Jagung Manis',
            'kategori' => 'Tanaman Pangan',
        ]);

        Komoditas::create([
            'id_komoditas' => 'kmdt-0003',
            'nama_komoditas' => 'Cabai Rawit',
            'kategori' => 'Hortikultura & Sayuran',
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/v1/komoditas');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Daftar komoditas berhasil diambil.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id_komoditas',
                        'nama_komoditas',
                        'kategori',
                    ],
                ],
            ]);

        $data = $response->json('data');
        $this->assertCount(3, $data);

        // Kategori 'Hortikultura & Sayuran' harus sebelum 'Tanaman Pangan'
        $this->assertEquals('Cabai Rawit', $data[0]['nama_komoditas']);
        // Di kategori 'Tanaman Pangan', 'Jagung Manis' harus sebelum 'Padi Ciherang'
        $this->assertEquals('Jagung Manis', $data[1]['nama_komoditas']);
        $this->assertEquals('Padi Ciherang', $data[2]['nama_komoditas']);
    }

    public function test_create_and_update_lahan_with_komoditas_integration(): void
    {
        $poktan = KelompokTani::create([
            'id_kelompok' => 'KT-TEST-001',
            'nama_kelompok' => 'Kelompok Tani Uji',
            'alamat' => 'Desa Uji',
            'status' => 'Aktif',
        ]);

        $user = Pengguna::create([
            'id_pengguna' => 'USR-TEST-002',
            'nama' => 'Test Anggota 2',
            'username' => 'test_anggota_2',
            'email' => 'test2@pawontani.test',
            'role' => 'Anggota',
            'status' => 'Aktif',
            'id_kelompok' => $poktan->id_kelompok,
            'password' => bcrypt('password123'),
        ]);

        $komoditas1 = Komoditas::create([
            'id_komoditas' => 'kmdt-1001',
            'nama_komoditas' => 'Padi Sawah',
            'kategori' => 'Tanaman Pangan',
        ]);

        $komoditas2 = Komoditas::create([
            'id_komoditas' => 'kmdt-1002',
            'nama_komoditas' => 'Cabai Merah',
            'kategori' => 'Hortikultura & Sayuran',
        ]);

        Sanctum::actingAs($user, ['*']);

        // 1. Create Lahan dengan komoditas 1
        $storeResponse = $this->postJson('/api/v1/lahan', [
            'nama_lahan' => 'Petak Timur',
            'luas' => 1.5,
            'alamat_lahan' => 'Dusun Krajan RT 01',
            'latitude' => -6.5621,
            'longitude' => 107.7589,
            'id_komoditas' => $komoditas1->id_komoditas,
            'musim_tanam' => 'MT-1',
            'keterangan' => 'Uji integrasi komoditas',
        ]);

        $storeResponse->assertStatus(201)
            ->assertJsonPath('data.detail_komoditas.id_komoditas', $komoditas1->id_komoditas)
            ->assertJsonPath('data.komoditas', 'Padi Sawah');

        $lahanId = $storeResponse->json('data.id');

        $this->assertDatabaseHas('lahan', [
            'id_lahan' => $lahanId,
            'id_komoditas' => $komoditas1->id_komoditas,
        ]);

        // 2. Read detail lahan
        $showResponse = $this->getJson("/api/v1/lahan/{$lahanId}");
        $showResponse->assertStatus(200)
            ->assertJsonPath('data.detail_komoditas.id_komoditas', $komoditas1->id_komoditas)
            ->assertJsonPath('data.detail_komoditas.nama_komoditas', 'Padi Sawah');

        // 3. Update Lahan ganti komoditas 2
        $updateResponse = $this->putJson("/api/v1/lahan/{$lahanId}", [
            'nama_lahan' => 'Petak Timur Direvisi',
            'id_komoditas' => $komoditas2->id_komoditas,
        ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.detail_komoditas.id_komoditas', $komoditas2->id_komoditas)
            ->assertJsonPath('data.komoditas', 'Cabai Merah');

        $this->assertDatabaseHas('lahan', [
            'id_lahan' => $lahanId,
            'id_komoditas' => $komoditas2->id_komoditas,
        ]);
    }
}
