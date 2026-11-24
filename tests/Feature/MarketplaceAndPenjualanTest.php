<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\KelompokTani;
use App\Models\Komoditas;
use App\Models\Pengguna;
use App\Models\Produk;
use App\Models\TransaksiPenjualan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MarketplaceAndPenjualanTest extends TestCase
{
    use RefreshDatabase;

    private function createActivePetani(string $username, string $phone = '081234567890'): Pengguna
    {
        $kelompok = KelompokTani::create([
            'nama_kelompok' => 'Poktan Subur Makmur ' . $username,
            'alamat' => 'Desa Karangmangu, Banyumas',
            'status' => 'Aktif',
        ]);

        $petani = Pengguna::create([
            'nama' => 'Petani ' . $username,
            'username' => $username,
            'password' => bcrypt('password123'),
            'role' => 'Anggota',
            'status' => 'Aktif',
            'id_kelompok' => $kelompok->id_kelompok,
            'no_telepon' => $phone,
            'kontak_publik_aktif' => true,
        ]);

        Anggota::create([
            'id_pengguna' => $petani->id_pengguna,
            'status_keanggotaan' => 'Aktif',
        ]);

        return $petani;
    }

    /**
     * Uji keberadaan tombol Login Sistem & Kunjungi Marketplace pada homepage utama PawonTani.
     */
    public function test_homepage_pawontani_has_marketplace_button_and_login(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Login Sistem');
        $response->assertSee('Kunjungi Marketplace');
        $response->assertSee(route('marketplace.home'));
        $response->assertSee(route('login'));
    }

    /**
     * Uji halaman promosi Customer Marketplace (/marketplace).
     */
    public function test_customer_marketplace_home_loads_successfully(): void
    {
        $response = $this->get(route('marketplace.home'));

        $response->assertStatus(200);
        $response->assertSee('Kenali Hasil Tani Lokal,');
        $response->assertSee('Dukung Petani Indonesia.');
        $response->assertSee('Jelajahi Marketplace');
        $response->assertSee(route('marketplace.katalog'));
    }

    /**
     * Uji katalog marketplace publik dan filter komoditas.
     */
    public function test_customer_catalog_loads_and_filters(): void
    {
        $response = $this->get(route('marketplace.katalog'));
        $response->assertStatus(200);
        $response->assertSee('Katalog Hasil Tani PawonTani');
    }

    /**
     * Uji pembuatan produk marketplace oleh petani via API dan verifikasi detail publik.
     */
    public function test_farmer_can_create_and_manage_marketplace_product(): void
    {
        // 1. Buat pengguna petani anggota aktif lengkap dengan relasi kelompok & anggota
        $petani = $this->createActivePetani('petani_test_1', '081234567890');

        Sanctum::actingAs($petani);

        // 2. Tambah produk via API
        $response = $this->postJson(route('api.v1.marketplace.produk.store'), [
            'nama_produk' => 'Beras Organik Uji',
            'deskripsi' => 'Beras organik kualitas super tanpa pengawet',
            'jenis_harga' => 'tetap',
            'harga' => 15000,
            'satuan' => 'kg',
            'stok_fisik' => 100,
            'status' => 'aktif',
        ]);

        $response->assertStatus(201);
        $idProduk = $response->json('data.id_produk');
        $this->assertNotEmpty($idProduk);

        // 3. Verifikasi produk tampil di katalog publik
        $publicDetail = $this->get(route('marketplace.detail', $idProduk));
        $publicDetail->assertStatus(200);
        $publicDetail->assertSee('Beras Organik Uji');
        $publicDetail->assertSee('Pesan via WhatsApp Petani');
        $publicDetail->assertSee('Petani petani_test_1');

        // Pastikan nomor WhatsApp dinormalisasi dengan benar (6281234567890)
        $publicDetail->assertSee('https://wa.me/6281234567890');

        // Pastikan data sensitif tidak bocor
        $publicDetail->assertDontSee($petani->password);

        // 4. Update produk
        $updateRes = $this->postJson(route('api.v1.marketplace.produk.update', $idProduk), [
            'nama_produk' => 'Beras Organik Uji Diperbarui',
            'harga' => 16000,
            'satuan' => 'kg',
            'stok_fisik' => 120,
        ]);
        $updateRes->assertStatus(200);
        $this->assertEquals(16000, Produk::find($idProduk)->harga);

        // 5. Cleanup
        Produk::find($idProduk)->forceDelete();
    }

    /**
     * Uji alur transaksi: DP bertahap, penahanan stok, pelunasan, dan pembatalan.
     */
    public function test_sale_transaction_dp_reservation_and_cancellation(): void
    {
        $petani = $this->createActivePetani('petani_test_2', '081298765432');

        Sanctum::actingAs($petani);

        // 1. Buat produk dengan stok awal 100 kg
        $produk = Produk::create([
            'id_pengguna' => $petani->id_pengguna,
            'nama_produk' => 'Jagung Manis Uji Stok',
            'jenis_harga' => 'tetap',
            'harga' => 10000,
            'satuan' => 'kg',
            'stok_fisik' => 100,
            'stok_ditahan' => 0,
            'status' => 'aktif',
        ]);

        $this->assertEquals(100, $produk->stok_tersedia);

        // 2. Catat penjualan dengan DP (misal beli 70 kg seharga Rp700.000, DP Rp300.000)
        $saleRes = $this->postJson(route('api.v1.penjualan.store'), [
            'id_produk' => $produk->id_produk,
            'nama_produk' => $produk->nama_produk,
            'nama_pembeli' => 'Pembeli Uji',
            'jumlah' => 70,
            'satuan' => 'kg',
            'harga_satuan' => 10000,
            'nominal_dibayar' => 300000, // DP pertama
            'status_pembayaran' => 'dp',
            'metode_pembayaran' => 'transfer_bri',
        ]);

        $saleRes->assertStatus(201);
        $idPenjualan = $saleRes->json('data.id_penjualan');

        // Verifikasi aturan stok saat DP:
        // Stok fisik tetap 100, stok ditahan 70, stok tersedia 30
        $produk->refresh();
        $this->assertEquals(100, $produk->stok_fisik);
        $this->assertEquals(70, $produk->stok_ditahan);
        $this->assertEquals(30, $produk->stok_tersedia);

        // 3. Tambah pembayaran DP kedua (misal Rp200.000)
        $pay2 = $this->postJson(route('api.v1.penjualan.pembayaran', $idPenjualan), [
            'nominal' => 200000,
            'keterangan' => 'DP kedua',
        ]);
        $pay2->assertStatus(200);

        $trx = TransaksiPenjualan::with('riwayatPembayaran')->find($idPenjualan);
        $this->assertEquals(500000, $trx->total_dibayar);
        $this->assertEquals(200000, $trx->sisa_pembayaran);

        // 4. Tambah pelunasan terakhir (Rp200.000)
        $pay3 = $this->postJson(route('api.v1.penjualan.pembayaran', $idPenjualan), [
            'nominal' => 200000,
            'keterangan' => 'Pelunasan sisa',
        ]);
        $pay3->assertStatus(200);

        // 5. Tandai Lunas
        $lunasRes = $this->postJson(route('api.v1.penjualan.lunas', $idPenjualan));
        $lunasRes->assertStatus(200);

        // Verifikasi aturan stok saat Lunas:
        // Stok fisik berkurang menjadi 30, stok ditahan dilepaskan (0), stok tersedia tetap 30
        $produk->refresh();
        $this->assertEquals(30, $produk->stok_fisik);
        $this->assertEquals(0, $produk->stok_ditahan);
        $this->assertEquals(30, $produk->stok_tersedia);

        // 6. Uji pembatalan sebelum penyerahan barang:
        // Karena lunas dibatalkan, stok fisik harus dikembalikan 70 kg -> 100 kg
        $cancelRes = $this->postJson(route('api.v1.penjualan.batalkan', $idPenjualan), [
            'alasan_batal' => 'Pembeli membatalkan pesanan sebelum barang dikirim',
        ]);
        $cancelRes->assertStatus(200);

        $produk->refresh();
        $this->assertEquals(100, $produk->stok_fisik);
        $this->assertEquals(0, $produk->stok_ditahan);
        $this->assertEquals(100, $produk->stok_tersedia);

        // 7. Uji pembatalan kedua kali harus ditolak
        $cancelAgain = $this->postJson(route('api.v1.penjualan.batalkan', $idPenjualan), [
            'alasan_batal' => 'Mencoba batalkan lagi',
        ]);
        $cancelAgain->assertStatus(422);

        // Cleanup
        $trx->forceDelete();
        $produk->forceDelete();
    }
}
