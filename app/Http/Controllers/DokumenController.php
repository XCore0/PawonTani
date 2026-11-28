<?php

namespace App\Http\Controllers;

use App\Models\AksesDokumen;
use App\Models\DokumenAnggota;
use App\Models\DokumenLahan;
use App\Models\Lahan;
use App\Models\LogAksesDokumen;
use App\Models\Pengguna;
use App\Services\AuditLogger;
use App\Services\DocumentStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DokumenController extends Controller
{
    public function __construct(private readonly DocumentStorageService $documents) {}

    public function previewIdentity(Request $request, string $idPengguna, string $jenis)
    {
        $member = Pengguna::where('role', 'Anggota')->where('id_pengguna', $idPengguna)->firstOrFail();
        $jenis = strtoupper($jenis);
        abort_unless(in_array($jenis, ['KTP', 'KK'], true), 404);

        $document = DokumenAnggota::where('id_pengguna', $member->id_pengguna)->where('jenis', $jenis)->firstOrFail();
        $permission = $this->authorizeMemberDocument($request, $member, $document);

        $this->recordAccessLog($request, $permission, 'identitas', $document->id, 'preview');

        return $this->inlineDocument(
            $document->file_path,
            $document->mime_type,
            $document->original_name,
            'DokumenAnggota',
            $document->id,
            $member->id_kelompok,
            $request
        );
    }

    public function downloadIdentity(Request $request, string $idPengguna, string $jenis): StreamedResponse
    {
        $member = Pengguna::where('role', 'Anggota')->where('id_pengguna', $idPengguna)->firstOrFail();
        $jenis = strtoupper($jenis);
        abort_unless(in_array($jenis, ['KTP', 'KK'], true), 404);

        $document = DokumenAnggota::where('id_pengguna', $member->id_pengguna)->where('jenis', $jenis)->firstOrFail();
        $permission = $this->authorizeMemberDocument($request, $member, $document);

        abort_unless(Storage::disk('documents')->exists($document->file_path), 404, 'File dokumen tidak ditemukan.');

        $this->recordAccessLog($request, $permission, 'identitas', $document->id, 'download');

        AuditLogger::log('dokumen.download', 'Mengunduh ' . $document->jenis . ' milik ' . $member->nama, 'DokumenAnggota', $document->id, $member->id_kelompok, $request);

        return Storage::disk('documents')->download(
            $document->file_path,
            $this->safeDownloadName($document->original_name),
            [
                'Content-Type' => $document->mime_type,
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store, max-age=0',
            ]
        );
    }

    public function previewSppt(Request $request, string $idDokumen)
    {
        $document = DokumenLahan::with('lahan.pengguna')->findOrFail($idDokumen);
        $land = $document->lahan;
        abort_unless($land, 404);
        $permission = $this->authorizeLandDocument($request, $land, $document);

        $this->recordAccessLog($request, $permission, 'sppt', $document->id, 'preview');

        return $this->inlineDocument(
            $document->file_path,
            $document->mime_type,
            $document->original_name,
            'DokumenLahan',
            $document->id,
            $land->id_kelompok,
            $request
        );
    }

    public function downloadSppt(Request $request, string $idDokumen): StreamedResponse
    {
        $document = DokumenLahan::with('lahan')->findOrFail($idDokumen);
        abort_unless($document->lahan, 404);
        $permission = $this->authorizeLandDocument($request, $document->lahan, $document);

        $this->recordAccessLog($request, $permission, 'sppt', $document->id, 'download');

        return $this->downloadSpptDocument($request, $document);
    }

    public function uploadSpptPengurus(Request $request, string $idLahan): RedirectResponse
    {
        $pengurus = $request->user();
        abort_unless($pengurus && $pengurus->role === 'Pengurus' && $pengurus->status === 'Aktif' && $pengurus->id_kelompok, 403);
        $land = Lahan::where('id_lahan', $idLahan)->where('id_kelompok', $pengurus->id_kelompok)->firstOrFail();

        $validated = $request->validate([
            'dokumen_sppt' => ['required', 'array', 'min:1', 'max:10'],
            'dokumen_sppt.*' => ['required', 'file', 'mimes:pdf,jpg,jpeg', 'max:5120'],
        ], [
            'dokumen_sppt.required' => 'Pilih minimal satu file SPPT.',
            'dokumen_sppt.max' => 'Maksimal 10 file dapat diunggah sekaligus.',
            'dokumen_sppt.*.mimes' => 'Format file tidak didukung. Gunakan PDF atau JPG.',
            'dokumen_sppt.*.max' => 'Setiap file harus berukuran maksimal 5 MB.',
        ]);

        $saved = 0;
        try {
            foreach ($validated['dokumen_sppt'] as $file) {
                $this->documents->validateUpload($file);
            }

            foreach ($validated['dokumen_sppt'] as $file) {
                $document = $this->documents->storeSppt($land, $file, $pengurus);
                AuditLogger::log('dokumen.upload', 'Mengunggah SPPT tambahan untuk lahan ' . $land->nama_lahan, 'DokumenLahan', $document->id, $land->id_kelompok, $request);
                $saved++;
            }
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['dokumen_sppt' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('pengurus.lahan.show', $land->id_lahan)
            ->with('success', $saved . ' dokumen SPPT berhasil ditambahkan.');
    }

    /* ============================================================
       API ANGGOTA PETANI (MOBILE)
       ============================================================ */

    public function apiIndex(Request $request): JsonResponse
    {
        $user = $request->user();
        $identitas = DokumenAnggota::where('id_pengguna', $user->id_pengguna)
            ->get(['id', 'jenis', 'original_name', 'mime_type', 'size_bytes', 'updated_at'])
            ->keyBy('jenis');

        $lands = Lahan::where('id_pengguna', $user->id_pengguna)
            ->where('id_kelompok', $user->id_kelompok)
            ->with('dokumenSppt:id,id_lahan,original_name,mime_type,size_bytes,updated_at')
            ->get(['id_lahan', 'nama_lahan', 'status_penguasaan']);

        $activePermissions = AksesDokumen::where('id_pengguna', $user->id_pengguna)
            ->where('status_akses', 'aktif')
            ->where(function ($q) {
                $q->whereNull('waktu_berakhir')->orWhere('waktu_berakhir', '>=', now());
            })
            ->with('kelompokTani:id_kelompok,nama_kelompok')
            ->latest('waktu_persetujuan')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Dokumen anggota berhasil dimuat.',
            'data' => [
                'identitas' => [
                    'ktp' => $identitas->get('KTP'),
                    'kk' => $identitas->get('KK'),
                ],
                'lahan' => $lands->map(function ($lahan) {
                    return [
                        'id_lahan' => $lahan->id_lahan,
                        'nama_lahan' => $lahan->nama_lahan,
                        'status_penguasaan' => $lahan->status_penguasaan,
                        'sppt_documents' => $lahan->dokumenSppt,
                    ];
                }),
                'izin_aktif' => $activePermissions,
            ],
        ]);
    }

    public function apiUploadIdentity(Request $request, string $jenis): JsonResponse
    {
        $jenis = strtoupper($jenis);
        abort_unless(in_array($jenis, ['KTP', 'KK'], true), 404);

        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg', 'max:5120'],
        ], [
            'file.required' => 'Pilih file dokumen ' . $jenis . '.',
            'file.mimes' => 'Format file tidak didukung. Gunakan PDF atau JPG.',
            'file.max' => 'Ukuran file dokumen maksimal 5 MB.',
        ]);

        $user = $request->user();
        try {
            $document = $this->documents->storeIdentity($user, $jenis, $request->file('file'), $user);
        } catch (\RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        AuditLogger::log('dokumen.upload', 'Mengunggah dokumen ' . $jenis . ' mandiri.', 'DokumenAnggota', $document->id, $user->id_kelompok, $request);

        return response()->json([
            'success' => true,
            'message' => 'Dokumen ' . $jenis . ' berhasil diunggah dan tersimpan privat.',
            'data' => [
                'id' => $document->id,
                'jenis' => $document->jenis,
                'original_name' => $document->original_name,
                'mime_type' => $document->mime_type,
                'size_bytes' => $document->size_bytes,
                'updated_at' => $document->updated_at,
            ],
        ]);
    }

    public function apiUploadSppt(Request $request, string $idLahan): JsonResponse
    {
        $user = $request->user();
        $land = Lahan::where('id_lahan', $idLahan)
            ->where('id_pengguna', $user->id_pengguna)
            ->where('id_kelompok', $user->id_kelompok)
            ->firstOrFail();

        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg', 'max:5120'],
        ], [
            'file.required' => 'Pilih file dokumen SPPT.',
            'file.mimes' => 'Format file tidak didukung. Gunakan PDF atau JPG.',
            'file.max' => 'Ukuran file dokumen maksimal 5 MB.',
        ]);

        try {
            $document = $this->documents->storeSppt($land, $request->file('file'), $user);
        } catch (\RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        AuditLogger::log('dokumen.upload', 'Mengunggah dokumen SPPT lahan ' . $land->nama_lahan . ' mandiri.', 'DokumenLahan', $document->id, $land->id_kelompok, $request);

        return response()->json([
            'success' => true,
            'message' => 'Dokumen SPPT berhasil ditambahkan.',
            'data' => [
                'id' => (string) $document->id,
                'id_lahan' => $land->id_lahan,
                'original_name' => $document->original_name,
                'mime_type' => $document->mime_type,
                'size_bytes' => $document->size_bytes,
                'updated_at' => $document->updated_at,
            ],
        ]);
    }

    public function apiDeleteSppt(Request $request, string $idDokumen): JsonResponse
    {
        $user = $request->user();
        $document = DokumenLahan::where('id', $idDokumen)->with('lahan')->firstOrFail();
        $land = $document->lahan;

        abort_unless(
            $land && $land->id_pengguna === $user->id_pengguna && $land->id_kelompok === $user->id_kelompok,
            403,
            'Anda tidak memiliki akses untuk menghapus dokumen SPPT ini.'
        );

        $this->documents->deleteSppt($document);

        AuditLogger::log('dokumen.delete', 'Menghapus dokumen SPPT lahan ' . $land->nama_lahan, 'DokumenLahan', $idDokumen, $land->id_kelompok, $request);

        return response()->json([
            'success' => true,
            'message' => 'Dokumen SPPT berhasil dihapus.',
            'data' => ['id' => (string) $idDokumen, 'id_lahan' => $land->id_lahan],
        ]);
    }

    public function apiDownloadIdentity(Request $request, string $jenis): StreamedResponse
    {
        return $this->downloadIdentity($request, $request->user()->id_pengguna, $jenis);
    }

    public function apiDownloadSppt(Request $request, string $idLahan): StreamedResponse
    {
        $land = Lahan::where('id_lahan', $idLahan)->firstOrFail();
        $document = DokumenLahan::where('id_lahan', $land->id_lahan)->latest()->firstOrFail();
        $this->authorizeLandDocument($request, $land, $document);

        return $this->downloadSpptDocument($request, $document);
    }

    public function apiDownloadSpptDocument(Request $request, string $idDokumen): StreamedResponse
    {
        $document = DokumenLahan::with('lahan')->findOrFail($idDokumen);
        abort_unless($document->lahan, 404);
        $this->authorizeLandDocument($request, $document->lahan, $document);

        return $this->downloadSpptDocument($request, $document);
    }

    /* ============================================================
       API SISTEM PERSETUJUAN & IZIN DOKUMEN (PETANI MEMBERIKAN IZIN)
       ============================================================ */

    /**
     * Mengambil riwayat dan daftar izin akses dokumen milik petani aktif.
     */
    public function apiGetPermissions(Request $request): JsonResponse
    {
        $user = $request->user();
        $permissions = AksesDokumen::where('id_pengguna', $user->id_pengguna)
            ->with(['kelompokTani:id_kelompok,nama_kelompok', 'logs'])
            ->latest('waktu_persetujuan')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar izin dokumen berhasil diambil.',
            'data' => $permissions,
        ]);
    }

    /**
     * Petani memberikan izin akses dokumen kepada Pengurus kelompok taninya.
     */
    public function apiGrantPermission(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->role === 'Anggota' && $user->id_kelompok, 403, 'Hanya anggota dengan kelompok terdaftar yang dapat memberikan izin.');

        // Normalisasi input jika client mengirimkan 'jenis_dokumen' atau 'tipe_dokumen'
        $rawTipe = strtolower((string) ($request->input('tipe_dokumen') ?? $request->input('jenis_dokumen') ?? 'semua'));
        if (in_array($rawTipe, ['ktp', 'kk'], true)) {
            $rawTipe = 'identitas';
        }

        $rawTujuan = (string) ($request->input('tujuan_penggunaan') ?? $request->input('tujuan_akses') ?? 'Administrasi Kelompok Tani');
        $rawDurasi = (int) ($request->input('durasi_hari') ?? $request->input('masa_berlaku_hari') ?? 30);

        $request->merge([
            'tipe_dokumen' => $rawTipe,
            'tujuan_penggunaan' => $rawTujuan,
            'durasi_hari' => $rawDurasi,
        ]);

        $validated = $request->validate([
            'tipe_dokumen' => ['required', 'in:identitas,sppt,semua'],
            'id_dokumen' => ['nullable', 'integer'],
            'tujuan_penggunaan' => ['required', 'string', 'max:255'],
            'durasi_hari' => ['nullable', 'integer', 'min:1', 'max:365'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ]);

        $durasi = (int) ($validated['durasi_hari'] ?? 30);
        $waktuBerakhir = now()->addDays($durasi);

        // Jika memilih 'semua', berikan izin untuk identitas dan sppt
        $tipeList = $validated['tipe_dokumen'] === 'semua' ? ['identitas', 'sppt'] : [$validated['tipe_dokumen']];
        $createdPermissions = [];

        foreach ($tipeList as $tipe) {
            $perm = AksesDokumen::create([
                'id_pengguna' => $user->id_pengguna,
                'tipe_dokumen' => $tipe,
                'id_dokumen' => $validated['id_dokumen'] ?? 0, // 0 = seluruh dokumen kategori ini
                'id_kelompok' => $user->id_kelompok,
                'tujuan_penggunaan' => $validated['tujuan_penggunaan'],
                'waktu_persetujuan' => now(),
                'waktu_berakhir' => $waktuBerakhir,
                'status_akses' => 'aktif',
                'keterangan' => $validated['keterangan'] ?? null,
            ]);
            $createdPermissions[] = $perm;
        }

        AuditLogger::log(
            'dokumen.grant_permission',
            'Memberikan izin akses dokumen ' . $validated['tipe_dokumen'] . ' kepada pengurus kelompok selama ' . $durasi . ' hari.',
            'AksesDokumen',
            $createdPermissions[0]->id,
            $user->id_kelompok,
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Izin akses dokumen berhasil diberikan kepada pengurus kelompok tani.',
            'data' => $createdPermissions,
        ]);
    }

    /**
     * Petani mencabut izin akses dokumen seketika (instant revocation).
     */
    public function apiRevokePermission(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $permission = AksesDokumen::where('id', $id)
            ->where('id_pengguna', $user->id_pengguna)
            ->firstOrFail();

        $permission->update([
            'status_akses' => 'dicabut',
            'waktu_berakhir' => now(),
        ]);

        AuditLogger::log(
            'dokumen.revoke_permission',
            'Mencabut izin akses dokumen ID ' . $permission->id,
            'AksesDokumen',
            $permission->id,
            $user->id_kelompok,
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Izin akses dokumen telah berhasil dicabut. Pengurus tidak lagi dapat membuka dokumen ini.',
            'data' => $permission,
        ]);
    }

    /**
     * Melihat riwayat akses oleh pengurus terhadap dokumen milik petani.
     */
    public function apiGetAccessLogs(Request $request): JsonResponse
    {
        $user = $request->user();
        $logs = LogAksesDokumen::whereHas('aksesDokumen', fn ($q) => $q->where('id_pengguna', $user->id_pengguna))
            ->with(['pengguna:id_pengguna,nama,role'])
            ->latest('waktu_akses')
            ->take(50)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat akses dokumen berhasil diambil.',
            'data' => $logs,
        ]);
    }

    /* ============================================================
       FUNGSI NONAKTIF SESUAI KETENTUAN PRIVASI PAWONTANI
       ============================================================ */

    public function downloadMemberArchive(Request $request, string $idPengguna): BinaryFileResponse
    {
        abort(403, 'Fitur pengunduhan arsip massal seluruh dokumen anggota telah dinonaktifkan demi perlindungan privasi data pribadi petani.');
    }

    /* ============================================================
       HELPER INTERNAL & OTORISASI
       ============================================================ */

    private function downloadSpptDocument(Request $request, DokumenLahan $document): StreamedResponse
    {
        abort_unless(Storage::disk('documents')->exists($document->file_path), 404, 'File SPPT tidak ditemukan.');
        $land = $document->lahan;
        AuditLogger::log('dokumen.download', 'Mengunduh dokumen SPPT untuk lahan ' . ($land?->nama_lahan ?? '-'), 'DokumenLahan', $document->id, $land?->id_kelompok, $request);

        return Storage::disk('documents')->download(
            $document->file_path,
            $this->safeDownloadName($document->original_name),
            [
                'Content-Type' => $document->mime_type,
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store, max-age=0',
            ]
        );
    }

    /**
     * Memeriksa otorisasi dokumen identitas (KTP/KK).
     * Mengembalikan model AksesDokumen jika diakses oleh Pengurus berizin, atau null jika pemilik sendiri.
     */
    private function authorizeMemberDocument(Request $request, Pengguna $member, ?DokumenAnggota $document = null): ?AksesDokumen
    {
        $actor = $request->user();
        abort_unless($actor, 401);

        // Administrator dan PPL tidak diizinkan membuka dokumen pribadi anggota
        if (in_array($actor->role, ['Admin', 'PPL'], true)) {
            abort(403, 'Akses langsung terhadap dokumen pribadi anggota tidak diizinkan untuk peran Administrator demi perlindungan privasi data petani.');
        }

        // Pengurus diizinkan melihat atau mengunduh KTP & KK anggota kelompoknya sendiri
        if ($actor->role === 'Pengurus') {
            abort_unless($actor->status === 'Aktif' && $actor->id_kelompok && $actor->id_kelompok === $member->id_kelompok, 403, 'Anda hanya dapat mengakses dokumen anggota di kelompok tani Anda.');
            return null;
        }

        // Anggota pemilik dokumen selalu memiliki akses
        if ($actor->role === 'Anggota' && $actor->id_pengguna === $member->id_pengguna) {
            abort_unless($actor->status === 'Aktif' && $actor->anggota?->status_keanggotaan === 'Aktif', 403, 'Akun anggota Anda sedang tidak aktif.');
            return null;
        }

        abort(403, 'Anda tidak memiliki hak akses terhadap dokumen ini.');
    }

    /**
     * Memeriksa otorisasi dokumen lahan (SPPT).
     * Mengembalikan model AksesDokumen jika diakses oleh Pengurus berizin, atau null jika pemilik sendiri.
     */
    private function authorizeLandDocument(Request $request, Lahan $land, ?DokumenLahan $document = null): ?AksesDokumen
    {
        $actor = $request->user();
        abort_unless($actor, 401);

        // Administrator dan PPL tidak diizinkan membuka dokumen SPPT anggota
        if (in_array($actor->role, ['Admin', 'PPL'], true)) {
            abort(403, 'Akses langsung terhadap dokumen SPPT tidak diizinkan untuk peran Administrator demi perlindungan privasi data petani.');
        }

        // Anggota pemilik lahan selalu memiliki akses
        if ($actor->role === 'Anggota' && $actor->id_pengguna === $land->id_pengguna) {
            abort_unless($actor->status === 'Aktif' && $actor->anggota?->status_keanggotaan === 'Aktif', 403, 'Akun anggota Anda sedang tidak aktif.');
            return null;
        }

        // Pengurus:
        if ($actor->role === 'Pengurus') {
            abort_unless($actor->status === 'Aktif' && $actor->id_kelompok && $actor->id_kelompok === $land->id_kelompok, 403, 'Pengurus hanya dapat mengakses lahan pada kelompoknya sendiri.');

            // Jika status verifikasi masih 'menunggu_verifikasi' atau 'menunggu', Pengurus diizinkan preview untuk keperluan verifikasi lahan
            $isReviewing = in_array($land->status_verifikasi, ['menunggu_verifikasi', 'menunggu'], true);

            $activePermission = AksesDokumen::where('id_pengguna', $land->id_pengguna)
                ->where('id_kelompok', $actor->id_kelompok)
                ->where('tipe_dokumen', 'sppt')
                ->where(function ($q) use ($document) {
                    if ($document) {
                        $q->where('id_dokumen', $document->id)->orWhere('id_dokumen', 0);
                    }
                })
                ->where('status_akses', 'aktif')
                ->where(function ($q) {
                    $q->whereNull('waktu_berakhir')->orWhere('waktu_berakhir', '>=', now());
                })
                ->first();

            if (!$activePermission && !$isReviewing) {
                abort(403, 'Akses dokumen SPPT belum diberikan atau telah dicabut oleh pemilik lahan setelah proses peninjauan selesai. Silakan minta persetujuan izin kepada petani.');
            }

            return $activePermission;
        }

        abort(403, 'Anda tidak memiliki hak akses terhadap dokumen lahan ini.');
    }

    private function recordAccessLog(Request $request, ?AksesDokumen $permission, string $tipeDokumen, int|string $idDokumen, string $activity): void
    {
        $actor = $request->user();
        if (!$actor || $actor->role === 'Anggota') {
            // Akses pemilik sendiri tidak perlu dicatat sebagai log audit pengurus
            return;
        }

        try {
            LogAksesDokumen::create([
                'id_akses_dokumen' => $permission?->id,
                'id_pengguna' => $actor->id_pengguna,
                'tipe_dokumen' => $tipeDokumen,
                'id_dokumen' => (int) $idDokumen,
                'jenis_aktivitas' => $activity,
                'waktu_akses' => now(),
                'keterangan' => 'Aktivitas ' . $activity . ' oleh Pengurus ' . $actor->nama . ' (IP: ' . $request->ip() . ')',
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function inlineDocument(string $path, string $mime, ?string $originalName, string $subjectType, int|string $subjectId, ?string $groupId, Request $request)
    {
        abort_unless(in_array($mime, ['application/pdf', 'image/jpeg'], true), 404);
        abort_unless(Storage::disk('documents')->exists($path), 404, 'File dokumen tidak ditemukan.');
        $absolutePath = Storage::disk('documents')->path($path);
        abort_unless(is_file($absolutePath), 404);

        AuditLogger::log('dokumen.preview', 'Melihat dokumen pribadi melalui tampilan PawonTani.', $subjectType, $subjectId, $groupId, $request);

        return response()->file($absolutePath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $this->safeDownloadName($originalName) . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    private function safeDownloadName(?string $name): string
    {
        $name = basename((string) $name);
        return preg_replace('/[^A-Za-z0-9._-]/', '_', $name) ?: 'dokumen';
    }
}
