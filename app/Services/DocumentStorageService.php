<?php

namespace App\Services;

use App\Models\DokumenAnggota;
use App\Models\DokumenLahan;
use App\Models\Pengguna;
use App\Models\Lahan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class DocumentStorageService
{
    public const MAX_KB = 5120;
    private const DISK = 'documents';

    public function validateUpload(UploadedFile $file): void
    {
        $this->assertSafeFile($file);
    }

    public function storeIdentity(Pengguna $member, string $type, UploadedFile $file, ?Pengguna $actor = null): DokumenAnggota
    {
        $type = strtoupper($type);
        if (!in_array($type, ['KTP', 'KK'], true)) {
            throw new RuntimeException('Jenis dokumen identitas tidak valid.');
        }

        $this->assertSafeFile($file);
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');
        $fileName = $type . '-' . Str::uuid() . '.' . $extension;
        $path = $file->storeAs('anggota/' . $member->id_pengguna, $fileName, self::DISK);
        if (!$path) {
            throw new RuntimeException('Dokumen gagal disimpan.');
        }

        $document = DokumenAnggota::query()->where('id_pengguna', $member->id_pengguna)->where('jenis', $type)->first();
        $oldPath = $document?->file_path;

        try {
            $document = DokumenAnggota::updateOrCreate(
                ['id_pengguna' => $member->id_pengguna, 'jenis' => $type],
                [
                    'file_path' => $path,
                    'original_name' => Str::limit(basename($file->getClientOriginalName()), 250, ''),
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'size_bytes' => $file->getSize(),
                    'uploaded_by' => $actor?->id_pengguna,
                ]
            );
        } catch (\Throwable $exception) {
            Storage::disk(self::DISK)->delete($path);
            throw $exception;
        }

        if ($oldPath && $oldPath !== $path) {
            try {
                Storage::disk(self::DISK)->delete($oldPath);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return $document;
    }

    public function storeSppt(Lahan $land, UploadedFile $file, ?Pengguna $actor = null): DokumenLahan
    {
        $this->assertSafeFile($file);
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');
        $fileName = 'SPPT-' . Str::uuid() . '.' . $extension;
        $path = $file->storeAs('lahan/' . $land->id_lahan, $fileName, self::DISK);
        if (!$path) {
            throw new RuntimeException('Dokumen SPPT gagal disimpan.');
        }

        try {
            // SPPT bersifat multi-file: upload berikutnya menambah arsip baru dan
            // tidak menghapus dokumen SPPT yang sudah pernah diunggah.
            return DokumenLahan::create([
                'id_lahan' => $land->id_lahan,
                'jenis' => 'SPPT',
                'file_path' => $path,
                'original_name' => Str::limit(basename($file->getClientOriginalName()), 250, ''),
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size_bytes' => $file->getSize(),
                'uploaded_by' => $actor?->id_pengguna,
            ]);
        } catch (\Throwable $exception) {
            Storage::disk(self::DISK)->delete($path);
            throw $exception;
        }
    }

    public function deleteIdentity(DokumenAnggota $document): void
    {
        Storage::disk(self::DISK)->delete($document->file_path);
        $document->delete();
    }

    public function deleteSppt(DokumenLahan $document): void
    {
        Storage::disk(self::DISK)->delete($document->file_path);
        $document->delete();
    }

    private function assertSafeFile(UploadedFile $file): void
    {
        if (!$file->isValid() || $file->getSize() > self::MAX_KB * 1024) {
            throw new RuntimeException('File tidak valid atau melebihi batas 5 MB.');
        }

        $mime = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());
        $allowed = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
        ];

        if (!isset($allowed[$extension]) || $mime !== $allowed[$extension]) {
            throw new RuntimeException('Format file tidak didukung. Gunakan PDF atau JPG.');
        }

        // Periksa signature file agar file lain yang hanya diganti ekstensi tidak diterima.
        $header = @file_get_contents($file->getRealPath(), false, null, 0, 1024);
        if (!is_string($header)) {
            throw new RuntimeException('File tidak dapat diperiksa. Silakan pilih ulang dokumen.');
        }

        $signatureValid = $extension === 'pdf'
            ? str_contains($header, '%PDF-')
            : str_starts_with($header, "\xFF\xD8\xFF");

        if (!$signatureValid) {
            throw new RuntimeException('Isi file tidak sesuai dengan format PDF/JPG yang dipilih.');
        }
    }
}
