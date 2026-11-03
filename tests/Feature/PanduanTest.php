<?php

namespace Tests\Feature;

use App\Models\Panduan;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanduanTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Pengguna
    {
        return Pengguna::create([
            'nama' => 'PPL Test',
            'username' => 'ppl_' . uniqid(),
            'password' => 'password',
            'email' => uniqid() . '@test.local',
            'role' => 'PPL',
            'status' => 'Aktif',
        ]);
    }

    public function test_public_only_shows_published_panduan(): void
    {
        Panduan::create([
            'judul' => 'Panduan Publik',
            'slug' => 'panduan-publik',
            'kategori' => 'Budidaya Tanaman',
            'ringkasan' => 'Ringkasan publik',
            'isi' => 'Isi publik',
            'tanggal' => '2026-09-20',
            'status' => 'Publik',
        ]);

        Panduan::create([
            'judul' => 'Panduan Draft',
            'slug' => 'panduan-draft',
            'kategori' => 'Budidaya Tanaman',
            'ringkasan' => 'Ringkasan draft',
            'isi' => 'Isi draft',
            'tanggal' => '2026-09-20',
            'status' => 'Draft',
        ]);

        $response = $this->get(route('edukasi.panduan'));

        $response->assertOk();
        $response->assertSee('Panduan Publik');
        $response->assertDontSee('Panduan Draft');
    }

    public function test_admin_can_create_update_and_delete_panduan(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $komoditas = \App\Models\Komoditas::create([
            'id_komoditas' => 'KOM-JAGUNG',
            'nama_komoditas' => 'Jagung',
            'kategori' => 'Budidaya Tanaman',
            'satuan' => 'kg',
            'harga_bawah' => 3000,
            'harga_atas' => 6000,
            'status' => 'Aktif',
        ]);

        $response = $this->post(route('admin.edukasi.panduan.store'), [
            'judul' => 'Panduan Budidaya Jagung',
            'kategori' => 'Budidaya Tanaman',
            'komoditas_id' => $komoditas->id_komoditas,
            'ringkasan' => 'Panduan ringkas budidaya jagung di lahan kering.',
            'isi' => 'Langkah lengkap budidaya jagung mulai dari persiapan lahan hingga panen.',
            'tanggal' => '2026-09-20',
            'status' => 'Draft',
        ]);

        $response->assertRedirect(route('admin.edukasi.panduan'));
        $response->assertSessionHas('success');

        $panduan = Panduan::firstOrFail();
        $this->assertSame('panduan-budidaya-jagung', $panduan->slug);
        $this->assertSame($admin->id_pengguna, $panduan->created_by);

        $response = $this->put(route('admin.edukasi.panduan.update', $panduan), [
            'judul' => 'Panduan Budidaya Jagung Hibrida',
            'kategori' => 'Budidaya Tanaman',
            'komoditas_id' => $komoditas->id_komoditas,
            'ringkasan' => 'Ringkasan diperbarui untuk varietas hibrida.',
            'isi' => 'Isi diperbarui untuk varietas hibrida yang lebih tahan hama.',
            'tanggal' => '2026-09-20',
            'status' => 'Publik',
        ]);

        $response->assertRedirect(route('admin.edukasi.panduan'));
        $panduan->refresh();
        $this->assertSame('panduan-budidaya-jagung-hibrida', $panduan->slug);
        $this->assertSame('Publik', $panduan->status);

        $response = $this->delete(route('admin.edukasi.panduan.destroy', $panduan));
        $response->assertRedirect(route('admin.edukasi.panduan'));
        $this->assertDatabaseMissing('panduan', ['id_panduan' => $panduan->id_panduan]);
    }

    public function test_admin_search_and_filter_are_server_side(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        Panduan::create([
            'judul' => 'Panduan Padi Sawah',
            'slug' => 'panduan-padi-sawah',
            'kategori' => 'Budidaya Tanaman',
            'ringkasan' => 'Padi',
            'isi' => 'Isi',
            'tanggal' => '2026-09-20',
            'status' => 'Publik',
        ]);

        Panduan::create([
            'judul' => 'Panduan Jagung',
            'slug' => 'panduan-jagung',
            'kategori' => 'Budidaya Tanaman',
            'ringkasan' => 'Jagung',
            'isi' => 'Isi',
            'tanggal' => '2026-09-19',
            'status' => 'Draft',
        ]);

        $response = $this->get(route('admin.edukasi.panduan', [
            'search' => 'padi',
            'status' => 'Publik',
        ]));

        $response->assertOk();
        $response->assertSee('Panduan Padi Sawah');
        $response->assertDontSee('Panduan Jagung');
    }
}
