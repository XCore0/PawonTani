<?php

namespace App\Services;

use Cloudinary;
use Cloudinary\Uploader;

class CloudinaryService
{
    public function __construct()
    {
        // Konfigurasi Cloudinary versi 1.x
        Cloudinary::config_from_url(env('CLOUDINARY_URL'));
    }

    /**
     * Mengunggah file ke Cloudinary.
     *
     * @param string $filePath Path fisik file (misal: $request->file('...')->getRealPath())
     * @param string $folder   Nama folder di Cloudinary
     * @return string URL gambar yang aman (HTTPS)
     */
    public function upload($filePath, $folder = 'pawontani')
    {
        $response = Uploader::upload($filePath, [
            'folder' => $folder,
        ]);

        return $response['secure_url'] ?? null;
    }

    /**
     * Menghapus file dari Cloudinary berdasarkan URL-nya.
     *
     * @param string|null $url URL gambar di Cloudinary
     * @return void
     */
    public function delete($url)
    {
        if (empty($url)) {
            return;
        }

        // Ekstrak public_id dari URL Cloudinary
        // Format umum: https://res.cloudinary.com/cloud_name/image/upload/v1234567890/folder/filename.ext
        $path = parse_url($url, PHP_URL_PATH);
        
        if ($path) {
            $parts = explode('/upload/', $path);
            
            if (isset($parts[1])) {
                $afterUpload = $parts[1];
                $afterUploadParts = explode('/', $afterUpload);
                
                // Hapus versioning (misal: v1234567890) jika ada
                if (preg_match('/^v\d+$/', $afterUploadParts[0])) {
                    array_shift($afterUploadParts);
                }
                
                $publicIdWithExt = implode('/', $afterUploadParts);
                $filename = pathinfo($publicIdWithExt, PATHINFO_FILENAME);
                $folderPath = pathinfo($publicIdWithExt, PATHINFO_DIRNAME);
                
                $publicId = ($folderPath && $folderPath !== '.') ? $folderPath . '/' . $filename : $filename;
                
                try {
                    Uploader::destroy($publicId);
                } catch (\Exception $e) {
                    // Abaikan error jika file tidak ditemukan
                }
            }
        }
    }
}
