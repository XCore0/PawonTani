<?php

namespace Tests\Feature\Api;

use App\Models\KelompokTani;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected KelompokTani $poktan;
    protected Pengguna $anggota;
    protected Pengguna $pengurus;
    protected Pengguna $anggotaNonaktif;

    protected function setUp(): void
    {
        parent::setUp();

        $this->poktan = KelompokTani::create([
            'id_kelompok' => 'KT-AUTH-001',
            'nama_kelompok' => 'Kelompok Tani Sejahtera',
            'alamat' => 'Desa Makmur',
            'status' => 'Aktif',
        ]);

        $this->anggota = Pengguna::create([
            'id_pengguna' => 'USR-AUTH-001',
            'nama' => 'Budi Santoso',
            'username' => 'budi_auth',
            'email' => 'budi@auth.test',
            'no_telepon' => '081234567800',
            'password' => bcrypt('password123'),
            'role' => 'Anggota',
            'status' => 'Aktif',
            'id_kelompok' => $this->poktan->id_kelompok,
        ]);

        $this->pengurus = Pengguna::create([
            'id_pengguna' => 'USR-AUTH-002',
            'nama' => 'Pak Ketua Budi',
            'username' => 'ketua_auth',
            'email' => 'ketua@auth.test',
            'password' => bcrypt('password123'),
            'role' => 'Pengurus',
            'jabatan' => 'Ketua',
            'status' => 'Aktif',
            'id_kelompok' => $this->poktan->id_kelompok,
        ]);

        $this->anggotaNonaktif = Pengguna::create([
            'id_pengguna' => 'USR-AUTH-003',
            'nama' => 'Agus Nonaktif',
            'username' => 'agus_auth',
            'email' => 'agus@auth.test',
            'password' => bcrypt('password123'),
            'role' => 'Anggota',
            'status' => 'Nonaktif',
            'id_kelompok' => $this->poktan->id_kelompok,
        ]);
    }

    public function test_login_successful_with_valid_credentials_returns_token(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'identifier' => 'budi_auth',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login berhasil.',
                'data' => [
                    'token_type' => 'Bearer',
                    'user' => [
                        'id' => 'USR-AUTH-001',
                        'nama' => 'Budi Santoso',
                        'role' => 'Anggota',
                        'status' => 'Aktif',
                    ],
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_login_can_use_email_or_phone_number(): void
    {
        // Login via email
        $resEmail = $this->postJson('/api/v1/auth/login', [
            'identifier' => 'budi@auth.test',
            'password' => 'password123',
        ]);
        $resEmail->assertStatus(200);

        // Login via telepon
        $resPhone = $this->postJson('/api/v1/auth/login', [
            'identifier' => '081234567800',
            'password' => 'password123',
        ]);
        $resPhone->assertStatus(200);
    }

    public function test_login_with_invalid_password_returns_401(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'identifier' => 'budi_auth',
            'password' => 'salah_password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Username/email atau password salah.',
            ]);
    }

    public function test_login_with_inactive_status_returns_403(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'identifier' => 'agus_auth',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akun Anda tidak aktif. Silakan hubungi pengurus kelompok tani.',
            ]);
    }

    public function test_login_with_non_anggota_role_returns_403(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'identifier' => 'ketua_auth',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses ditolak. Aplikasi mobile ini hanya diperuntukkan bagi Anggota kelompok tani.',
            ]);
    }

    public function test_me_returns_profile_when_authenticated_with_sanctum(): void
    {
        $loginRes = $this->postJson('/api/v1/auth/login', [
            'identifier' => 'budi_auth',
            'password' => 'password123',
        ]);
        $token = $loginRes->json('data.token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data profil berhasil diambil.',
                'data' => [
                    'id' => 'USR-AUTH-001',
                    'nama' => 'Budi Santoso',
                    'username' => 'budi_auth',
                    'role' => 'Anggota',
                ],
            ]);
    }

    public function test_me_without_token_returns_401(): void
    {
        $response = $this->getJson('/api/v1/auth/me');
        $response->assertStatus(401);
    }

    public function test_logout_revokes_current_token(): void
    {
        $loginRes = $this->postJson('/api/v1/auth/login', [
            'identifier' => 'budi_auth',
            'password' => 'password123',
        ]);
        $token = $loginRes->json('data.token');

        $logoutRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout');

        $logoutRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout berhasil. Sesi mobile telah diakhiri.',
            ]);

        // Token personal_access_tokens harus sudah terhapus di database
        $this->assertDatabaseCount('personal_access_tokens', 0);

        // Reset state auth guard pada test container
        $this->app['auth']->forgetGuards();

        // Coba akses endpoint terproteksi setelah token direvokasi
        $meRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/me');

        $meRes->assertStatus(401);
    }
}
