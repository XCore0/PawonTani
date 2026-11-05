<?php

namespace Tests\Feature;

use App\Models\KelompokTani;
use App\Models\Pengguna;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['router']->pushMiddlewareToGroup('web', EnforceCsrfDuringTests::class);
    }

    public function test_web_mutation_without_csrf_token_is_rejected(): void
    {
        $kelompok = $this->createKelompok('KT-CSRF-01', 'Kelompok CSRF');
        $pengurus = $this->createPengguna('USR-CSRF-01', 'Pengurus', 'Aktif', $kelompok->id_kelompok);

        $response = $this->actingAs($pengurus)->post('/pengurus/anggota', [
            'nama' => 'Anggota Uji',
            'username' => 'anggota_csrf_uji',
            'password' => 'password123',
            'status' => 'Aktif',
        ]);

        $response->assertStatus(419);
        $this->assertDatabaseMissing('pengguna', ['username' => 'anggota_csrf_uji']);
    }

    public function test_web_mutation_with_valid_csrf_token_is_processed(): void
    {
        $kelompok = $this->createKelompok('KT-CSRF-02', 'Kelompok CSRF');
        $pengurus = $this->createPengguna('USR-CSRF-02', 'Pengurus', 'Aktif', $kelompok->id_kelompok);
        $token = str_repeat('a', 40);

        $response = $this->actingAs($pengurus)
            ->withSession(['_token' => $token])
            ->post('/pengurus/anggota', [
                '_token' => $token,
                'nama' => 'Anggota Uji',
                'username' => 'anggota_csrf_valid',
                'password' => 'password123',
                'status' => 'Aktif',
            ]);

        $response->assertRedirect(route('pengurus.anggota'));
        $this->assertDatabaseHas('pengguna', [
            'username' => 'anggota_csrf_valid',
            'role' => 'Anggota',
            'id_kelompok' => $kelompok->id_kelompok,
        ]);
    }

    public function test_login_requires_and_accepts_a_valid_csrf_token(): void
    {
        $user = $this->createPengguna('USR-CSRF-LOGIN', 'Pengurus', 'Aktif');

        $this->post('/login', [
            'login' => $user->username,
            'password' => 'password123',
        ])->assertStatus(419);

        $token = $this->csrfTokenFromLoginPage();
        $this->post('/login', [
            '_token' => $token,
            'login' => $user->username,
            'password' => 'password123',
        ])->assertRedirect(route('pengurus.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_logout_accepts_csrf_token_and_invalidates_the_session(): void
    {
        $user = $this->createPengguna('USR-CSRF-LOGOUT', 'PPL', 'Aktif');
        $token = str_repeat('b', 40);

        $response = $this->actingAs($user)
            ->withSession(['_token' => $token, 'session_marker' => 'must be removed'])
            ->post('/logout', ['_token' => $token]);

        $response->assertRedirect(route('login'));
        $response->assertSessionMissing('session_marker');
        $this->assertGuest();
        $this->assertNotSame($token, $this->app['session.store']->token());
    }

    public function test_sanctum_api_mutation_does_not_require_web_csrf_token(): void
    {
        $kelompok = $this->createKelompok('KT-CSRF-API', 'Kelompok API');
        $anggota = $this->createPengguna('USR-CSRF-API', 'Anggota', 'Aktif', $kelompok->id_kelompok);
        $token = $anggota->createToken('security-test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_active_pengurus_can_access_pengurus_route(): void
    {
        $pengurus = $this->createPengguna('USR-ROLE-PENGURUS', 'Pengurus', 'Aktif');

        $this->actingAs($pengurus)->get('/pengurus/dashboard')->assertOk();
    }

    public function test_active_ppl_can_access_admin_route(): void
    {
        $ppl = $this->createPengguna('USR-ROLE-PPL', 'PPL', 'Aktif');

        $this->actingAs($ppl)->get('/admin/dashboard')->assertOk();
    }

    public function test_inactive_pengurus_session_is_invalidated_and_denied(): void
    {
        $pengurus = $this->createPengguna('USR-INACTIVE-PENGURUS', 'Pengurus', 'Aktif');
        $token = str_repeat('c', 40);
        $this->actingAs($pengurus)->withSession([
            '_token' => $token,
            'session_marker' => 'must be removed',
        ]);

        $pengurus->status = 'Tidak Aktif';
        $pengurus->save();

        $this->get('/pengurus/dashboard')
            ->assertForbidden()
            ->assertSessionMissing('session_marker');

        $this->assertGuest();
        $this->assertNotSame($token, $this->app['session.store']->token());
    }

    public function test_inactive_ppl_session_is_invalidated_and_denied(): void
    {
        $ppl = $this->createPengguna('USR-INACTIVE-PPL', 'PPL', 'Aktif');
        $token = str_repeat('d', 40);
        $this->actingAs($ppl)->withSession([
            '_token' => $token,
            'session_marker' => 'must be removed',
        ]);

        $ppl->status = 'Tidak Aktif';
        $ppl->save();

        $this->get('/admin/dashboard')
            ->assertForbidden()
            ->assertSessionMissing('session_marker');

        $this->assertGuest();
        $this->assertNotSame($token, $this->app['session.store']->token());
    }

    public function test_active_user_with_wrong_role_is_still_denied(): void
    {
        $pengurus = $this->createPengguna('USR-WRONG-ROLE', 'Pengurus', 'Aktif');

        $this->actingAs($pengurus)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_current_pengurus_uses_authenticated_user_not_session_identity(): void
    {
        $kelompokPengurus = $this->createKelompok('KT-IDENTITY-01', 'Kelompok Identitas Sendiri');
        $kelompokAsing = $this->createKelompok('KT-IDENTITY-02', 'Kelompok Identitas Lain');
        $pengurus = $this->createPengguna(
            'USR-IDENTITY-01',
            'Pengurus',
            'Aktif',
            $kelompokPengurus->id_kelompok
        );
        $pengurusLain = $this->createPengguna(
            'USR-IDENTITY-02',
            'Pengurus',
            'Aktif',
            $kelompokAsing->id_kelompok
        );

        $this->actingAs($pengurus)
            ->withSession(['pengurus_id' => $pengurusLain->id_pengguna])
            ->get('/pengurus/anggota')
            ->assertOk()
            ->assertSee('Kelompok Identitas Sendiri')
            ->assertDontSee('Kelompok Identitas Lain');
    }

    public function test_non_pengurus_cannot_use_session_identity_as_pengurus(): void
    {
        $kelompok = $this->createKelompok('KT-IDENTITY-03', 'Kelompok Identitas');
        $pengurus = $this->createPengguna('USR-IDENTITY-03', 'Pengurus', 'Aktif', $kelompok->id_kelompok);
        $anggota = $this->createPengguna('USR-IDENTITY-04', 'Anggota', 'Aktif', $kelompok->id_kelompok);

        $this->actingAs($anggota)
            ->withSession(['pengurus_id' => $pengurus->id_pengguna])
            ->get('/pengurus/anggota')
            ->assertForbidden()
            ->assertDontSee('Kelompok Identitas');
    }

    public function test_guest_cannot_fall_back_to_first_pengurus(): void
    {
        $kelompok = $this->createKelompok('KT-IDENTITY-05', 'Kelompok Rahasia');
        $pengurus = $this->createPengguna('USR-IDENTITY-05', 'Pengurus', 'Aktif', $kelompok->id_kelompok);

        $this->withSession(['pengurus_id' => $pengurus->id_pengguna])
            ->get('/pengurus/anggota')
            ->assertRedirect(route('login'))
            ->assertDontSee('Kelompok Rahasia');
    }

    private function createKelompok(string $id, string $nama): KelompokTani
    {
        return KelompokTani::create([
            'id_kelompok' => $id,
            'nama_kelompok' => $nama,
            'alamat' => 'Desa Uji',
            'status' => 'Aktif',
        ]);
    }

    private function createPengguna(
        string $id,
        string $role,
        string $status,
        ?string $idKelompok = null
    ): Pengguna {
        return Pengguna::create([
            'id_pengguna' => $id,
            'nama' => 'Pengguna ' . $id,
            'username' => strtolower($id),
            'password' => 'password123',
            'role' => $role,
            'status' => $status,
            'id_kelompok' => $idKelompok,
        ]);
    }

    private function csrfTokenFromLoginPage(): string
    {
        $response = $this->get('/login')->assertOk();

        $this->assertSame(
            1,
            preg_match('/name="_token" value="([^"]+)"/', $response->getContent(), $matches),
            'The login form should render a CSRF token.'
        );

        return html_entity_decode($matches[1], ENT_QUOTES);
    }
}

class EnforceCsrfDuringTests extends PreventRequestForgery
{
    protected function runningUnitTests(): bool
    {
        return false;
    }
}
