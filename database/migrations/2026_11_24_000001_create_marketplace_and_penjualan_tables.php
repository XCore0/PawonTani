<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah pengaturan kontak publik ke tabel pengguna jika belum ada
        if (Schema::hasTable('pengguna') && !Schema::hasColumn('pengguna', 'kontak_publik_aktif')) {
            Schema::table('pengguna', function (Blueprint $table) {
                $table->boolean('kontak_publik_aktif')->default(true)->after('no_telepon');
            });
        }

        // 2. Tabel Produk Marketplace
        if (!Schema::hasTable('produk')) {
            Schema::create('produk', function (Blueprint $table) {
                $table->string('id_produk', 30)->primary();
                $table->string('id_pengguna', 20)->index();
                $table->string('id_komoditas', 20)->nullable()->index();
                $table->string('nama_produk', 150);
                $table->string('foto_produk', 255)->nullable();
                $table->text('deskripsi')->nullable();
                $table->string('jenis_harga', 20)->default('tetap'); // tetap | rentang
                $table->decimal('harga', 14, 2)->default(0);
                $table->decimal('harga_min', 14, 2)->nullable();
                $table->decimal('harga_max', 14, 2)->nullable();
                $table->string('satuan', 30)->default('kg');
                $table->decimal('stok_fisik', 12, 2)->default(0);
                $table->decimal('stok_ditahan', 12, 2)->default(0); // Direservasi untuk DP/komitmen aktif
                $table->string('status', 30)->default('aktif')->index(); // aktif, tidak_aktif, stok_habis, diarsipkan
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('id_pengguna')
                    ->references('id_pengguna')
                    ->on('pengguna')
                    ->onDelete('cascade');

                $table->foreign('id_komoditas')
                    ->references('id_komoditas')
                    ->on('komoditas')
                    ->nullOnDelete();
            });
        }

        // 3. Tabel Transaksi Penjualan
        if (!Schema::hasTable('transaksi_penjualan')) {
            Schema::create('transaksi_penjualan', function (Blueprint $table) {
                $table->string('id_penjualan', 30)->primary();
                $table->string('kode_transaksi', 50)->unique();
                $table->string('id_pengguna', 20)->index(); // Penjual / Petani
                $table->string('id_produk', 30)->nullable()->index(); // Relasi produk marketplace (opsional)
                $table->string('id_komoditas', 20)->nullable()->index();
                $table->string('nama_produk', 150); // Snapshot nama produk
                $table->string('nama_pembeli', 150)->default('Pelanggan');
                $table->string('no_telepon_pembeli', 30)->nullable();
                $table->decimal('jumlah', 12, 2)->default(0); // Volume / Qty
                $table->string('satuan', 30)->default('kg');
                $table->decimal('harga_satuan', 14, 2)->default(0); // Snapshot harga disepakati
                $table->decimal('total_harga', 14, 2)->default(0);
                $table->decimal('total_dibayar', 14, 2)->default(0);
                $table->string('status_pembayaran', 30)->default('belum_dibayar')->index(); // belum_dibayar, dp, lunas, tempo, dibatalkan
                $table->string('status_penyerahan', 30)->default('belum_diserahkan')->index(); // belum_diserahkan, diserahkan
                $table->date('tanggal_transaksi')->index();
                $table->string('waktu_transaksi', 20)->nullable();
                $table->string('metode_pembayaran', 50)->default('transfer'); // transfer_bri, tunai, tempo, dll
                $table->text('catatan')->nullable();
                $table->text('alasan_batal')->nullable();
                $table->string('status_pengembalian_dana', 30)->nullable(); // belum_dikembalikan, sudah_dikembalikan
                $table->timestamp('tanggal_diserahkan')->nullable();
                $table->timestamp('tanggal_dibatalkan')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('id_pengguna')
                    ->references('id_pengguna')
                    ->on('pengguna')
                    ->onDelete('cascade');

                $table->foreign('id_produk')
                    ->references('id_produk')
                    ->on('produk')
                    ->nullOnDelete();

                $table->foreign('id_komoditas')
                    ->references('id_komoditas')
                    ->on('komoditas')
                    ->nullOnDelete();
            });
        }

        // 4. Tabel Riwayat Pembayaran Penjualan (Multi-stage DP & Pelunasan)
        if (!Schema::hasTable('riwayat_pembayaran_penjualan')) {
            Schema::create('riwayat_pembayaran_penjualan', function (Blueprint $table) {
                $table->string('id_pembayaran', 30)->primary();
                $table->string('id_penjualan', 30)->index();
                $table->decimal('nominal', 14, 2);
                $table->date('tanggal_pembayaran');
                $table->text('keterangan')->nullable();
                $table->string('status_verifikasi', 30)->default('terverifikasi'); // menunggu, terverifikasi, ditolak
                $table->string('bukti_transfer', 255)->nullable();
                $table->timestamps();

                $table->foreign('id_penjualan')
                    ->references('id_penjualan')
                    ->on('transaksi_penjualan')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_pembayaran_penjualan');
        Schema::dropIfExists('transaksi_penjualan');
        Schema::dropIfExists('produk');

        if (Schema::hasTable('pengguna') && Schema::hasColumn('pengguna', 'kontak_publik_aktif')) {
            Schema::table('pengguna', function (Blueprint $table) {
                $table->dropColumn('kontak_publik_aktif');
            });
        }
    }
};
