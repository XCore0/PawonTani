<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AktivitasTanam extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'aktivitas_tanam';
    protected $primaryKey = 'id_aktivitas';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_aktivitas',
        'id_lahan',
        'id_pengguna',
        'id_kelompok',
        'kategori',
        'judul',
        'kondisi_tanaman',
        'tanggal',
        'waktu',
        'hari_setelah_tanam',
        'catatan',
        'foto_aktivitas',
        'foto_aktivitas_list',
        'biaya',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'hari_setelah_tanam' => 'integer',
            'biaya' => 'decimal:2',
            'foto_aktivitas_list' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (AktivitasTanam $model): void {
            if (empty($model->id_aktivitas)) {
                $model->id_aktivitas = static::generateIdAktivitas();
            }
        });
    }

    /**
     * Generate unique random ID: AKT-YYYYMMDD-XXXX
     */
    public static function generateIdAktivitas(): string
    {
        $date = now()->format('Ymd');
        do {
            $random = strtoupper(Str::random(4));
            $id = "AKT-{$date}-{$random}";
        } while (self::withTrashed()->where('id_aktivitas', $id)->exists());

        return $id;
    }

    /**
     * Execute save with retry on unique collision (SQLSTATE 23505).
     */
    public function saveWithUniqueRetry(int $maxAttempts = 5): bool
    {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                return DB::transaction(function () {
                    if (empty($this->id_aktivitas)) {
                        $this->id_aktivitas = static::generateIdAktivitas();
                    }

                    return $this->save();
                });
            } catch (QueryException $e) {
                $msg = $e->getMessage();
                $isUniqueViolation = $e->getCode() === '23505'
                    || str_contains($msg, '23505')
                    || str_contains($msg, 'unique')
                    || str_contains($msg, 'Duplicate');

                if ($isUniqueViolation && $attempt < $maxAttempts) {
                    $this->id_aktivitas = null;
                    continue;
                }

                throw $e;
            }
        }

        return false;
    }

    public function lahan(): BelongsTo
    {
        return $this->belongsTo(Lahan::class, 'id_lahan', 'id_lahan');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }

    public function kelompokTani(): BelongsTo
    {
        return $this->belongsTo(KelompokTani::class, 'id_kelompok', 'id_kelompok');
    }
}

