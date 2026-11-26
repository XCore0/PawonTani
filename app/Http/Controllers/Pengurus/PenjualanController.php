<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Models\Panen;
use App\Models\Pengguna;
use App\Models\TransaksiPenjualan;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PenjualanController extends Controller
{
    public function index(Request $request): View
    {
        $pengurus = $this->currentPengurus($request);

        abort_unless(
            Schema::hasTable('transaksi_penjualan') && Schema::hasColumn('transaksi_penjualan', 'id_kelompok'),
            503,
            'Jalankan php artisan migrate untuk mengaktifkan integrasi transaksi penjualan.'
        );

        $idKelompok = $pengurus->id_kelompok;
        $validated = $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $tanggalMulai = Carbon::parse($validated['tanggal_mulai'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $tanggalSelesai = Carbon::parse($validated['tanggal_selesai'] ?? now()->toDateString())->endOfDay();

        $baseQuery = TransaksiPenjualan::query()
            ->where('id_kelompok', $idKelompok)
            ->whereBetween('tanggal_transaksi', [$tanggalMulai->toDateString(), $tanggalSelesai->toDateString()])
            ->where('status_pembayaran', '!=', 'dibatalkan');

        $stats = [
            'transaksi' => (clone $baseQuery)->count(),
            'berat_kg' => (float) (clone $baseQuery)->where('satuan', 'kg')->sum('jumlah'),
            'total_penjualan' => (float) (clone $baseQuery)->sum('total_harga'),
        ];

        $query = (clone $baseQuery)->with([
            'panen:id_panen,kode_panen',
            'penjual:id_pengguna,nama',
            'pencatat:id_pengguna,nama',
            'komoditas:id_komoditas,nama_komoditas',
        ]);

        if ($search = trim((string) ($validated['q'] ?? ''))) {
            $like = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(function (Builder $builder) use ($search, $like) {
                $builder->where('id_penjualan', $like, "%{$search}%")
                    ->orWhere('kode_transaksi', $like, "%{$search}%")
                    ->orWhere('nama_pembeli', $like, "%{$search}%")
                    ->orWhere('nama_produk', $like, "%{$search}%")
                    ->orWhereHas('komoditas', fn (Builder $komoditas) => $komoditas->where('nama_komoditas', $like, "%{$search}%"));
            });
        }

        $penjualan = $query
            ->orderByDesc('tanggal_transaksi')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $panenTersedia = Panen::query()
            ->where('id_kelompok', $idKelompok)
            ->where('status', 'disetujui')
            ->with('pengguna:id_pengguna,nama')
            ->withSum([
                'transaksiPenjualan as total_terjual_kg' => fn (Builder $query) => $query
                    ->where('status_pembayaran', '!=', 'dibatalkan')
                    ->where('satuan', 'kg'),
            ], 'jumlah')
            ->orderByDesc('tanggal_panen')
            ->get()
            ->filter(fn (Panen $panen) => (float) $panen->berat_kg - (float) ($panen->total_terjual_kg ?? 0) > 0.009)
            ->values();

        return view('Pengurus.Content.Penjualan.Index', compact(
            'pengurus',
            'penjualan',
            'panenTersedia',
            'stats',
            'tanggalMulai',
            'tanggalSelesai'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $pengurus = $this->currentPengurus($request);

        abort_unless(
            Schema::hasTable('transaksi_penjualan') && Schema::hasColumn('transaksi_penjualan', 'id_panen'),
            503,
            'Jalankan php artisan migrate untuk mengaktifkan pencatatan penjualan hasil panen.'
        );

        $validated = $request->validate([
            'id_panen' => ['required', 'string', 'exists:panen,id_panen'],
            'tanggal_penjualan' => ['required', 'date', 'before_or_equal:today'],
            'nama_pembeli' => ['nullable', 'string', 'max:150'],
            'berat_kg' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'harga_per_kg' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ], [
            'id_panen.required' => 'Pilih hasil panen yang akan dijual.',
            'id_panen.exists' => 'Data panen tidak ditemukan.',
            'tanggal_penjualan.before_or_equal' => 'Tanggal penjualan tidak boleh melebihi hari ini.',
            'berat_kg.gt' => 'Berat yang dijual harus lebih dari 0 kg.',
            'harga_per_kg.gt' => 'Harga per kilogram harus lebih dari Rp0.',
        ]);

        $transaksi = DB::transaction(function () use ($validated, $pengurus): TransaksiPenjualan {
            $panen = Panen::query()
                ->where('id_panen', $validated['id_panen'])
                ->where('id_kelompok', $pengurus->id_kelompok)
                ->where('status', 'disetujui')
                ->lockForUpdate()
                ->firstOrFail();

            $beratSudahTerjual = (float) TransaksiPenjualan::query()
                ->where('id_panen', $panen->id_panen)
                ->where('status_pembayaran', '!=', 'dibatalkan')
                ->where('satuan', 'kg')
                ->sum('jumlah');

            $sisaBerat = max(0, (float) $panen->berat_kg - $beratSudahTerjual);
            $beratDijual = (float) $validated['berat_kg'];

            if ($beratDijual > $sisaBerat + 0.0001) {
                throw ValidationException::withMessages([
                    'berat_kg' => 'Berat yang dijual melebihi sisa hasil panen (' . number_format($sisaBerat, 2, ',', '.') . ' kg).',
                ]);
            }

            $hargaPerKg = (float) $validated['harga_per_kg'];

            return TransaksiPenjualan::create([
                // Penjual tetap anggota pemilik hasil panen; Pengurus dicatat sebagai pencatat transaksi.
                'id_pengguna' => $panen->id_pengguna,
                'id_kelompok' => $pengurus->id_kelompok,
                'id_panen' => $panen->id_panen,
                'id_pengurus' => $pengurus->id_pengguna,
                'id_produk' => null,
                'id_komoditas' => $panen->id_komoditas,
                'nama_produk' => $panen->komoditas_nama,
                'nama_pembeli' => $validated['nama_pembeli'] ?? 'Pelanggan',
                'jumlah' => $beratDijual,
                'satuan' => 'kg',
                'harga_satuan' => $hargaPerKg,
                'total_harga' => round($hargaPerKg * $beratDijual, 2),
                'total_dibayar' => 0,
                'status_pembayaran' => 'belum_dibayar',
                'status_penyerahan' => 'belum_diserahkan',
                'tanggal_transaksi' => $validated['tanggal_penjualan'],
                'metode_pembayaran' => 'transfer',
                'catatan' => $validated['catatan'] ?? null,
            ]);
        });

        AuditLogger::log(
            'penjualan.dicatat',
            "Mencatat penjualan {$transaksi->nama_produk} sebanyak {$transaksi->jumlah} kg senilai Rp" . number_format($transaksi->total_harga, 0, ',', '.') . '.',
            'Penjualan',
            $transaksi->id_penjualan,
            $pengurus->id_kelompok,
            $request
        );

        return redirect()->route('pengurus.penjualan')->with('success', 'Transaksi penjualan berhasil dicatat.');
    }

    private function currentPengurus(Request $request): Pengguna
    {
        /** @var Pengguna|null $pengurus */
        $pengurus = $request->user();

        abort_unless(
            $pengurus
                && $pengurus->role === 'Pengurus'
                && $pengurus->status === 'Aktif'
                && filled($pengurus->id_kelompok),
            403,
            'Akses hanya tersedia untuk Pengurus aktif yang memiliki kelompok tani.'
        );

        abort_unless($pengurus->kelompokTani?->status === 'Aktif', 403, 'Kelompok tani tidak aktif.');

        return $pengurus;
    }
}
