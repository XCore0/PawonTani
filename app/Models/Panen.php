<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Panen extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'panen';
    protected $primaryKey = 'id_panen';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_panen',
        'kode_panen',
        'id_lahan',
        'id_siklus',
        'id_pengguna',
        'id_kelompok',
        'id_komoditas',
        'komoditas_nama',
        'varietas',
        'jenis_panen',
        'tanggal_panen',
        'waktu_panen',
        'musim_tanam',
        'berat_kg',
        'kualitas_grade',
        'kadar_air',
        'persen_butir_utuh',
        'persen_hampa_kotoran',
        'metode_panen',
        'biaya_panen',
        'keterangan_tenaga_kerja',
        'harga_per_kg',
        'catatan',
        'foto_panen',
        'foto_panen_list',
        'status',
        'diverifikasi_oleh',
        'tanggal_verifikasi',
        'catatan_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_panen' => 'date',
            'tanggal_verifikasi' => 'datetime',
            'berat_kg' => 'decimal:2',
            'kadar_air' => 'decimal:2',
            'persen_butir_utuh' => 'decimal:2',
            'persen_hampa_kotoran' => 'decimal:2',
            'biaya_panen' => 'decimal:2',
            'harga_per_kg' => 'decimal:2',
            'foto_panen_list' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Panen $model): void {
            if (empty($model->id_panen)) {
                $model->id_panen = static::generateIdPanen();
            }
            if (empty($model->kode_panen)) {
                $model->kode_panen = static::generateKodePanen();
            }
        });
    }

    /**
     * Generate ID: PN-YYYYMMDD-XXXX
     */
    public static function generateIdPanen(): string
    {
        $prefix = 'PN-' . now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        return "{$prefix}-{$random}";
    }

    /**
     * Generate Kode Panen terurut: PN-YYYYMM-XXX
     */
    public static function generateKodePanen(): string
    {
        $prefix = 'PN-' . now()->format('Ym');
        $count = static::withTrashed()->where('kode_panen', 'like', "{$prefix}-%")->count() + 1;
        return sprintf('%s-%03d', $prefix, $count);
    }

    public function saveWithUniqueRetry(int $maxAttempts = 5): bool
    {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                if (empty($this->id_panen)) {
                    $this->id_panen = static::generateIdPanen();
                }
                if (empty($this->kode_panen)) {
                    $this->kode_panen = static::generateKodePanen();
                }
                return $this->save();
            } catch (QueryException $e) {
                $isUniqueViolation = in_array((string) $e->getCode(), ['23505', '23000'], true)
                    || str_contains($e->getMessage(), 'panen_pkey')
                    || str_contains($e->getMessage(), 'panen_kode_panen_unique');

                if ($isUniqueViolation && $attempt < $maxAttempts) {
                    $this->id_panen = static::generateIdPanen();
                    $this->kode_panen = 'PN-' . now()->format('Ym') . '-' . strtoupper(Str::random(3));
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

    public function komoditas(): BelongsTo
    {
        return $this->belongsTo(Komoditas::class, 'id_komoditas', 'id_komoditas');
    }

    public function siklusTanam(): BelongsTo
    {
        return $this->belongsTo(SiklusTanam::class, 'id_siklus', 'id_siklus');
    }

    public function scopeForSiklus($query, string $idSiklus)
    {
        return $query->where('id_siklus', $idSiklus);
    }

    public function scopeLegacy($query)
    {
        return $query->whereNull('id_siklus');
    }
}
