<?php

namespace Tests\Feature;

use App\Models\Artikel;
use App\Models\Panduan;
use App\Models\Tip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicEdukasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_homepage_loads_latest_published_tips_and_articles(): void
    {
        Tip::create([
            'judul' => 'Tips Uji Pengelolaan Air',
            'kategori' => 'Irigasi & Air',
            'ringkasan' => 'Ringkasan tips pengelolaan air untuk pengujian.',
            'isi' => 'Isi tips pengelolaan air untuk pengujian.',
            'tanggal' => '2026-11-01',
            'status' => 'Publik',
        ]);

        Artikel::create([
            'judul' => 'Artikel Uji Pemantauan Tanaman',
            'kategori' => 'Perawatan Tanaman',
            'ringkasan' => 'Ringkasan artikel pemantauan tanaman untuk pengujian.',
            'isi' => 'Isi artikel pemantauan tanaman untuk pengujian.',
            'tanggal' => '2026-11-02',
            'status' => 'Publik',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Tips Uji Pengelolaan Air')
            ->assertSee('Artikel Uji Pemantauan Tanaman');
    }

    public function test_public_tip_detail_can_be_opened_but_draft_tip_is_hidden(): void
    {
        $tip = Tip::create([
            'judul' => 'Tips Publik Uji',
            'kategori' => 'Irigasi & Air',
            'ringkasan' => 'Ringkasan tips yang dipublikasikan.',
            'isi' => 'Isi tips yang dipublikasikan untuk dibaca pengunjung.',
            'tanggal' => '2026-11-01',
            'status' => 'Publik',
        ]);

        $draft = Tip::create([
            'judul' => 'Tips Draft Uji',
            'kategori' => 'Irigasi & Air',
            'ringkasan' => 'Ringkasan tips draft.',
            'isi' => 'Isi tips draft yang tidak boleh dibuka pengunjung.',
            'status' => 'Draft',
        ]);

        $this->get(route('tips.show', $tip->id_tips))
            ->assertOk()
            ->assertSee('Tips Publik Uji')
            ->assertSee('Isi tips yang dipublikasikan');

        $this->get(route('tips.show', $draft->id_tips))->assertNotFound();
    }

    public function test_public_article_detail_can_be_opened_but_draft_article_is_hidden(): void
    {
        $artikel = Artikel::create([
            'judul' => 'Artikel Publik Uji',
            'kategori' => 'Perawatan Tanaman',
            'ringkasan' => 'Ringkasan artikel yang dipublikasikan.',
            'isi' => 'Isi artikel yang dipublikasikan untuk dibaca pengunjung.',
            'tanggal' => '2026-11-02',
            'status' => 'Publik',
        ]);

        $draft = Artikel::create([
            'judul' => 'Artikel Draft Uji',
            'kategori' => 'Perawatan Tanaman',
            'ringkasan' => 'Ringkasan artikel draft.',
            'isi' => 'Isi artikel draft yang tidak boleh dibuka pengunjung.',
            'status' => 'Draft',
        ]);

        $this->get(route('artikel.show', $artikel->id_artikel))
            ->assertOk()
            ->assertSee('Artikel Publik Uji')
            ->assertSee('Isi artikel yang dipublikasikan');

        $this->get(route('artikel.show', $draft->id_artikel))->assertNotFound();
    }

    public function test_public_panduan_detail_route_works_with_slug(): void
    {
        $panduan = Panduan::create([
            'judul' => 'Panduan Uji Route Publik',
            'slug' => 'panduan-uji-route-publik',
            'kategori' => 'Budidaya Tanaman',
            'ringkasan' => 'Ringkasan panduan untuk pengujian route detail.',
            'isi' => 'Isi panduan untuk memastikan route detail dapat dibuka.',
            'tanggal' => '2026-11-03',
            'status' => 'Publik',
        ]);

        $this->get(route('panduan.show', $panduan->slug))
            ->assertOk()
            ->assertSee('Panduan Uji Route Publik')
            ->assertSee('Isi panduan untuk memastikan route detail dapat dibuka.');
    }
}
