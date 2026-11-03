<?php

namespace Tests\Feature;

use App\Models\KelompokTani;
use App\Models\Komoditas;
use App\Models\Lahan;
use App\Models\Notifikasi;
use App\Models\Pengguna;
use App\Models\RiwayatVerifikasiLahan;
use App\Services\CloudinaryService;
use App\Services\LahanService;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PengurusLahanHttpTest extends TestCase
{
    use RefreshDatabase;

    protected KelompokTani $poktan1;
    protected KelompokTani $poktan2;
    protected Pengguna $ketuaPoktan1;
    protected Pengguna $sekretarisPoktan1;
    protected Pengguna $ketuaPoktan2;
    protected Pengguna $anggotaPoktan1;
    protected Pengguna $pengurusNonaktif;
    protected Komoditas $komoditasPadi;
    protected Lahan $lahanMenunggu;
    protected Lahan $lahanTerverifikasi;
    protected Lahan $lahanPerluPerbaikan;
    protected Lahan $lahanPoktan2;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Mock CloudinaryService agar tidak pernah menghubungi jaringan eksternal
        $cloudinaryMock = Mockery::mock(CloudinaryService::class);
        $cloudinaryMock->shouldReceive('upload')->andReturn('https://res.cloudinary.com/demo/image/upload/sample.jpg');
        $cloudinaryMock->shouldReceive('delete')->andReturn(true);
        $this->app->instance(CloudinaryService::class, $cloudinaryMock);

        // 2. Setup Fixture Kelompok Tani
        $this->poktan1 = KelompokTani::create([
            'id_kelompok' => 'KT-001',
            'nama_kelompok' => 'Kelompok Tani Subur Makmur',
            'alamat' => 'Desa Sukamaju No. 10',
            'status' => 'Aktif',
        ]);

        $this->poktan2 = KelompokTani::create([
            'id_kelompok' => 'KT-002',
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

        // 4. Setup Fixture Pengguna
        // Ketua Poktan 1 (Verifikator Sah)
        $this->ketuaPoktan1 = Pengguna::create([
            'id_pengguna' => 'USR-KETUA-1',
            'nama' => 'Bapak Ketua Satu',
            'username' => 'ketua_poktan1',
            'role' => 'Pengurus',
            'jabatan' => 'Ketua',
            'status' => 'Aktif',
            'id_kelompok' => 'KT-001',
            'password' => bcrypt('password123'),
        ]);

        // Sekretaris Poktan 1 (Pengurus biasa, read-only)
        $this->sekretarisPoktan1 = Pengguna::create([
            'id_pengguna' => 'USR-SEKRE-1',
            'nama' => 'Ibu Sekretaris Satu',
            'username' => 'sekre_poktan1',
            'role' => 'Pengurus',
            'jabatan' => 'Sekretaris',
            'status' => 'Aktif',
            'id_kelompok' => 'KT-001',
            'password' => bcrypt('password123'),
        ]);

        // Ketua Poktan 2 (Pengurus Kelompok Lain)
        $this->ketuaPoktan2 = Pengguna::create([
            'id_pengguna' => 'USR-KETUA-2',
            'nama' => 'Bapak Ketua Dua',
            'username' => 'ketua_poktan2',
            'role' => 'Pengurus',
            'jabatan' => 'Ketua',
            'status' => 'Aktif',
            'id_kelompok' => 'KT-002',
            'password' => bcrypt('password123'),
        ]);

        // Pengurus Nonaktif
        $this->pengurusNonaktif = Pengguna::create([
            'id_pengguna' => 'USR-NONAKTIF',
            'nama' => 'Pengurus Nonaktif',
            'username' => 'pengurus_nonaktif',
            'role' => 'Pengurus',
            'jabatan' => 'Bendahara',
            'status' => 'Tidak Aktif',
            'id_kelompok' => 'KT-001',
            'password' => bcrypt('password123'),
        ]);

        // Anggota Pemilik Lahan Poktan 1
        $this->anggotaPoktan1 = Pengguna::create([
            'id_pengguna' => 'USR-ANGGOTA-1',
            'nama' => 'Petani Joko Susanto',
            'nik' => '3301234567890001',
            'username' => 'joko_petani',
            'role' => 'Anggota',
            'status' => 'Aktif',
            'id_kelompok' => 'KT-001',
            'no_telepon' => '081234567890',
            'alamat' => 'Dusun Krajan RT 01',
            'password' => bcrypt('password123'),
        ]);

        // 5. Setup Fixture Lahan
        // Lahan 1: Status menunggu_verifikasi
        $this->lahanMenunggu = Lahan::create([
            'id_lahan' => 'LHN-20261104-0001',
            'id_pengguna' => 'USR-ANGGOTA-1',
            'id_kelompok' => 'KT-001',
            'nama_lahan' => 'Sawah Blok Barat',
            'luas' => 1.5,
            'alamat_lahan' => 'Jl. Pematang Sawah No. 12',
            'latitude' => -7.1234567,
            'longitude' => 110.1234567,
            'id_komoditas' => 'KMD-001',
            'musim_tanam' => 'Musim Hujan 2026',
            'status_operasional' => 'persiapan',
            'status_verifikasi' => 'menunggu_verifikasi',
            'nomor_revisi' => 1,
        ]);

        // Lahan 2: Status terverifikasi
        $this->lahanTerverifikasi = Lahan::create([
            'id_lahan' => 'LHN-20261104-0002',
            'id_pengguna' => 'USR-ANGGOTA-1',
            'id_kelompok' => 'KT-001',
            'nama_lahan' => 'Kebun Jagung Lereng',
            'luas' => 2.0,
            'alamat_lahan' => 'Lereng Perbukitan Blok C',
            'latitude' => -7.2345678,
            'longitude' => 110.2345678,
            'id_komoditas' => 'KMD-001',
            'musim_tanam' => 'Musim Gadu 2026',
            'status_operasional' => 'tanam',
            'status_verifikasi' => 'terverifikasi',
            'verifikator_id' => 'USR-KETUA-1',
            'tanggal_verifikasi' => now()->subDay(),
            'nomor_revisi' => 1,
        ]);

        // Lahan 3: Status perlu_perbaikan
        $this->lahanPerluPerbaikan = Lahan::create([
            'id_lahan' => 'LHN-20261104-0003',
            'id_pengguna' => 'USR-ANGGOTA-1',
            'id_kelompok' => 'KT-001',
            'nama_lahan' => 'Sawah Irigasi Timur',
            'luas' => 0.8,
            'alamat_lahan' => 'Jl. Saluran Irigasi RT 04',
            'id_komoditas' => 'KMD-001',
            'status_operasional' => 'persiapan',
            'status_verifikasi' => 'perlu_perbaikan',
            'catatan_verifikasi' => 'Perbaiki batas patok bagian utara.',
            'nomor_revisi' => 1,
        ]);

        // Lahan Poktan 2 (Isolasi data kelompok lain)
        $this->lahanPoktan2 = Lahan::create([
            'id_lahan' => 'LHN-20261104-0099',
            'id_pengguna' => 'USR-KETUA-2',
            'id_kelompok' => 'KT-002',
            'nama_lahan' => 'Lahan Milik Poktan Dua',
            'luas' => 3.0,
            'alamat_lahan' => 'Desa Seberang RT 09',
            'status_operasional' => 'persiapan',
            'status_verifikasi' => 'menunggu_verifikasi',
            'nomor_revisi' => 1,
        ]);
    }

    /* ============================================================
       1. AUTHENTICATION & ACCESS CONTROL TESTS
       ============================================================ */

    public function test_unauthenticated_visitor_is_redirected_to_login(): void
    {
        $this->get('/pengurus/lahan')->assertRedirect('/login');
        $this->get('/pengurus/lahan/' . $this->lahanMenunggu->id_lahan)->assertRedirect('/login');
        $this->post('/pengurus/lahan/' . $this->lahanMenunggu->id_lahan . '/verifikasi', ['aksi' => 'setujui'])
            ->assertRedirect('/login');
    }

    public function test_active_pengurus_can_access_lahan_index(): void
    {
        $response = $this->actingAs($this->ketuaPoktan1)->get('/pengurus/lahan');

        $response->assertOk();
        $response->assertSee('Kelola Lahan');
        $response->assertSee('Total Lahan');
        $response->assertSee('Sawah Blok Barat');
    }

    public function test_active_pengurus_can_access_lahan_detail_of_own_group(): void
    {
        $response = $this->actingAs($this->sekretarisPoktan1)
            ->get('/pengurus/lahan/' . $this->lahanMenunggu->id_lahan);

        $response->assertOk();
        $response->assertSee('Sawah Blok Barat');
        $response->assertSee('Menunggu Verifikasi');
    }

    public function test_inactive_pengurus_is_rejected_with_403(): void
    {
        $this->actingAs($this->pengurusNonaktif)
            ->get('/pengurus/lahan')
            ->assertForbidden();

        $this->actingAs($this->pengurusNonaktif)
            ->get('/pengurus/lahan/' . $this->lahanMenunggu->id_lahan)
            ->assertForbidden();

        $this->actingAs($this->pengurusNonaktif)
            ->post('/pengurus/lahan/' . $this->lahanMenunggu->id_lahan . '/verifikasi', ['aksi' => 'setujui'])
            ->assertForbidden();
    }

    public function test_non_pengurus_role_is_rejected_with_403(): void
    {
        $this->actingAs($this->anggotaPoktan1)
            ->get('/pengurus/lahan')
            ->assertForbidden();

        $this->actingAs($this->anggotaPoktan1)
            ->get('/pengurus/lahan/' . $this->lahanMenunggu->id_lahan)
            ->assertForbidden();
    }

    /* ============================================================
       2. GROUP ISOLATION & IDOR PREVENTION TESTS
       ============================================================ */

    public function test_pengurus_cannot_see_other_group_lahan_on_index(): void
    {
        $response = $this->actingAs($this->ketuaPoktan1)->get('/pengurus/lahan');

        $response->assertOk();
        $response->assertSee('Sawah Blok Barat');
        $response->assertDontSee('Lahan Milik Poktan Dua');
    }

    public function test_pengurus_cannot_access_other_group_lahan_detail(): void
    {
        $this->actingAs($this->ketuaPoktan1)
            ->get('/pengurus/lahan/' . $this->lahanPoktan2->id_lahan)
            ->assertNotFound();
    }

    public function test_id_manipulation_returns_uniform_404_without_leaking_existence(): void
    {
        // 1. ID yang memang tidak pernah ada
        $this->actingAs($this->ketuaPoktan1)
            ->get('/pengurus/lahan/LHN-TIDAK-ADA-9999')
            ->assertNotFound();

        // 2. ID sah milik kelompok lain (harus sama persis 404, status oracle dicegah)
        $this->actingAs($this->ketuaPoktan1)
            ->get('/pengurus/lahan/' . $this->lahanPoktan2->id_lahan)
            ->assertNotFound();
    }

    /* ============================================================
       3. VERIFICATION AUTHORIZATION TESTS
       ============================================================ */

    public function test_active_ketua_can_see_verification_panel_and_submit_decision(): void
    {
        // 1. Periksa halaman detail untuk Ketua
        $resShow = $this->actingAs($this->ketuaPoktan1)
            ->get('/pengurus/lahan/' . $this->lahanMenunggu->id_lahan);

        $resShow->assertOk();
        $resShow->assertSee('Panel Verifikasi Ketua');
        $resShow->assertSee('Setujui Lahan (Terverifikasi)');

        // 2. Kirim keputusan verifikasi
        $resPost = $this->actingAs($this->ketuaPoktan1)
            ->post('/pengurus/lahan/' . $this->lahanMenunggu->id_lahan . '/verifikasi', [
                'aksi' => 'setujui',
                'catatan' => 'Lahan memenuhi syarat verifikasi.',
            ]);

        $resPost->assertRedirect(route('pengurus.lahan.show', $this->lahanMenunggu->id_lahan));
        $resPost->assertSessionHas('success');
    }

    public function test_sekretaris_cannot_see_verification_form_and_direct_post_is_forbidden(): void
    {
        // 1. Periksa halaman detail untuk Sekretaris (non-Ketua)
        $resShow = $this->actingAs($this->sekretarisPoktan1)
            ->get('/pengurus/lahan/' . $this->lahanMenunggu->id_lahan);

        $resShow->assertOk();
        $resShow->assertDontSee('Panel Verifikasi Ketua');
        $resShow->assertSee('Akses Verifikasi Khusus Ketua');

        // 2. Upaya POST langsung ditolak HTTP 403 Forbidden
        $this->actingAs($this->sekretarisPoktan1)
            ->post('/pengurus/lahan/' . $this->lahanMenunggu->id_lahan . '/verifikasi', [
                'aksi' => 'setujui',
            ])
            ->assertForbidden();
    }

    public function test_pengurus_of_other_group_cannot_verify_lahan(): void
    {
        $this->actingAs($this->ketuaPoktan2)
            ->post('/pengurus/lahan/' . $this->lahanMenunggu->id_lahan . '/verifikasi', [
                'aksi' => 'setujui',
            ])
            ->assertForbidden();
    }

    /* ============================================================
       4. VERIFICATION WORKFLOW & AUDIT TRAIL TESTS
       ============================================================ */

    public function test_workflow_menunggu_verifikasi_to_terverifikasi(): void
    {
        $this->actingAs($this->ketuaPoktan1)
            ->post('/pengurus/lahan/' . $this->lahanMenunggu->id_lahan . '/verifikasi', [
                'aksi' => 'setujui',
                'catatan' => 'Lahan disetujui sesuai survei lapangan.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->lahanMenunggu->refresh();

        // 1. Periksa status dan metadata lahan
        $this->assertSame('terverifikasi', $this->lahanMenunggu->status_verifikasi);
        $this->assertSame($this->ketuaPoktan1->id_pengguna, $this->lahanMenunggu->verifikator_id);
        $this->assertNotNull($this->lahanMenunggu->tanggal_verifikasi);

        // 2. Periksa pencatatan riwayat audit trail
        $riwayat = RiwayatVerifikasiLahan::where('id_lahan', $this->lahanMenunggu->id_lahan)->latest('id_riwayat')->first();
        $this->assertNotNull($riwayat);
        $this->assertSame('verifikasi_disetujui', $riwayat->aksi);
        $this->assertSame('menunggu_verifikasi', $riwayat->status_sebelumnya);
        $this->assertSame('terverifikasi', $riwayat->status_sesudahnya);
        $this->assertSame($this->ketuaPoktan1->id_pengguna, $riwayat->id_pengguna);

        // 3. Periksa notifikasi ke anggota pemilik lahan
        $notif = Notifikasi::where('id_penerima', $this->anggotaPoktan1->id_pengguna)->latest('created_at')->first();
        $this->assertNotNull($notif);
        $this->assertSame(NotificationService::JENIS_TERVERIFIKASI, $notif->jenis_notifikasi);
    }

    public function test_workflow_menunggu_verifikasi_to_perlu_perbaikan(): void
    {
        $catatanAlasan = 'Foto patok batas tanah sisi timur buram dan perlu diperjelas.';

        $this->actingAs($this->ketuaPoktan1)
            ->post('/pengurus/lahan/' . $this->lahanMenunggu->id_lahan . '/verifikasi', [
                'aksi' => 'perlu_perbaikan',
                'catatan' => $catatanAlasan,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->lahanMenunggu->refresh();

        // 1. Status berubah menjadi perlu_perbaikan
        $this->assertSame('perlu_perbaikan', $this->lahanMenunggu->status_verifikasi);
        $this->assertSame($catatanAlasan, $this->lahanMenunggu->catatan_verifikasi);

        // 2. Jejak riwayat audit trail tercatat
        $riwayat = RiwayatVerifikasiLahan::where('id_lahan', $this->lahanMenunggu->id_lahan)->latest('id_riwayat')->first();
        $this->assertNotNull($riwayat);
        $this->assertSame('permintaan_perbaikan', $riwayat->aksi);
        $this->assertSame('menunggu_verifikasi', $riwayat->status_sebelumnya);
        $this->assertSame('perlu_perbaikan', $riwayat->status_sesudahnya);
        $this->assertSame($catatanAlasan, $riwayat->catatan);

        // 3. Notifikasi terkirim ke anggota
        $notif = Notifikasi::where('id_penerima', $this->anggotaPoktan1->id_pengguna)->latest('created_at')->first();
        $this->assertNotNull($notif);
        $this->assertSame(NotificationService::JENIS_PERLU_PERBAIKAN, $notif->jenis_notifikasi);
    }

    public function test_workflow_terverifikasi_to_perlu_perbaikan_via_cabut_verifikasi(): void
    {
        $catatanCabut = 'Ditemukan sengketa batas tanah dengan lahan tetangga.';

        $this->actingAs($this->ketuaPoktan1)
            ->post('/pengurus/lahan/' . $this->lahanTerverifikasi->id_lahan . '/verifikasi', [
                'aksi' => 'cabut_verifikasi',
                'catatan' => $catatanCabut,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->lahanTerverifikasi->refresh();

        // 1. Status dicabut kembali ke perlu_perbaikan
        $this->assertSame('perlu_perbaikan', $this->lahanTerverifikasi->status_verifikasi);
        // Metadata verifikator lama harus dibersihkan
        $this->assertNull($this->lahanTerverifikasi->verifikator_id);

        // 2. Jejak riwayat audit trail tercatat
        $riwayat = RiwayatVerifikasiLahan::where('id_lahan', $this->lahanTerverifikasi->id_lahan)->latest('id_riwayat')->first();
        $this->assertNotNull($riwayat);
        $this->assertSame('pencabutan_verifikasi', $riwayat->aksi);
        $this->assertSame('terverifikasi', $riwayat->status_sebelumnya);
        $this->assertSame('perlu_perbaikan', $riwayat->status_sesudahnya);
        $this->assertSame($catatanCabut, $riwayat->catatan);
    }

    public function test_workflow_perlu_perbaikan_to_menunggu_verifikasi_after_anggota_updates(): void
    {
        // Anggota melakukan pembaruan (resubmit) melalui LahanService
        /** @var LahanService $lahanService */
        $lahanService = app(LahanService::class);

        $lahanDiperbarui = $lahanService->updateLahan($this->anggotaPoktan1, $this->lahanPerluPerbaikan, [
            'nama_lahan' => 'Sawah Irigasi Timur (Patok Diperbarui)',
            'luas' => 0.8,
            'alamat_lahan' => 'Jl. Saluran Irigasi RT 04 Baru',
        ]);

        // 1. Status kembali menjadi menunggu_verifikasi
        $this->assertSame('menunggu_verifikasi', $lahanDiperbarui->status_verifikasi);

        // 2. Nomor revisi naik dari 1 menjadi 2
        $this->assertSame(2, $lahanDiperbarui->nomor_revisi);

        // 3. Metadata verifikator dan catatan lama dibersihkan
        $this->assertNull($lahanDiperbarui->verifikator_id);
        $this->assertNull($lahanDiperbarui->catatan_verifikasi);

        // 4. Riwayat tercatat sebagai pengajuan_ulang
        $riwayat = RiwayatVerifikasiLahan::where('id_lahan', $this->lahanPerluPerbaikan->id_lahan)->latest('id_riwayat')->first();
        $this->assertNotNull($riwayat);
        $this->assertSame('pengajuan_ulang', $riwayat->aksi);
        $this->assertSame('menunggu_verifikasi', $riwayat->status_sesudahnya);
        $this->assertSame(2, $riwayat->nomor_revisi);

        // 5. Notifikasi pengajuan ulang dikirimkan ke Ketua aktif
        $notifKetua = Notifikasi::where('id_penerima', $this->ketuaPoktan1->id_pengguna)->latest('created_at')->first();
        $this->assertNotNull($notifKetua);
        $this->assertSame(NotificationService::JENIS_PENGAJUAN_ULANG, $notifKetua->jenis_notifikasi);
    }

    /* ============================================================
       5. FORM VALIDATION & INTEGRITY TESTS
       ============================================================ */

    public function test_unknown_aksi_is_rejected(): void
    {
        $response = $this->actingAs($this->ketuaPoktan1)
            ->post('/pengurus/lahan/' . $this->lahanMenunggu->id_lahan . '/verifikasi', [
                'aksi' => 'aksi_tidak_dikenal',
                'catatan' => 'Catatan sembarang',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['aksi']);

        // Data tidak berubah
        $this->lahanMenunggu->refresh();
        $this->assertSame('menunggu_verifikasi', $this->lahanMenunggu->status_verifikasi);
    }

    public function test_empty_catatan_for_perlu_perbaikan_is_rejected(): void
    {
        $response = $this->actingAs($this->ketuaPoktan1)
            ->post('/pengurus/lahan/' . $this->lahanMenunggu->id_lahan . '/verifikasi', [
                'aksi' => 'perlu_perbaikan',
                'catatan' => '',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['catatan']);

        // Data tidak berubah
        $this->lahanMenunggu->refresh();
        $this->assertSame('menunggu_verifikasi', $this->lahanMenunggu->status_verifikasi);
    }

    public function test_empty_catatan_for_cabut_verifikasi_is_rejected(): void
    {
        $response = $this->actingAs($this->ketuaPoktan1)
            ->post('/pengurus/lahan/' . $this->lahanTerverifikasi->id_lahan . '/verifikasi', [
                'aksi' => 'cabut_verifikasi',
                'catatan' => null,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['catatan']);

        // Data tidak berubah
        $this->lahanTerverifikasi->refresh();
        $this->assertSame('terverifikasi', $this->lahanTerverifikasi->status_verifikasi);
    }

    public function test_mass_assignment_protection_on_verification_endpoint(): void
    {
        $namaAwal = $this->lahanMenunggu->nama_lahan;
        $luasAwal = (float)$this->lahanMenunggu->luas;

        // Upaya menyisipkan manipulasi atribut fisik lahan melalui endpoint verifikasi
        $response = $this->actingAs($this->ketuaPoktan1)
            ->post('/pengurus/lahan/' . $this->lahanMenunggu->id_lahan . '/verifikasi', [
                'aksi' => 'setujui',
                'catatan' => 'Disetujui',
                'nama_lahan' => 'Nama Lahan Diretas',
                'luas' => 9999.99,
                'status_operasional' => 'panen',
                'id_pengguna' => 'USR-PERETAS',
                'id_kelompok' => 'KT-PALSU',
                'nomor_revisi' => 99,
            ]);

        $response->assertRedirect();
        $this->lahanMenunggu->refresh();

        // 1. Status verifikasi sukses diperbarui
        $this->assertSame('terverifikasi', $this->lahanMenunggu->status_verifikasi);

        // 2. Seluruh atribut fisik dan relasi kepemilikan tetap terlindungi (tidak termodifikasi)
        $this->assertSame($namaAwal, $this->lahanMenunggu->nama_lahan);
        $this->assertSame($luasAwal, (float)$this->lahanMenunggu->luas);
        $this->assertSame('persiapan', $this->lahanMenunggu->status_operasional);
        $this->assertSame($this->anggotaPoktan1->id_pengguna, $this->lahanMenunggu->id_pengguna);
        $this->assertSame('KT-001', $this->lahanMenunggu->id_kelompok);
        $this->assertSame(1, $this->lahanMenunggu->nomor_revisi);
    }
}
