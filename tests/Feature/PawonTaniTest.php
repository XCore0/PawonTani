<?php

namespace Tests\Feature;

use App\Models\KelompokTani;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PawonTaniTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('PawonTani');
        $response->assertSee('Sistem Informasi untuk Kelompok Tani');
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Masuk ke PawonTani');
        $response->assertSee('Sistem Informasi Kelompok Tani');
        $response->assertSee('Logo.png');
    }

    public function test_admin_pengurus_renders_successfully(): void
    {
        $response = $this->get('/admin/pengurus');

        $response->assertStatus(200);
        $response->assertSee('Pengurus');
        $response->assertSee('Kelola dan pantau struktur pengurus');
        $response->assertSee('Ahmad Fauzi');
    }

    public function test_admin_pengurus_store_creates_pengguna_with_random_id(): void
    {
        $poktan = KelompokTani::create([
            'id_kelompok' => 'PokTan-9988',
            'nama_kelompok' => 'Kelompok Tani Harapan Jaya',
            'alamat' => 'Desa Sukamaju No. 1',
            'status' => 'Aktif',
        ]);

        $uniqueUsername = 'pengurus_' . uniqid();
        $response = $this->post('/admin/pengurus', [
            'nama' => 'Bpk. Hendra Kusuma, S.P.',
            'jabatan' => 'Ketua',
            'id_kelompok' => $poktan->id_kelompok,
            'nik' => '3302181122330001',
            'username' => $uniqueUsername,
            'password' => 'password123',
            'email' => $uniqueUsername . '@pawontani.id',
            'no_telepon' => '081299887766',
            'alamat' => 'Dusun Sukamaju No. 77',
            'status' => 'Aktif',
        ]);

        $response->assertRedirect('/admin/pengurus');
        $response->assertSessionHas('success');

        $pengurus = \App\Models\Pengguna::where('username', $uniqueUsername)->first();
        $this->assertNotNull($pengurus);
        $this->assertStringStartsWith('PGR-', $pengurus->id_pengguna);
        $this->assertEquals('Bpk. Hendra Kusuma, S.P.', $pengurus->nama);
        $this->assertEquals('Ketua', $pengurus->jabatan);
        $this->assertEquals('PokTan-9988', $pengurus->id_kelompok);
        $this->assertEquals('Pengurus', $pengurus->role);
        $this->assertEquals('Aktif', $pengurus->status);

        // Verify it shows on index
        $indexResponse = $this->get('/admin/pengurus');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee($pengurus->id_pengguna);
        $indexResponse->assertSee('Bpk. Hendra Kusuma, S.P.');
    }

    public function test_admin_pengurus_update_and_delete(): void
    {
        $poktan = KelompokTani::create([
            'id_kelompok' => 'PokTan-7766',
            'nama_kelompok' => 'Kelompok Tani Subur Makmur',
            'alamat' => 'Desa Makmur No. 9',
            'status' => 'Aktif',
        ]);

        $username = 'pengurus_edit_' . uniqid();
        $pengurus = \App\Models\Pengguna::create([
            'nama' => 'H. Ahmad Basuki',
            'jabatan' => 'Sekretaris',
            'id_kelompok' => $poktan->id_kelompok,
            'nik' => '3302189999990002',
            'username' => $username,
            'password' => 'secret123',
            'email' => $username . '@pawontani.id',
            'no_telepon' => '081234567890',
            'alamat' => 'Jl. Padi No. 12',
            'role' => 'Pengurus',
            'status' => 'Aktif',
        ]);

        // 1. Update Pengurus
        $updateResponse = $this->put('/admin/pengurus/' . $pengurus->id_pengguna, [
            'nama' => 'H. Ahmad Basuki, M.P.',
            'jabatan' => 'Ketua',
            'id_kelompok' => $poktan->id_kelompok,
            'nik' => '3302189999990002',
            'username' => $username . '_updated',
            'email' => $username . '_updated@pawontani.id',
            'no_telepon' => '081987654321',
            'alamat' => 'Jl. Padi No. 12 RT 01/02',
            'status' => 'Tidak Aktif',
        ]);

        $updateResponse->assertRedirect('/admin/pengurus');
        $updateResponse->assertSessionHas('success');

        $pengurus->refresh();
        $this->assertEquals('H. Ahmad Basuki, M.P.', $pengurus->nama);
        $this->assertEquals('Ketua', $pengurus->jabatan);
        $this->assertEquals($username . '_updated', $pengurus->username);
        $this->assertEquals('Tidak Aktif', $pengurus->status);

        // 2. Delete Pengurus
        $deleteResponse = $this->delete('/admin/pengurus/' . $pengurus->id_pengguna);
        $deleteResponse->assertRedirect('/admin/pengurus');
        $deleteResponse->assertSessionHas('success');

        $this->assertNull(\App\Models\Pengguna::where('id_pengguna', $pengurus->id_pengguna)->first());
    }

    public function test_admin_kelompok_renders_successfully(): void
    {
        KelompokTani::create([
            'id_kelompok' => 'PokTan-1042',
            'nama_kelompok' => 'Kelompok Tani Maju Bersama',
            'alamat' => 'Desa Sukamaju, Kec. Ciawi, Bogor',
            'status' => 'Aktif',
        ]);

        $response = $this->get('/admin/kelompok');

        $response->assertStatus(200);
        $response->assertSee('Kelompok Tani');
        $response->assertSee('Kelola data kelompok tani yang terdaftar');
        $response->assertSee('Cetak / Export');
        $response->assertSee('Tambah Kelompok');
        $response->assertSee('Kelompok Tani Maju Bersama');
        $response->assertSee('PokTan-1042');
    }

    public function test_admin_kelompok_crud_lifecycle(): void
    {
        // 1. Create (Store)
        $uniqueName = 'Poktan Test ' . uniqid();
        $storeResponse = $this->post('/admin/kelompok', [
            'nama_kelompok' => $uniqueName,
            'alamat' => 'Desa Percobaan No. 123',
            'status' => 'Aktif',
        ]);

        $storeResponse->assertRedirect('/admin/kelompok');
        $storeResponse->assertSessionHas('success');

        $record = \App\Models\KelompokTani::where('nama_kelompok', $uniqueName)->first();
        $this->assertNotNull($record);
        $this->assertStringStartsWith('PokTan-', $record->id_kelompok);
        $this->assertEquals('Desa Percobaan No. 123', $record->alamat);
        $this->assertEquals('Aktif', $record->status);

        // 2. Read (Index shows the new record)
        $indexResponse = $this->get('/admin/kelompok');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee($record->id_kelompok);
        $indexResponse->assertSee($uniqueName);

        // 3. Update
        $updateResponse = $this->put('/admin/kelompok/' . $record->id_kelompok, [
            'nama_kelompok' => $uniqueName . ' Updated',
            'alamat' => 'Desa Percobaan Baru No. 456',
            'status' => 'Tidak Aktif',
        ]);

        $updateResponse->assertRedirect('/admin/kelompok');
        $record->refresh();
        $this->assertEquals($uniqueName . ' Updated', $record->nama_kelompok);
        $this->assertEquals('Desa Percobaan Baru No. 456', $record->alamat);
        $this->assertEquals('Tidak Aktif', $record->status);

        // 4. Delete (Destroy)
        $deleteResponse = $this->delete('/admin/kelompok/' . $record->id_kelompok);
        $deleteResponse->assertRedirect('/admin/kelompok');

        $this->assertNull(\App\Models\KelompokTani::where('id_kelompok', $record->id_kelompok)->first());
    }
}
