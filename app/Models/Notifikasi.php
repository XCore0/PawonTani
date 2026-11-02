<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasi';
    protected $primaryKey = 'id_notifikasi';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_notifikasi',
        'id_pengirim',
        'id_penerima',
        'id_lahan',
        'id_riwayat',
        'jenis_notifikasi',
        'judul',
        'pesan',
        'data_payload',
        'sudah_dibaca',
        'dibaca_pada',
    ];

    protected function casts(): array
    {
        return [
            'data_payload' => 'array',
            'sudah_dibaca' => 'boolean',
            'dibaca_pada' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Notifikasi $notifikasi): void {
            if (empty($notifikasi->id_notifikasi)) {
                $notifikasi->id_notifikasi = static::generateIdNotifikasi();
            }
        });
    }

    /**
     * Generate unique random ID: NTF-YYYYMMDD-XXXX
     */
    public static function generateIdNotifikasi(): string
    {
        $date = now()->format('Ymd');
        do {
            $random = strtoupper(Str::random(4));
            $id = "NTF-{$date}-{$random}";
        } while (self::where('id_notifikasi', $id)->exists());

        return $id;
    }

    /**
     * Execute save with retry on unique collision (SQLSTATE 23505).
     * Wrapped in DB::transaction to utilize PostgreSQL SAVEPOINTs when inside an outer transaction.
     */
    public function saveWithUniqueRetry(int $maxAttempts = 5): bool
    {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                return DB::transaction(function () {
                    if (empty($this->id_notifikasi)) {
                        $this->id_notifikasi = static::generateIdNotifikasi();
                    }

                    return $this->save();
                });
            } catch (QueryException $e) {
                $msg = $e->getMessage();
                $isUniqueViolation = $e->getCode() === '23505'
                    || str_contains($msg, '23505')
                    || str_contains($msg, 'UNIQUE constraint failed')
                    || str_contains($msg, 'unique constraint');

                $isIdCollision = $isUniqueViolation && (
                    str_contains($msg, 'id_notifikasi') || str_contains($msg, 'notifikasi_pkey')
                );

                if ($isIdCollision && $attempt < $maxAttempts) {
                    $this->id_notifikasi = static::generateIdNotifikasi();
                    continue;
                }
                throw $e;
            }
        }

        return false;
    }

    /**
     * Relationship to Sender (Pengguna)
     */
    public function pengirim(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengirim', 'id_pengguna');
    }

    /**
     * Relationship to Receiver (Pengguna)
     */
    public function penerima(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_penerima', 'id_pengguna');
    }

    /**
     * Relationship to Lahan
     */
    public function lahan(): BelongsTo
    {
        return $this->belongsTo(Lahan::class, 'id_lahan', 'id_lahan')->withTrashed();
    }

    /**
     * Relationship to Riwayat Verifikasi
     */
    public function riwayatVerifikasi(): BelongsTo
    {
        return $this->belongsTo(RiwayatVerifikasiLahan::class, 'id_riwayat', 'id_riwayat');
    }
}
