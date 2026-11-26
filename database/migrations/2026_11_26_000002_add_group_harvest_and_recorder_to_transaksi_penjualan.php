<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('transaksi_penjualan')) {
            return;
        }

        $addKelompok = !Schema::hasColumn('transaksi_penjualan', 'id_kelompok');
        $addPanen = !Schema::hasColumn('transaksi_penjualan', 'id_panen');
        $addPengurus = !Schema::hasColumn('transaksi_penjualan', 'id_pengurus');

        if ($addKelompok || $addPanen || $addPengurus) {
            Schema::table('transaksi_penjualan', function (Blueprint $table) use ($addKelompok, $addPanen, $addPengurus) {
                // Panjang kolom mengikuti tabel referensi: kelompok/pengguna 20, panen 30.
                if ($addKelompok) {
                    $table->string('id_kelompok', 20)->nullable()->index();
                }
                if ($addPanen) {
                    $table->string('id_panen', 30)->nullable()->index();
                }
                if ($addPengurus) {
                    $table->string('id_pengurus', 20)->nullable()->index();
                }
            });

            Schema::table('transaksi_penjualan', function (Blueprint $table) use ($addKelompok, $addPanen, $addPengurus) {
                if ($addKelompok) {
                    $table->foreign('id_kelompok', 'trx_penjualan_kelompok_fk')
                        ->references('id_kelompok')->on('kelompok_tani')->nullOnDelete();
                }
                if ($addPanen) {
                    $table->foreign('id_panen', 'trx_penjualan_panen_fk')
                        ->references('id_panen')->on('panen')->nullOnDelete();
                }
                if ($addPengurus) {
                    $table->foreign('id_pengurus', 'trx_penjualan_pengurus_fk')
                        ->references('id_pengguna')->on('pengguna')->nullOnDelete();
                }
            });
        }

        // Backfill transaksi lama dari Mobile/Marketplace berdasarkan kelompok penjual.
        if (Schema::hasColumn('transaksi_penjualan', 'id_kelompok')) {
            DB::table('transaksi_penjualan')
                ->select('id_penjualan', 'id_pengguna')
                ->whereNull('id_kelompok')
                ->orderBy('id_penjualan')
                ->chunkById(200, function ($rows): void {
                    foreach ($rows as $row) {
                        $idKelompok = DB::table('pengguna')
                            ->where('id_pengguna', $row->id_pengguna)
                            ->value('id_kelompok');

                        if ($idKelompok) {
                            DB::table('transaksi_penjualan')
                                ->where('id_penjualan', $row->id_penjualan)
                                ->whereNull('id_kelompok')
                                ->update(['id_kelompok' => $idKelompok]);
                        }
                    }
                }, 'id_penjualan');
        }

        // Migrasikan catatan dari tabel sementara revisi sebelumnya jika pernah dibuat.
        // Data lama dipertahankan dan dikonversi ke satu sumber transaksi utama.
        if (Schema::hasTable('penjualan')
            && Schema::hasColumn('penjualan', 'id_panen')
            && Schema::hasColumn('penjualan', 'id_pengurus')) {
            DB::table('penjualan')->whereNull('deleted_at')->orderBy('id_penjualan')->chunkById(200, function ($rows): void {
                foreach ($rows as $legacy) {
                    $legacyCode = 'LEGACY-' . $legacy->id_penjualan;
                    if (DB::table('transaksi_penjualan')->where('kode_transaksi', $legacyCode)->exists()) {
                        continue;
                    }

                    $panen = DB::table('panen')->where('id_panen', $legacy->id_panen)->first();
                    $idPengguna = $panen->id_pengguna ?? $legacy->id_pengurus;
                    $idKelompok = $legacy->id_kelompok ?? ($panen->id_kelompok ?? null);

                    if (!$idKelompok && $idPengguna) {
                        $idKelompok = DB::table('pengguna')->where('id_pengguna', $idPengguna)->value('id_kelompok');
                    }

                    if (!$idPengguna || !$idKelompok) {
                        continue;
                    }

                    $idPenjualan = 'LEG-' . strtoupper(substr(hash('sha256', (string) $legacy->id_penjualan), 0, 20));

                    DB::table('transaksi_penjualan')->insert([
                        'id_penjualan' => $idPenjualan,
                        'kode_transaksi' => $legacyCode,
                        'id_pengguna' => $idPengguna,
                        'id_kelompok' => $idKelompok,
                        'id_pengurus' => $legacy->id_pengurus,
                        'id_panen' => $legacy->id_panen,
                        'id_produk' => null,
                        'id_komoditas' => $panen->id_komoditas ?? null,
                        'nama_produk' => $legacy->komoditas_nama,
                        'nama_pembeli' => $legacy->nama_pembeli ?: 'Pelanggan',
                        'no_telepon_pembeli' => null,
                        'jumlah' => $legacy->berat_kg,
                        'satuan' => 'kg',
                        'harga_satuan' => $legacy->harga_per_kg,
                        'total_harga' => $legacy->total_harga,
                        'total_dibayar' => 0,
                        'status_pembayaran' => 'belum_dibayar',
                        'status_penyerahan' => 'belum_diserahkan',
                        'tanggal_transaksi' => $legacy->tanggal_penjualan,
                        'waktu_transaksi' => null,
                        'metode_pembayaran' => 'transfer',
                        'catatan' => $legacy->catatan,
                        'alasan_batal' => null,
                        'status_pengembalian_dana' => null,
                        'tanggal_diserahkan' => null,
                        'tanggal_dibatalkan' => null,
                        'created_at' => $legacy->created_at,
                        'updated_at' => $legacy->updated_at,
                    ]);
                }
            }, 'id_penjualan');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('transaksi_penjualan')) {
            return;
        }

        Schema::table('transaksi_penjualan', function (Blueprint $table) {
            $table->dropForeign('trx_penjualan_kelompok_fk');
            $table->dropForeign('trx_penjualan_panen_fk');
            $table->dropForeign('trx_penjualan_pengurus_fk');
        });

        $columns = array_values(array_filter(
            ['id_kelompok', 'id_panen', 'id_pengurus'],
            fn (string $column) => Schema::hasColumn('transaksi_penjualan', $column)
        ));

        if ($columns) {
            Schema::table('transaksi_penjualan', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }
};
