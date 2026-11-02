<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Lahan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lahan';
    protected $primaryKey = 'id_lahan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_lahan',
        'id_pengguna',
        'id_kelompok',
        'nama_lahan',
        'luas',
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'desa_id',
        'alamat_lahan',
        'latitude',
        'longitude',
        'id_komoditas',
        'musim_tanam',
        'foto_lahan',
        'keterangan',
        'status_operasional',
        'status_verifikasi',
        'catatan_verifikasi',
        'tanggal_verifikasi',
        'verifikator_id',
        'nomor_revisi',
    ];

    protected function casts(): array
    {
        return [
            'luas' => 'decimal:4',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'tanggal_verifikasi' => 'datetime',
            'nomor_revisi' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Lahan $lahan): void {
            if (empty($lahan->id_lahan)) {
                $lahan->id_lahan = static::generateIdLahan();
            }
        });
    }

    /**
     * Generate unique random ID: LHN-YYYYMMDD-XXXX
     */
    public static function generateIdLahan(): string
    {
        $date = now()->format('Ymd');
        do {
            $random = strtoupper(Str::random(4));
            $id = "LHN-{$date}-{$random}";
        } while (self::withTrashed()->where('id_lahan', $id)->exists());

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
                    if (empty($this->id_lahan)) {
                        $this->id_lahan = static::generateIdLahan();
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
                    str_contains($msg, 'id_lahan') || str_contains($msg, 'lahan_pkey')
                );

                if ($isIdCollision && $attempt < $maxAttempts) {
                    $this->id_lahan = static::generateIdLahan();
                    continue;
                }
                throw $e;
            }
        }

        return false;
    }

    /**
     * Relationship to Owner (Pengguna)
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }

    /**
     * Relationship to Kelompok Tani
     */
    public function kelompokTani(): BelongsTo
    {
        return $this->belongsTo(KelompokTani::class, 'id_kelompok', 'id_kelompok');
    }

    /**
     * Relationship to Komoditas
     */
    public function komoditas(): BelongsTo
    {
        return $this->belongsTo(Komoditas::class, 'id_komoditas', 'id_komoditas');
    }

    /**
     * Relationship to Verifier (Pengguna)
     */
    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'verifikator_id', 'id_pengguna');
    }

    /**
     * Relationship to Verification History
     */
    public function riwayatVerifikasi(): HasMany
    {
        return $this->hasMany(RiwayatVerifikasiLahan::class, 'id_lahan', 'id_lahan')->orderByDesc('created_at');
    }

    /**
     * Relationship to Notifications
     */
    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'id_lahan', 'id_lahan');
    }
}
