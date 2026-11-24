<?php

namespace App\Services;

use App\Models\Pengguna;
use App\Models\Produk;
use App\Models\RiwayatPembayaranPenjualan;
use App\Models\TransaksiPenjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

class PenjualanService
{
    /**
     * Ambil daftar transaksi penjualan milik petani dengan filter status.
     */
    public function getPenjualanForUser(Pengguna $user, Request $request)
    {
        $query = TransaksiPenjualan::with(['produk', 'komoditas', 'riwayatPembayaran'])
            ->where('id_pengguna', $user->id_pengguna);

        if ($status = $request->query('status')) {
            $query->where('status_pembayaran', $status);
        }

        if ($search = $request->query('q')) {
            $likeOp = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(function ($q) use ($search, $likeOp) {
                $q->where('nama_produk', $likeOp, "%{$search}%")
                  ->orWhere('nama_pembeli', $likeOp, "%{$search}%")
                  ->orWhere('kode_transaksi', $likeOp, "%{$search}%");
            });
        }

        return $query->orderByDesc('tanggal_transaksi')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Ambil detail transaksi penjualan milik petani.
     */
    public function getDetailPenjualan(Pengguna $user, string $idPenjualan): TransaksiPenjualan
    {
        $trx = TransaksiPenjualan::with(['produk', 'komoditas', 'riwayatPembayaran'])
            ->where('id_penjualan', $idPenjualan)
            ->firstOrFail();

        if ($trx->id_pengguna !== $user->id_pengguna) {
            throw new AccessDeniedHttpException('Anda tidak berhak mengakses transaksi ini.');
        }

        return $trx;
    }

    /**
     * Catat transaksi penjualan baru dengan dukungan snapshot & penahanan/pengurangan stok.
     */
    public function catatPenjualan(Pengguna $user, array $data): TransaksiPenjualan
    {
        return DB::transaction(function () use ($user, $data) {
            $idProduk = $data['id_produk'] ?? null;
            $produk = null;

            $jumlah = (float) ($data['jumlah'] ?? 0);
            if ($jumlah <= 0) {
                throw new InvalidArgumentException('Jumlah penjualan harus lebih dari 0.');
            }

            $hargaSatuan = (float) ($data['harga_satuan'] ?? 0);
            if ($hargaSatuan <= 0) {
                throw new InvalidArgumentException('Harga satuan harus lebih dari 0.');
            }

            $totalHarga = $jumlah * $hargaSatuan;
            $nominalDibayar = (float) ($data['nominal_dibayar'] ?? 0);

            // Tentukan status pembayaran awal
            if ($nominalDibayar >= $totalHarga) {
                $statusPembayaran = 'lunas';
                $nominalDibayar = $totalHarga; // Tidak boleh melebihi total harga
            } elseif ($nominalDibayar > 0) {
                $statusPembayaran = 'dp';
            } else {
                $statusPembayaran = $data['status_pembayaran'] ?? 'belum_dibayar';
            }

            // Jika terhubung dengan produk marketplace, cek dan perbarui stok dengan lock
            if ($idProduk) {
                $produk = Produk::where('id_produk', $idProduk)->lockForUpdate()->firstOrFail();

                if ($produk->id_pengguna !== $user->id_pengguna) {
                    throw new AccessDeniedHttpException('Anda hanya dapat mencatat transaksi dari produk milik sendiri.');
                }

                // Cek ketersediaan stok
                if ($produk->stok_tersedia < $jumlah) {
                    throw new InvalidArgumentException("Stok tersedia ({$produk->stok_tersedia} {$produk->satuan}) tidak mencukupi untuk jumlah transaksi ({$jumlah} {$produk->satuan}).");
                }

                // Aturan penahanan dan pengurangan stok
                if ($statusPembayaran === 'lunas') {
                    // Transaksi lunas: stok fisik langsung berkurang
                    $produk->stok_fisik = max(0.0, $produk->stok_fisik - $jumlah);
                } else {
                    // Transaksi DP atau Belum Lunas: stok fisik belum berkurang, tetapi stok ditahan bertambah
                    $produk->stok_ditahan += $jumlah;
                }

                // Jika stok habis, update status katalog
                if ($produk->stok_tersedia <= 0) {
                    $produk->status = 'stok_habis';
                }

                $produk->save();
            }

            // Buat record Transaksi Penjualan
            $trx = new TransaksiPenjualan();
            $trx->id_pengguna = $user->id_pengguna;
            $trx->id_produk = $idProduk;
            $trx->id_komoditas = $data['id_komoditas'] ?? ($produk?->id_komoditas);
            $trx->nama_produk = $data['nama_produk'] ?? ($produk?->nama_produk ?: 'Hasil Tani');
            $trx->nama_pembeli = $data['nama_pembeli'] ?? 'Pelanggan';
            $trx->no_telepon_pembeli = $data['no_telepon_pembeli'] ?? null;
            $trx->jumlah = $jumlah;
            $trx->satuan = strtolower($data['satuan'] ?? ($produk?->satuan ?: 'kg'));
            $trx->harga_satuan = $hargaSatuan;
            $trx->total_harga = $totalHarga;
            $trx->total_dibayar = $nominalDibayar;
            $trx->status_pembayaran = $statusPembayaran;
            $trx->status_penyerahan = 'belum_diserahkan';
            $trx->tanggal_transaksi = $data['tanggal_transaksi'] ?? now()->toDateString();
            $trx->waktu_transaksi = $data['waktu_transaksi'] ?? now()->format('H:i');
            $trx->metode_pembayaran = $data['metode_pembayaran'] ?? 'transfer';
            $trx->catatan = $data['catatan'] ?? null;
            $trx->save();

            // Catat pembayaran awal jika ada nominal dibayar
            if ($nominalDibayar > 0) {
                RiwayatPembayaranPenjualan::create([
                    'id_penjualan' => $trx->id_penjualan,
                    'nominal' => $nominalDibayar,
                    'tanggal_pembayaran' => $trx->tanggal_transaksi,
                    'keterangan' => $statusPembayaran === 'lunas' ? 'Pembayaran Penuh / Pelunasan' : 'Uang Muka (DP)',
                    'status_verifikasi' => 'terverifikasi',
                ]);
            }

            return $trx->load(['produk', 'komoditas', 'riwayatPembayaran']);
        });
    }

    /**
     * Tambah pembayaran bertahap (DP tambahan / cicilan pelunasan).
     */
    public function tambahPembayaran(Pengguna $user, string $idPenjualan, array $data): TransaksiPenjualan
    {
        return DB::transaction(function () use ($user, $idPenjualan, $data) {
            $trx = TransaksiPenjualan::where('id_penjualan', $idPenjualan)->lockForUpdate()->firstOrFail();

            if ($trx->id_pengguna !== $user->id_pengguna) {
                throw new AccessDeniedHttpException('Anda tidak berhak mengubah transaksi ini.');
            }

            if ($trx->status_pembayaran === 'dibatalkan') {
                throw new InvalidArgumentException('Tidak dapat menambahkan pembayaran untuk transaksi yang sudah dibatalkan.');
            }

            if ($trx->status_pembayaran === 'lunas') {
                throw new InvalidArgumentException('Transaksi ini sudah berstatus Lunas.');
            }

            $nominal = (float) ($data['nominal'] ?? 0);
            if ($nominal <= 0) {
                throw new InvalidArgumentException('Nominal pembayaran harus lebih dari 0.');
            }

            $sisa = $trx->sisa_pembayaran;
            if ($nominal > $sisa) {
                throw new InvalidArgumentException('Nominal pembayaran melebihi sisa tagihan (Rp ' . number_format($sisa, 0, ',', '.') . ').');
            }

            // Catat pembayaran
            RiwayatPembayaranPenjualan::create([
                'id_penjualan' => $trx->id_penjualan,
                'nominal' => $nominal,
                'tanggal_pembayaran' => $data['tanggal_pembayaran'] ?? now()->toDateString(),
                'keterangan' => $data['keterangan'] ?? 'Pembayaran Bertahap',
                'status_verifikasi' => 'terverifikasi',
                'bukti_transfer' => $data['bukti_transfer'] ?? null,
            ]);

            // Update total dibayar
            $trx->total_dibayar += $nominal;
            if ($trx->status_pembayaran === 'belum_dibayar') {
                $trx->status_pembayaran = 'dp';
            }
            $trx->save();

            return $trx->load(['produk', 'komoditas', 'riwayatPembayaran']);
        });
    }

    /**
     * Tandai transaksi sebagai Lunas setelah verifikasi pembayaran penuh.
     * Mengurangi stok fisik dan melepaskan stok yang sebelumnya ditahan.
     */
    public function tandaiLunas(Pengguna $user, string $idPenjualan): TransaksiPenjualan
    {
        return DB::transaction(function () use ($user, $idPenjualan) {
            $trx = TransaksiPenjualan::where('id_penjualan', $idPenjualan)->lockForUpdate()->firstOrFail();

            if ($trx->id_pengguna !== $user->id_pengguna) {
                throw new AccessDeniedHttpException('Anda tidak berhak mengubah transaksi ini.');
            }

            if ($trx->status_pembayaran === 'lunas') {
                return $trx;
            }

            if ($trx->status_pembayaran === 'dibatalkan') {
                throw new InvalidArgumentException('Transaksi yang sudah dibatalkan tidak dapat ditandai lunas.');
            }

            // Validasi pembayaran harus sudah mencapai total transaksi
            if ($trx->total_dibayar < $trx->total_harga) {
                $sisa = $trx->total_harga - $trx->total_dibayar;
                throw new InvalidArgumentException('Pembayaran belum penuh. Masih ada sisa tagihan Rp ' . number_format($sisa, 0, ',', '.') . '.');
            }

            // Jika terhubung ke produk marketplace, kurangi stok fisik dan lepaskan stok ditahan
            if ($trx->id_produk) {
                $produk = Produk::where('id_produk', $trx->id_produk)->lockForUpdate()->first();
                if ($produk) {
                    $stokDitahanSebelumnya = min($produk->stok_ditahan, $trx->jumlah);
                    // Lepaskan stok yang ditahan
                    $produk->stok_ditahan = max(0.0, $produk->stok_ditahan - $stokDitahanSebelumnya);
                    // Kurangi stok fisik
                    $produk->stok_fisik = max(0.0, $produk->stok_fisik - $trx->jumlah);

                    if ($produk->stok_tersedia <= 0) {
                        $produk->status = 'stok_habis';
                    }
                    $produk->save();
                }
            }

            $trx->status_pembayaran = 'lunas';
            $trx->save();

            return $trx->load(['produk', 'komoditas', 'riwayatPembayaran']);
        });
    }

    /**
     * Batalkan transaksi sebelum barang diserahkan.
     * Melepaskan stok ditahan atau mengembalikan stok fisik jika sebelumnya lunas.
     */
    public function batalkanTransaksi(Pengguna $user, string $idPenjualan, string $alasanBatal): TransaksiPenjualan
    {
        return DB::transaction(function () use ($user, $idPenjualan, $alasanBatal) {
            $trx = TransaksiPenjualan::where('id_penjualan', $idPenjualan)->lockForUpdate()->firstOrFail();

            if ($trx->id_pengguna !== $user->id_pengguna) {
                throw new AccessDeniedHttpException('Anda tidak berhak membatalkan transaksi ini.');
            }

            if ($trx->status_pembayaran === 'dibatalkan') {
                throw new InvalidArgumentException('Transaksi ini sudah dibatalkan sebelumnya.');
            }

            // Validasi penyerahan barang
            if ($trx->status_penyerahan === 'diserahkan') {
                throw new InvalidArgumentException('Transaksi yang barangnya sudah diserahkan tidak dapat dibatalkan melalui sistem.');
            }

            // Restorasi stok pada produk sumber jika ada
            if ($trx->id_produk) {
                $produk = Produk::where('id_produk', $trx->id_produk)->lockForUpdate()->first();
                if ($produk) {
                    if ($trx->status_pembayaran === 'lunas') {
                        // Jika sudah lunas tapi belum diserahkan, stok fisik dikembalikan
                        $produk->stok_fisik += $trx->jumlah;
                    } else {
                        // Jika masih status DP/belum lunas, stok ditahan dilepaskan
                        $produk->stok_ditahan = max(0.0, $produk->stok_ditahan - $trx->jumlah);
                    }

                    // Jika stok pulih dan statusnya 'stok_habis', kembalikan ke 'aktif'
                    if ($produk->status === 'stok_habis' && $produk->stok_tersedia > 0) {
                        $produk->status = 'aktif';
                    }

                    $produk->save();
                }
            }

            // Update status pembatalan
            $trx->status_pembayaran = 'dibatalkan';
            $trx->alasan_batal = $alasanBatal;
            $trx->tanggal_dibatalkan = now();
            if ($trx->total_dibayar > 0) {
                $trx->status_pengembalian_dana = 'belum_dikembalikan';
            }
            $trx->save();

            return $trx->load(['produk', 'komoditas', 'riwayatPembayaran']);
        });
    }

    /**
     * Tandai barang sudah diserahkan kepada pembeli (mengunci opsi pembatalan).
     */
    public function tandaiBarangDiserahkan(Pengguna $user, string $idPenjualan): TransaksiPenjualan
    {
        return DB::transaction(function () use ($user, $idPenjualan) {
            $trx = TransaksiPenjualan::where('id_penjualan', $idPenjualan)->lockForUpdate()->firstOrFail();

            if ($trx->id_pengguna !== $user->id_pengguna) {
                throw new AccessDeniedHttpException('Anda tidak berhak memperbarui transaksi ini.');
            }

            if ($trx->status_pembayaran === 'dibatalkan') {
                throw new InvalidArgumentException('Transaksi yang sudah dibatalkan tidak dapat diserahkan.');
            }

            $trx->status_penyerahan = 'diserahkan';
            $trx->tanggal_diserahkan = now();
            $trx->save();

            return $trx->load(['produk', 'komoditas', 'riwayatPembayaran']);
        });
    }
}
