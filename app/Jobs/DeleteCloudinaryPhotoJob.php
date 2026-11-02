<?php

namespace App\Jobs;

use App\Services\CloudinaryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class DeleteCloudinaryPhotoJob implements ShouldQueueAfterCommit
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah maksimal percobaan job jika gagal.
     *
     * @var int
     */
    public int $tries = 5;

    /**
     * Jeda waktu antar retry dalam detik: 10s, 60s, 300s (5 menit).
     * Percobaan ke-4 dan ke-5 akan menggunakan nilai terakhir (300 detik).
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 60, 300];

    /**
     * URL foto Cloudinary yang akan dihapus.
     *
     * @var string
     */
    public string $photoUrl;

    /**
     * Create a new job instance.
     *
     * Catatan Arsitektur:
     * Job ini mengimplementasikan ShouldQueueAfterCommit sehingga Laravel secara otomatis
     * menahan (defer) dispatch job hingga transaksi database aktif berhasil commit.
     * Tidak mendeklarasikan ulang properti $afterCommit bertipe kaku untuk menjaga
     * kompatibilitas penuh dengan trait Queueable.
     *
     * @param string $photoUrl
     */
    public function __construct(string $photoUrl)
    {
        $this->photoUrl = $photoUrl;
    }

    /**
     * Eksekusi job untuk menghapus foto dari Cloudinary.
     *
     * Jika terjadi kegagalan (network/API Cloudinary), exception akan dilempar kembali
     * agar worker antrean (queue worker) dapat menjadwalkan ulang (retry) sesuai backoff.
     *
     * @param CloudinaryService $cloudinary
     * @return void
     * @throws Throwable
     */
    public function handle(CloudinaryService $cloudinary): void
    {
        $cloudinary->delete($this->photoUrl);
    }

    /**
     * Callback ketika job gagal secara permanen setelah semua retry habis ($tries = 5).
     * Catatan kegagalan akhir akan tercatat di tabel failed_jobs dan Laravel log.
     *
     * Log sanitization:
     * - Menggunakan public_id aman (bukan raw URL).
     * - Tidak mencatat query string, signature, token, atau kredensial.
     * - Mempertahankan pesan error untuk keperluan diagnosis teknis.
     *
     * @param Throwable|null $exception
     * @return void
     */
    public function failed(?Throwable $exception): void
    {
        $safeIdentifier = null;
        try {
            $cloudinary = app(CloudinaryService::class);
            $safeIdentifier = $cloudinary->extractPublicId($this->photoUrl);
        } catch (Throwable) {
            // Fallback jika container tidak dapat me-resolve service saat logging
        }

        if (!$safeIdentifier) {
            $path = parse_url($this->photoUrl, PHP_URL_PATH);
            $safeIdentifier = $path ? basename($path) : 'unknown_asset';
        }

        Log::critical('DeleteCloudinaryPhotoJob: Gagal menghapus foto Cloudinary setelah batas maksimal percobaan.', [
            'public_id' => $safeIdentifier,
            'max_tries' => $this->tries,
            'error' => $exception?->getMessage(),
        ]);
    }
}
