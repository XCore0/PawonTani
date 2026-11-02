<?php

namespace App\Services;

use Cloudinary;
use Cloudinary\Uploader;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class CloudinaryService
{
    public function __construct()
    {
        // Konfigurasi Cloudinary versi 1.x
        $cloudinaryUrl = env('CLOUDINARY_URL');
        if (!empty($cloudinaryUrl)) {
            Cloudinary::config_from_url($cloudinaryUrl);
        }
    }

    /**
     * Mengunggah file ke Cloudinary.
     *
     * @param string $filePath Path fisik file (misal: $request->file('...')->getRealPath())
     * @param string $folder   Nama folder di Cloudinary
     * @return string|null URL gambar yang aman (HTTPS)
     */
    public function upload($filePath, $folder = 'pawontani')
    {
        $response = Uploader::upload($filePath, [
            'folder' => $folder,
        ]);

        return $response['secure_url'] ?? null;
    }

    /**
     * Menghapus file dari Cloudinary berdasarkan URL atau public_id-nya.
     *
     * - Idempoten: status 'ok' dan 'not found' dianggap sukses.
     * - Tidak menelan exception: kegagalan API/jaringan dilempar kembali (rethrow)
     *   agar caller atau background job dapat melakukan retry.
     *
     * @param string|null $url URL gambar atau public_id di Cloudinary
     * @return bool True jika berhasil dihapus atau aset sudah tidak ada
     * @throws RuntimeException Jika terjadi kegagalan API atau jaringan saat menghapus
     */
    public function delete($url): bool
    {
        if (empty($url)) {
            return true;
        }

        $publicId = $this->extractPublicId($url);
        if (!$publicId) {
            Log::warning('Cloudinary delete: Format URL tidak valid atau public_id tidak dapat diekstrak.', [
                'url' => $this->sanitizeForLog($url),
            ]);
            return true;
        }

        try {
            $result = Uploader::destroy($publicId);
            $status = is_array($result) ? ($result['result'] ?? null) : null;

            if ($status === 'ok' || $status === 'not found') {
                Log::info('Cloudinary delete berhasil.', [
                    'public_id' => $publicId,
                    'result' => $status,
                ]);
                return true;
            }

            Log::error('Cloudinary delete menghasilkan status response tidak terduga.', [
                'public_id' => $publicId,
                'result' => $status,
            ]);

            throw new RuntimeException("Cloudinary destroy gagal untuk public_id '{$publicId}' dengan status: '{$status}'");
        } catch (Throwable $e) {
            if ($e instanceof RuntimeException) {
                throw $e;
            }

            Log::error('Gagal menghubungi Cloudinary saat menghapus file: ' . $e->getMessage(), [
                'public_id' => $publicId,
            ]);

            throw new RuntimeException("Gagal menghapus file Cloudinary '{$publicId}': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Ekstrak public_id dari URL Cloudinary atau kembalikan raw public_id.
     * Mendukung struktur folder bertingkat (misal: pawontani/lahan/foto1).
     *
     * @param string $url
     * @return string|null
     */
    public function extractPublicId(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        // Jika string bukan URL (tidak ada scheme / bukan http/https) dan tidak ada '/upload/'
        if (!preg_match('#^https?://#i', $url) && !str_contains($url, '/upload/')) {
            $filename = pathinfo($url, PATHINFO_FILENAME);
            $dirname = pathinfo($url, PATHINFO_DIRNAME);
            return ($dirname && $dirname !== '.') ? $dirname . '/' . $filename : $filename;
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) {
            return null;
        }

        $parts = explode('/upload/', $path);
        if (!isset($parts[1]) || empty($parts[1])) {
            return null;
        }

        $afterUpload = $parts[1];
        $segments = explode('/', $afterUpload);

        // Abaikan parameter transformasi Cloudinary dan versioning (v1234567890)
        while (!empty($segments)) {
            $current = $segments[0];
            if (preg_match('/^v\d+$/', $current)) {
                array_shift($segments);
                break; // Setelah versioning, semua segment berikutnya adalah folder dan public_id
            } elseif (str_contains($current, ',') || preg_match('/^[a-z]_[a-z0-9_-]+$/i', $current)) {
                // Segment transformasi
                array_shift($segments);
            } else {
                // Tidak ada segment transformasi/versioning lagi
                break;
            }
        }

        if (empty($segments)) {
            return null;
        }

        $publicIdWithExt = implode('/', $segments);
        $filename = pathinfo($publicIdWithExt, PATHINFO_FILENAME);
        $dirname = pathinfo($publicIdWithExt, PATHINFO_DIRNAME);

        return ($dirname && $dirname !== '.') ? $dirname . '/' . $filename : $filename;
    }

    /**
     * Sanitasi string untuk keperluan log agar tidak membocorkan query params sensitif.
     *
     * @param string $string
     * @return string
     */
    protected function sanitizeForLog(string $string): string
    {
        $path = parse_url($string, PHP_URL_PATH);
        $host = parse_url($string, PHP_URL_HOST);
        return ($host && $path) ? $host . $path : substr($string, 0, 50);
    }
}
