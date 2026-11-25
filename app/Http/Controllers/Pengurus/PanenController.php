<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Models\Komoditas;
use App\Models\Panen;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PanenController extends Controller
{
    /**
     * Menampilkan daftar panen anggota dalam kelompok tani Pengurus.
     */
    public function index(Request $request): View
    {
        /** @var \App\Models\Pengguna $pengurus */
        $pengurus = $request->user();

        abort_unless(
            $pengurus && $pengurus->status === 'Aktif' && $pengurus->role === 'Pengurus' && !empty($pengurus->id_kelompok),
            403,
            'Akses ditolak. Anda harus menjadi Pengurus aktif yang terdaftar dalam kelompok tani.'
        );

        $kelompok = $pengurus->kelompokTani;

        // Base query scoped ke kelompok tani pengurus (BOLA/IDOR protection)
        $baseQuery = Panen::where('id_kelompok', $pengurus->id_kelompok);

        // Summary metrics
        $metricsBase = clone $baseQuery;
        $totalTonPadi = (float) (clone $metricsBase)->where('komoditas_nama', 'ILIKE', '%padi%')->sum('berat_kg') / 1000.0;
        $totalTonJagung = (float) (clone $metricsBase)->where('komoditas_nama', 'ILIKE', '%jagung%')->sum('berat_kg') / 1000.0;

        $stats = [
            'total' => (clone $metricsBase)->count(),
            'menunggu' => (clone $metricsBase)->where('status', 'menunggu')->count(),
            'disetujui' => (clone $metricsBase)->where('status', 'disetujui')->count(),
            'ditolak' => (clone $metricsBase)->where('status', 'ditolak')->count(),
            'total_berat_kg' => (float) (clone $metricsBase)->sum('berat_kg'),
            'total_berat_ton' => round((float) (clone $metricsBase)->sum('berat_kg') / 1000.0, 2),
            'total_ton_padi' => round($totalTonPadi, 2),
            'total_ton_jagung' => round($totalTonJagung, 2),
        ];

        // Query list panen
        $query = (clone $baseQuery)->with([
            'pengguna:id_pengguna,nama,no_telepon,foto_profil',
            'lahan:id_lahan,nama_lahan,luas,alamat_lahan',
            'komoditas:id_komoditas,nama_komoditas',
        ]);

        // Filter status
        if ($status = $request->query('status')) {
            $query->where('status', strtolower($status));
        }

        // Filter komoditas
        if ($komoditas = $request->query('komoditas')) {
            $query->where('komoditas_nama', 'ILIKE', "%{$komoditas}%");
        }

        // Pencarian teks
        if ($search = trim((string) $request->query('q'))) {
            $like = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(function ($q) use ($search, $like) {
                $q->where('kode_panen', $like, "%{$search}%")
                  ->orWhere('varietas', $like, "%{$search}%")
                  ->orWhere('komoditas_nama', $like, "%{$search}%")
                  ->orWhere('jenis_panen', $like, "%{$search}%")
                  ->orWhere('catatan', $like, "%{$search}%")
                  ->orWhereHas('pengguna', fn ($uq) => $uq->where('nama', $like, "%{$search}%"))
                  ->orWhereHas('lahan', fn ($lq) => $lq->where('nama_lahan', $like, "%{$search}%"));
            });
        }

        $panens = $query->orderByDesc('tanggal_panen')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $komoditasList = Komoditas::orderBy('nama_komoditas')->get(['id_komoditas', 'nama_komoditas']);

        return view('Pengurus.Content.Panen.Index', compact('panens', 'stats', 'kelompok', 'komoditasList'));
    }

    /**
     * Detail catatan panen.
     */
    public function show(Request $request, string $id): View
    {
        /** @var \App\Models\Pengguna $pengurus */
        $pengurus = $request->user();

        abort_unless(
            $pengurus && $pengurus->status === 'Aktif' && $pengurus->role === 'Pengurus' && !empty($pengurus->id_kelompok),
            403
        );

        $panen = Panen::with([
            'pengguna',
            'lahan',
            'komoditas',
            'kelompokTani',
        ])
            ->where('id_kelompok', $pengurus->id_kelompok)
            ->where('id_panen', $id)
            ->firstOrFail();

        return view('Pengurus.Content.Panen.Show', compact('panen'));
    }

    /**
     * Verifikasi catatan panen (Disetujui / Ditolak).
     */
    public function verifikasi(Request $request, string $id): RedirectResponse
    {
        /** @var \App\Models\Pengguna $pengurus */
        $pengurus = $request->user();

        abort_unless(
            $pengurus && $pengurus->status === 'Aktif' && $pengurus->role === 'Pengurus' && !empty($pengurus->id_kelompok),
            403
        );

        $request->validate([
            'status' => ['required', 'in:disetujui,ditolak,menunggu'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:500'],
        ]);

        $panen = Panen::where('id_kelompok', $pengurus->id_kelompok)
            ->where('id_panen', $id)
            ->firstOrFail();

        $statusLama = $panen->status;
        $statusBaru = $request->input('status');

        $panen->status = $statusBaru;
        $panen->diverifikasi_oleh = $pengurus->nama;
        $panen->tanggal_verifikasi = now();
        $panen->catatan_verifikasi = $request->input('catatan_verifikasi');
        $panen->save();

        // Dispatch notifikasi hasil verifikasi panen setelah commit
        $notificationService = app(\App\Services\NotificationService::class);
        if ($statusBaru === 'disetujui') {
            $notificationService->afterCommit(function () use ($notificationService, $panen, $pengurus) {
                $notificationService->notifyPanenDisetujui($panen, $pengurus, $panen->catatan_verifikasi);
            });
        } elseif ($statusBaru === 'ditolak') {
            $notificationService->afterCommit(function () use ($notificationService, $panen, $pengurus) {
                $notificationService->notifyPanenDitolak($panen, $pengurus, $panen->catatan_verifikasi);
            });
        }

        AuditLogger::log(
            'panen.diverifikasi',
            "Memverifikasi hasil panen {$panen->kode_panen} dari '{$statusLama}' menjadi '{$statusBaru}'.",
            'Panen',
            $panen->id_panen,
            $pengurus->id_kelompok,
            $request
        );

        $pesan = $statusBaru === 'disetujui'
            ? "Hasil panen ({$panen->kode_panen}) berhasil disetujui."
            : ($statusBaru === 'ditolak'
                ? "Hasil panen ({$panen->kode_panen}) telah ditolak."
                : "Status hasil panen dikembalikan ke menunggu verifikasi.");

        return redirect()->back()->with('success', $pesan);
    }
}
