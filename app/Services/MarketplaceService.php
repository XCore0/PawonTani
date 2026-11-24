<?php

namespace App\Services;

use App\Models\Pengguna;
use App\Models\Produk;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class MarketplaceService
{
    public function __construct(
        protected CloudinaryService $cloudinaryService
    ) {}

    /**
     * Query produk publik untuk Website Customer dengan filter, pencarian, dan pengurutan.
     */
    public function getPublicCatalog(Request $request, int $perPage = 12)
    {
        $query = Produk::with(['pengguna.kelompokTani', 'komoditas'])
            ->publik();

        // 1. Pencarian berdasarkan nama produk
        if ($search = $request->query('q')) {
            $likeOp = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(function (Builder $q) use ($search, $likeOp) {
                $q->where('nama_produk', $likeOp, "%{$search}%")
                  ->orWhere('deskripsi', $likeOp, "%{$search}%");
            });
        }

        // 2. Filter kategori/komoditas
        if ($idKomoditas = $request->query('komoditas')) {
            $query->where('id_komoditas', $idKomoditas);
        }

        // 3. Pengurutan
        $sort = $request->query('sort', 'terbaru');
        match ($sort) {
            'nama_asc' => $query->orderBy('nama_produk', 'asc'),
            'nama_desc' => $query->orderBy('nama_produk', 'desc'),
            'harga_asc' => $query->orderByRaw('COALESCE(harga_min, harga) ASC'),
            'harga_desc' => $query->orderByRaw('COALESCE(harga_max, harga) DESC'),
            default => $query->orderByDesc('created_at'),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Detail produk publik beserta rekomendasi produk lain dari penjual.
     */
    public function getPublicDetail(string $idProduk): array
    {
        $produk = Produk::with(['pengguna.kelompokTani', 'komoditas'])
            ->publik()
            ->where('id_produk', $idProduk)
            ->firstOrFail();

        // Produk lain dari penjual yang sama
        $produkLain = Produk::with('komoditas')
            ->publik()
            ->where('id_pengguna', $produk->id_pengguna)
            ->where('id_produk', '!=', $idProduk)
            ->latest()
            ->take(4)
            ->get();

        return [
            'produk' => $produk,
            'produk_lain' => $produkLain,
        ];
    }

    /**
     * Ambil data profil publik penjual yang aman (tanpa NIK, password, alamat lengkap personal).
     */
    public function getPublicSellerProfile(string $idPengguna): array
    {
        $seller = Pengguna::with('kelompokTani')
            ->where('id_pengguna', $idPengguna)
            ->where('status', 'Aktif')
            ->firstOrFail();

        $produkList = Produk::with('komoditas')
            ->publik()
            ->where('id_pengguna', $idPengguna)
            ->latest()
            ->paginate(12);

        return [
            'penjual' => [
                'id_pengguna' => $seller->id_pengguna,
                'nama' => $seller->nama,
                'foto_profil' => $seller->foto_profil,
                'kelompok_tani' => $seller->kelompokTani?->nama_kelompok,
                'lokasi_umum' => $seller->lokasi_nama ?: ($seller->kelompokTani?->alamat ?: 'Banyumas, Jawa Tengah'),
                'no_telepon' => $seller->kontak_publik_aktif ? $seller->no_telepon : null,
            ],
            'produk' => $produkList,
        ];
    }

    /**
     * Ambil daftar produk milik anggota petani tertentu (untuk Flutter Produk Saya).
     */
    public function getProdukForUser(Pengguna $user, Request $request)
    {
        $query = Produk::with('komoditas')
            ->where('id_pengguna', $user->id_pengguna);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->query('q')) {
            $likeOp = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where('nama_produk', $likeOp, "%{$search}%");
        }

        return $query->orderByDesc('created_at')->get();
    }

    /**
     * Tambah produk baru oleh petani.
     */
    public function createProduk(Pengguna $user, array $data, ?UploadedFile $foto = null): Produk
    {
        $fotoUrl = null;
        if ($foto) {
            $fotoUrl = $this->cloudinaryService->upload($foto->getRealPath(), 'pawontani/produk');
        }

        try {
            return DB::transaction(function () use ($user, $data, $fotoUrl) {
                $produk = new Produk();
                $produk->id_pengguna = $user->id_pengguna;
                $produk->id_komoditas = $data['id_komoditas'] ?? null;
                $produk->nama_produk = $data['nama_produk'];
                $produk->deskripsi = $data['deskripsi'] ?? null;
                $produk->foto_produk = $fotoUrl;
                $produk->jenis_harga = $data['jenis_harga'] ?? 'tetap';
                $produk->satuan = strtolower($data['satuan'] ?? 'kg');
                $produk->stok_fisik = (float) ($data['stok_fisik'] ?? 0);
                $produk->stok_ditahan = 0;
                $produk->status = $data['status'] ?? 'aktif';

                if ($produk->jenis_harga === 'rentang') {
                    $min = (float) ($data['harga_min'] ?? 0);
                    $max = (float) ($data['harga_max'] ?? 0);
                    if ($min > $max) {
                        throw new InvalidArgumentException('Harga minimum tidak boleh melebihi harga maksimum.');
                    }
                    $produk->harga_min = $min;
                    $produk->harga_max = $max;
                    $produk->harga = $min;
                } else {
                    $produk->harga = (float) ($data['harga'] ?? 0);
                    $produk->harga_min = null;
                    $produk->harga_max = null;
                }

                $produk->save();

                return $produk->load('komoditas');
            });
        } catch (Throwable $e) {
            if ($fotoUrl) {
                try {
                    $this->cloudinaryService->delete($fotoUrl);
                } catch (Throwable) {}
            }
            throw $e;
        }
    }

    /**
     * Perbarui data produk oleh petani pemiliknya.
     */
    public function updateProduk(Pengguna $user, string $idProduk, array $data, ?UploadedFile $foto = null): Produk
    {
        $produk = Produk::where('id_produk', $idProduk)->firstOrFail();

        if ($produk->id_pengguna !== $user->id_pengguna) {
            throw new AccessDeniedHttpException('Anda hanya dapat mengubah produk milik sendiri.');
        }

        $newFotoUrl = null;
        if ($foto) {
            $newFotoUrl = $this->cloudinaryService->upload($foto->getRealPath(), 'pawontani/produk');
        }

        $oldFotoUrl = null;

        try {
            return DB::transaction(function () use ($produk, $data, $newFotoUrl, &$oldFotoUrl) {
                if (isset($data['nama_produk'])) {
                    $produk->nama_produk = $data['nama_produk'];
                }
                if (array_key_exists('id_komoditas', $data)) {
                    $produk->id_komoditas = $data['id_komoditas'];
                }
                if (array_key_exists('deskripsi', $data)) {
                    $produk->deskripsi = $data['deskripsi'];
                }
                if (isset($data['satuan'])) {
                    $produk->satuan = strtolower($data['satuan']);
                }
                if (isset($data['status'])) {
                    $produk->status = $data['status'];
                }
                if (isset($data['stok_fisik'])) {
                    $produk->stok_fisik = (float) $data['stok_fisik'];
                }

                if (isset($data['jenis_harga'])) {
                    $produk->jenis_harga = $data['jenis_harga'];
                    if ($produk->jenis_harga === 'rentang') {
                        $min = (float) ($data['harga_min'] ?? $produk->harga_min);
                        $max = (float) ($data['harga_max'] ?? $produk->harga_max);
                        if ($min > $max) {
                            throw new InvalidArgumentException('Harga minimum tidak boleh melebihi harga maksimum.');
                        }
                        $produk->harga_min = $min;
                        $produk->harga_max = $max;
                        $produk->harga = $min;
                    } else {
                        $produk->harga = (float) ($data['harga'] ?? $produk->harga);
                        $produk->harga_min = null;
                        $produk->harga_max = null;
                    }
                } elseif (isset($data['harga'])) {
                    $produk->harga = (float) $data['harga'];
                }

                if ($newFotoUrl) {
                    $oldFotoUrl = $produk->foto_produk;
                    $produk->foto_produk = $newFotoUrl;
                }

                $produk->save();

                return $produk->load('komoditas');
            });
        } catch (Throwable $e) {
            if ($newFotoUrl) {
                try {
                    $this->cloudinaryService->delete($newFotoUrl);
                } catch (Throwable) {}
            }
            throw $e;
        }
    }

    /**
     * Hapus / Arsipkan produk secara aman.
     */
    public function archiveOrDeleteProduk(Pengguna $user, string $idProduk): bool
    {
        $produk = Produk::where('id_produk', $idProduk)->firstOrFail();

        if ($produk->id_pengguna !== $user->id_pengguna) {
            throw new AccessDeniedHttpException('Anda hanya dapat mengarsipkan produk milik sendiri.');
        }

        // Jika produk memiliki komitmen transaksi aktif (stok ditahan > 0), tolak pengarsipan
        if ($produk->stok_ditahan > 0) {
            throw new InvalidArgumentException('Produk tidak dapat diarsipkan karena masih memiliki pesanan/stok yang sedang ditahan.');
        }

        return (bool) $produk->delete();
    }
}
