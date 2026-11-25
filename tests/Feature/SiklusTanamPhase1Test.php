<?php

namespace Tests\Feature;

use App\Models\KelompokTani;
use App\Models\Komoditas;
use App\Models\Lahan;
use App\Models\Pengguna;
use App\Models\SiklusTanam;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SiklusTanamPhase1Test extends TestCase
{
    /**
     * Uji pembuatan ID otomatis format SKL-YYYYMMDD-XXXX dan relasi dasar model.
     */
    public function test_siklus_tanam_auto_id_and_relationships()
    {
        $lahan = Lahan::first();
        $this->assertNotNull($lahan, 'Lahan tidak boleh kosong');

        $user = Pengguna::first();
        $komoditas = Komoditas::first();
        $kelompok = KelompokTani::first();

        DB::beginTransaction();
        try {
            $siklus = new SiklusTanam();
            $siklus->id_lahan = $lahan->id_lahan;
            $siklus->id_pengguna = $user->id_pengguna;
            $siklus->id_kelompok = $kelompok->id_kelompok;
            $siklus->id_komoditas = $komoditas->id_komoditas;
            $siklus->komoditas_nama = $komoditas->nama_komoditas;
            $siklus->varietas = 'Varietas Uji Coba';
            $siklus->musim_tanam = 'Musim Hujan 1';
            $siklus->tanggal_mulai = now()->toDateString();
            $siklus->status = SiklusTanam::STATUS_AKTIF;
            $siklus->save();

            // Verifikasi ID
            $this->assertNotEmpty($siklus->id_siklus);
            $this->assertMatchesRegularExpression('/^SKL-\d{8}-[A-Z0-9]{4}$/', $siklus->id_siklus);

            // Verifikasi relasi dari Lahan
            $lahanFresh = $lahan->fresh();
            $this->assertNotNull($lahanFresh->siklusBerjalan);
            $this->assertEquals($siklus->id_siklus, $lahanFresh->siklusBerjalan->id_siklus);
            $this->assertNotNull($lahanFresh->siklusAktif);
            $this->assertEquals($siklus->id_siklus, $lahanFresh->siklusAktif->id_siklus);

            // Verifikasi scope
            $this->assertTrue(SiklusTanam::berjalan()->where('id_siklus', $siklus->id_siklus)->exists());
            $this->assertTrue(SiklusTanam::bolehInput()->where('id_siklus', $siklus->id_siklus)->exists());
        } finally {
            DB::rollBack();
        }
    }

    /**
     * Uji partial unique index di PostgreSQL: tidak boleh ada 2 siklus berjalan pada lahan yang sama.
     */
    public function test_cannot_have_multiple_running_cycles_on_same_lahan()
    {
        $lahan = Lahan::first();
        $user = Pengguna::first();
        $komoditas = Komoditas::first();
        $kelompok = KelompokTani::first();

        DB::beginTransaction();
        try {
            // Siklus 1: Aktif
            $siklus1 = new SiklusTanam();
            $siklus1->id_lahan = $lahan->id_lahan;
            $siklus1->id_pengguna = $user->id_pengguna;
            $siklus1->id_kelompok = $kelompok->id_kelompok;
            $siklus1->id_komoditas = $komoditas->id_komoditas;
            $siklus1->komoditas_nama = $komoditas->nama_komoditas;
            $siklus1->tanggal_mulai = now()->toDateString();
            $siklus1->status = SiklusTanam::STATUS_AKTIF;
            $siklus1->save();

            $this->assertNotEmpty($siklus1->id_siklus);

            // Siklus 2: Mencoba aktif lagi pada lahan yang sama (harus ditolak oleh database unique index)
            $siklus2 = new SiklusTanam();
            $siklus2->id_lahan = $lahan->id_lahan;
            $siklus2->id_pengguna = $user->id_pengguna;
            $siklus2->id_kelompok = $kelompok->id_kelompok;
            $siklus2->id_komoditas = $komoditas->id_komoditas;
            $siklus2->komoditas_nama = $komoditas->nama_komoditas;
            $siklus2->tanggal_mulai = now()->toDateString();
            $siklus2->status = SiklusTanam::STATUS_MENUNGGU_SELESAI;

            $this->expectException(QueryException::class);
            $siklus2->save();
        } finally {
            DB::rollBack();
        }
    }

    /**
     * Uji bahwa siklus selesai atau gagal memperbolehkan siklus baru dibuat pada lahan yang sama.
     */
    public function test_completed_cycle_allows_new_cycle_on_same_lahan()
    {
        $lahan = Lahan::first();
        $user = Pengguna::first();
        $komoditas = Komoditas::first();
        $kelompok = KelompokTani::first();

        DB::beginTransaction();
        try {
            // Siklus 1: Selesai
            $siklus1 = new SiklusTanam();
            $siklus1->id_lahan = $lahan->id_lahan;
            $siklus1->id_pengguna = $user->id_pengguna;
            $siklus1->id_kelompok = $kelompok->id_kelompok;
            $siklus1->id_komoditas = $komoditas->id_komoditas;
            $siklus1->komoditas_nama = $komoditas->nama_komoditas;
            $siklus1->tanggal_mulai = now()->subDays(60)->toDateString();
            $siklus1->tanggal_selesai = now()->toDateString();
            $siklus1->status = SiklusTanam::STATUS_SELESAI;
            $siklus1->save();

            // Siklus 2: Aktif (harus berhasil karena siklus1 sudah terminal)
            $siklus2 = new SiklusTanam();
            $siklus2->id_lahan = $lahan->id_lahan;
            $siklus2->id_pengguna = $user->id_pengguna;
            $siklus2->id_kelompok = $kelompok->id_kelompok;
            $siklus2->id_komoditas = $komoditas->id_komoditas;
            $siklus2->komoditas_nama = $komoditas->nama_komoditas;
            $siklus2->tanggal_mulai = now()->toDateString();
            $siklus2->status = SiklusTanam::STATUS_AKTIF;
            $siklus2->save();

            $this->assertNotEmpty($siklus2->id_siklus);
            $this->assertNotEquals($siklus1->id_siklus, $siklus2->id_siklus);

            $lahanFresh = $lahan->fresh();
            $this->assertEquals($siklus2->id_siklus, $lahanFresh->siklusBerjalan->id_siklus);
            $this->assertCount(2, $lahanFresh->siklusTanam);
        } finally {
            DB::rollBack();
        }
    }

    /**
     * Uji integritas data historis: memastikan jumlah aktivitas legacy (10) dan panen legacy (4) tetap utuh.
     */
    public function test_legacy_data_integrity()
    {
        $aktivitasCount = DB::table('aktivitas_tanam')->count();
        $panenCount = DB::table('panen')->count();

        $this->assertEquals(10, $aktivitasCount, 'Data aktivitas legacy harus tetap 10');
        $this->assertEquals(4, $panenCount, 'Data panen legacy harus tetap 4');
    }
}
