<?php

namespace App\Http\Controllers;

use App\Models\DokumenAnggota;
use App\Models\DokumenLahan;
use App\Models\Lahan;
use App\Models\Pengguna;
use App\Services\AuditLogger;
use App\Services\DocumentStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DokumenController extends Controller
{
    public function __construct(private readonly DocumentStorageService $documents) {}


    public function previewIdentity(Request $request, string $idPengguna, string $jenis)
    {
        $member = Pengguna::where('role', 'Anggota')->where('id_pengguna', $idPengguna)->firstOrFail();
        $this->authorizeMemberDocument($request, $member);
        $jenis = strtoupper($jenis);
        abort_unless(in_array($jenis, ['KTP', 'KK'], true), 404);

        $document = DokumenAnggota::where('id_pengguna', $member->id_pengguna)->where('jenis', $jenis)->firstOrFail();
        return $this->inlineDocument($document->file_path, $document->mime_type, $document->original_name, 'DokumenAnggota', $document->id, $member->id_kelompok, $request);
    }

    public function previewSppt(Request $request, string $idDokumen)
    {
        $document = DokumenLahan::with('lahan.pengguna')->findOrFail($idDokumen);
        $land = $document->lahan;
        abort_unless($land, 404);
        $this->authorizeLandDocument($request, $land);

        return $this->inlineDocument($document->file_path, $document->mime_type, $document->original_name, 'DokumenLahan', $document->id, $land->id_kelompok, $request);
    }

    public function downloadSppt(Request $request, string $idDokumen): StreamedResponse
    {
        $document = DokumenLahan::with('lahan')->findOrFail($idDokumen);
        abort_unless($document->lahan, 404);
        $this->authorizeLandDocument($request, $document->lahan);

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
            // Periksa seluruh file sebelum menyimpan file pertama agar format yang salah
            // tidak meninggalkan upload setengah jalan.
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
            ->with('success', $saved . ' dokumen SPPT berhasil ditambahkan. Dokumen lama tetap tersimpan.');
    }

    public function apiIndex(Request $request): JsonResponse
    {
        $user = $request->user();
        $identitas = DokumenAnggota::where('id_pengguna', $user->id_pengguna)
            ->get(['jenis', 'original_name', 'mime_type', 'size_bytes', 'updated_at'])
            ->keyBy('jenis');
        $lands = Lahan::where('id_pengguna', $user->id_pengguna)
            ->where('id_kelompok', $user->id_kelompok)
            ->with('dokumenSppt:id,id_lahan,original_name,mime_type,size_bytes,updated_at')
            ->get(['id_lahan', 'nama_lahan']);

        return response()->json([
            'success' => true,
            'data' => [
                'identitas' => collect(['KTP', 'KK'])->map(fn ($type) => [
                    'jenis' => $type,
                    'tersedia' => $identitas->has($type),
                    'dokumen' => $identitas->get($type),
                    'download_url' => $identitas->has($type) ? route('api.v1.documents.identity.download', ['jenis' => $type]) : null,
                ]),
                'lahan' => $lands->map(fn ($land) => [
                    'id_lahan' => $land->id_lahan,
                    'nama_lahan' => $land->nama_lahan,
                    'sppt_tersedia' => $land->dokumenSppt->isNotEmpty(),
                    // Field `sppt` dan URL lama dipertahankan untuk kompatibilitas Flutter versi sebelumnya.
                    'sppt' => $land->dokumenSppt->first(),
                    'sppt_download_url' => $land->dokumenSppt->isNotEmpty()
                        ? route('api.v1.documents.sppt.download', ['idLahan' => $land->id_lahan])
                        : null,
                    'sppt_documents' => $land->dokumenSppt->map(fn ($document) => [
                        'id' => $document->id,
                        'original_name' => $document->original_name,
                        'mime_type' => $document->mime_type,
                        'size_bytes' => $document->size_bytes,
                        'updated_at' => $document->updated_at,
                        'download_url' => route('api.v1.documents.sppt.document.download', ['idDokumen' => $document->id]),
                    ])->values(),
                ]),
            ],
        ]);
    }

    public function apiUploadIdentity(Request $request, string $jenis): JsonResponse
    {
        $user = $request->user();
        $jenis = strtoupper($jenis);
        abort_unless(in_array($jenis, ['KTP', 'KK'], true), 404);
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg', 'max:5120'],
        ], [
            'file.mimes' => 'Format file tidak didukung. Gunakan PDF atau JPG.',
            'file.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        try {
            $document = $this->documents->storeIdentity($user, $jenis, $validated['file'], $user);
        } catch (\RuntimeException $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        }
        AuditLogger::log('dokumen.upload', 'Anggota mengunggah atau mengganti dokumen ' . $jenis, 'DokumenAnggota', $document->id, $user->id_kelompok, $request);

        return response()->json(['success' => true, 'message' => 'Dokumen berhasil disimpan.', 'data' => [
            'jenis' => $document->jenis,
            'original_name' => $document->original_name,
            'size_bytes' => $document->size_bytes,
        ]]);
    }

    public function apiUploadSppt(Request $request, string $idLahan): JsonResponse
    {
        $user = $request->user();
        $land = Lahan::where('id_lahan', $idLahan)
            ->where('id_pengguna', $user->id_pengguna)
            ->where('id_kelompok', $user->id_kelompok)
            ->firstOrFail();

        // Mendukung format lama `file` sekaligus format multi-upload `files[]`.
        $validated = $request->validate([
            'files' => ['sometimes', 'required', 'array', 'min:1', 'max:10'],
            'files.*' => ['required', 'file', 'mimes:pdf,jpg,jpeg', 'max:5120'],
            'file' => ['required_without:files', 'file', 'mimes:pdf,jpg,jpeg', 'max:5120'],
        ], [
            'files.max' => 'Maksimal 10 file dapat diunggah sekaligus.',
            'files.*.mimes' => 'Format file tidak didukung. Gunakan PDF atau JPG.',
            'files.*.max' => 'Setiap file harus berukuran maksimal 5 MB.',
            'file.mimes' => 'Format file tidak didukung. Gunakan PDF atau JPG.',
            'file.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        $files = $validated['files'] ?? [$validated['file']];
        $savedDocuments = [];
        try {
            foreach ($files as $file) {
                $this->documents->validateUpload($file);
            }

            foreach ($files as $file) {
                $document = $this->documents->storeSppt($land, $file, $user);
                $savedDocuments[] = [
                    'id' => $document->id,
                    'original_name' => $document->original_name,
                    'size_bytes' => $document->size_bytes,
                    'download_url' => route('api.v1.documents.sppt.document.download', ['idDokumen' => $document->id]),
                ];
                AuditLogger::log('dokumen.upload', 'Anggota menambahkan SPPT untuk lahan ' . $land->nama_lahan, 'DokumenLahan', $document->id, $land->id_kelompok, $request);
            }
        } catch (\RuntimeException $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        }

        $responseData = [
            'id_lahan' => $land->id_lahan,
            'documents' => $savedDocuments,
            'count' => count($savedDocuments),
        ];
        // Pertahankan field respons lama saat hanya satu file diunggah.
        if (count($savedDocuments) === 1) {
            $responseData = array_merge($savedDocuments[0], $responseData);
        }

        return response()->json([
            'success' => true,
            'message' => count($savedDocuments) . ' dokumen SPPT berhasil ditambahkan.',
            'data' => $responseData,
        ], 201);
    }

    public function downloadIdentity(Request $request, string $idPengguna, string $jenis): StreamedResponse
    {
        $member = Pengguna::where('role', 'Anggota')->where('id_pengguna', $idPengguna)->firstOrFail();
        $this->authorizeMemberDocument($request, $member);
        $jenis = strtoupper($jenis);
        abort_unless(in_array($jenis, ['KTP', 'KK'], true), 404);

        $document = DokumenAnggota::where('id_pengguna', $member->id_pengguna)->where('jenis', $jenis)->firstOrFail();
        abort_unless(Storage::disk('documents')->exists($document->file_path), 404, 'File dokumen tidak ditemukan.');

        AuditLogger::log('dokumen.download', 'Mengunduh dokumen ' . $jenis . ' anggota ' . $member->nama, 'DokumenAnggota', $document->id, $member->id_kelompok, $request);

        return Storage::disk('documents')->download($document->file_path, $this->safeDownloadName($document->original_name), [
            'Content-Type' => $document->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

    public function apiDownloadIdentity(Request $request, string $jenis): StreamedResponse
    {
        return $this->downloadIdentity($request, $request->user()->id_pengguna, $jenis);
    }

    public function apiDownloadSppt(Request $request, string $idLahan): StreamedResponse
    {
        // Endpoint lama dipertahankan: unduh dokumen SPPT terbaru pada lahan.
        $land = Lahan::where('id_lahan', $idLahan)->firstOrFail();
        $this->authorizeLandDocument($request, $land);
        $document = DokumenLahan::where('id_lahan', $land->id_lahan)->latest()->firstOrFail();

        return $this->downloadSpptDocument($request, $document);
    }

    public function apiDownloadSpptDocument(Request $request, string $idDokumen): StreamedResponse
    {
        $document = DokumenLahan::with('lahan')->findOrFail($idDokumen);
        abort_unless($document->lahan, 404);
        $this->authorizeLandDocument($request, $document->lahan);

        return $this->downloadSpptDocument($request, $document);
    }

    public function downloadMemberArchive(Request $request, string $idPengguna): BinaryFileResponse
    {
        $member = Pengguna::where('role', 'Anggota')->where('id_pengguna', $idPengguna)
            ->with(['dokumenIdentitas', 'lahan.dokumenSppt'])
            ->firstOrFail();
        $this->authorizeMemberDocument($request, $member);

        abort_unless(class_exists(\ZipArchive::class), 503, 'Fitur unduh semua dokumen memerlukan ekstensi PHP ZIP (php_zip). Aktifkan ekstensi ZIP pada PHP lalu coba lagi.');

        $archiveDirectory = storage_path('app/private/document-exports');
        if (!is_dir($archiveDirectory)) {
            mkdir($archiveDirectory, 0700, true);
        }
        // Bersihkan arsip unduhan yang tertinggal lebih dari satu jam (misalnya
        // saat browser menutup koneksi sebelum response selesai dikirim).
        foreach (glob($archiveDirectory . DIRECTORY_SEPARATOR . '*.zip') ?: [] as $oldArchive) {
            if (is_file($oldArchive) && filemtime($oldArchive) < (time() - 3600)) {
                @unlink($oldArchive);
            }
        }
        $archivePath = $archiveDirectory . DIRECTORY_SEPARATOR . 'dokumen-' . Str::uuid() . '.zip';
        $zip = new \ZipArchive();
        $opened = $zip->open($archivePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        abort_unless($opened === true, 500, 'Arsip dokumen gagal dibuat.');

        $added = 0;
        $usedNames = [];
        $addFile = function (string $path, string $folder, ?string $originalName) use ($zip, &$added, &$usedNames): void {
            if (!Storage::disk('documents')->exists($path)) {
                return;
            }
            $baseName = $this->safeDownloadName($originalName);
            $archiveName = $folder . '/' . $baseName;
            $counter = 1;
            while (isset($usedNames[$archiveName])) {
                $info = pathinfo($baseName);
                $archiveName = $folder . '/' . ($info['filename'] ?? 'dokumen') . '-' . $counter++ . (isset($info['extension']) ? '.' . $info['extension'] : '');
            }
            $usedNames[$archiveName] = true;
            $success = $zip->addFile(Storage::disk('documents')->path($path), $archiveName);
            if ($success) {
                $added++;
            }
        };

        foreach ($member->dokumenIdentitas as $document) {
            $addFile($document->file_path, 'Identitas', $document->jenis . '_' . $document->original_name);
        }
        foreach ($member->lahan as $land) {
            foreach ($land->dokumenSppt as $document) {
                $landFolder = 'SPPT/' . $this->safeDownloadName($land->nama_lahan ?: $land->id_lahan);
                $addFile($document->file_path, $landFolder, $document->original_name);
            }
        }

        $closed = $zip->close();
        if (!$closed) {
            @unlink($archivePath);
            abort(500, 'Arsip dokumen tidak dapat diselesaikan.');
        }
        if ($added === 0) {
            @unlink($archivePath);
            abort(404, 'Belum ada dokumen yang dapat diunduh untuk anggota ini.');
        }

        AuditLogger::log('dokumen.download_all', 'Mengunduh seluruh dokumen anggota ' . $member->nama, 'Pengguna', $member->id_pengguna, $member->id_kelompok, $request);

        return response()->download($archivePath, 'Dokumen-' . $this->safeDownloadName($member->nama) . '.zip', [
            'Content-Type' => 'application/zip',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
        ])->deleteFileAfterSend(true);
    }

    private function downloadSpptDocument(Request $request, DokumenLahan $document): StreamedResponse
    {
        abort_unless(Storage::disk('documents')->exists($document->file_path), 404, 'File SPPT tidak ditemukan.');
        $land = $document->lahan;
        AuditLogger::log('dokumen.download', 'Mengunduh dokumen SPPT untuk lahan ' . ($land?->nama_lahan ?? '-'), 'DokumenLahan', $document->id, $land?->id_kelompok, $request);

        return Storage::disk('documents')->download($document->file_path, $this->safeDownloadName($document->original_name), [
            'Content-Type' => $document->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

    private function authorizeMemberDocument(Request $request, Pengguna $member): void
    {
        $actor = $request->user();
        abort_unless($actor, 401);

        if (in_array($actor->role, ['Admin', 'PPL'], true)) {
            return;
        }
        if ($actor->role === 'Pengurus') {
            abort_unless($actor->status === 'Aktif' && $actor->id_kelompok && $actor->id_kelompok === $member->id_kelompok, 403);
            return;
        }
        abort_unless(
            $actor->role === 'Anggota'
                && $actor->status === 'Aktif'
                && $actor->anggota?->status_keanggotaan === 'Aktif'
                && $actor->id_pengguna === $member->id_pengguna,
            403
        );
    }

    private function authorizeLandDocument(Request $request, Lahan $land): void
    {
        $actor = $request->user();
        abort_unless($actor, 401);

        if (in_array($actor->role, ['Admin', 'PPL'], true)) {
            return;
        }
        if ($actor->role === 'Pengurus') {
            abort_unless($actor->status === 'Aktif' && $actor->id_kelompok && $actor->id_kelompok === $land->id_kelompok, 403);
            return;
        }
        abort_unless(
            $actor->role === 'Anggota'
                && $actor->status === 'Aktif'
                && $actor->anggota?->status_keanggotaan === 'Aktif'
                && $actor->id_pengguna === $land->id_pengguna
                && $actor->id_kelompok === $land->id_kelompok,
            403
        );
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
