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

class SiklusTanam extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'siklus_tanam';
    protected $primaryKey = 'id_siklus';
    public $incrementing = false;
    protected $keyType = 'string';

    // Status siklus tanam
    public const STATUS_AKTIF = 'aktif';
    public const STATUS_MENUNGGU_SELESAI = 'menunggu_selesai';
    public const STATUS_MENUNGGU_GAGAL = 'menunggu_gagal';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_GAGAL = 'gagal';
    public const STATUS_DIBATALKAN = 'dibatalkan';

    // Definisi siklus berjalan: budidaya masih berlangsung atau sedang menunggu keputusan pengakhiran Ketua
    public const STATUSES_BERJALAN = [
        self::STATUS_AKTIF,
        self::STATUS_MENUNGGU_SELESAI,
        self::STATUS_MENUNGGU_GAGAL,
    ];

    // Status terminal / final: siklus telah berakhir secara resmi
    public const STATUSES_TERMINAL = [
        self::STATUS_SELESAI,
        self::STATUS_GAGAL,
        self::STATUS_DIBATALKAN,
    ];

    protected $fillable = [
        'id_siklus',
        'id_lahan',
        'id_pengguna',
        'id_kelompok',
        'id_komoditas',
        'komoditas_nama',
        'varietas',
        'musim_tanam',
        'tanggal_mulai',
        'estimasi_panen',
        'tanggal_selesai',
        'status',
        'jenis_pengakhiran',
        'tanggal_pengajuan',
        'tanggal_verifikasi',
        'alasan_pengakhiran',
        'foto_bukti_gagal',
        'target_panen_ton',
        'diverifikasi_oleh',
        'catatan_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'estimasi_panen' => 'date',
            'tanggal_selesai' => 'date',
            'tanggal_pengajuan' => 'datetime',
            'tanggal_verifikasi' => 'datetime',
            'target_panen_ton' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SiklusTanam $siklus): void {
            if (empty($siklus->id_siklus)) {
                $siklus->id_siklus = static::generateIdSiklus();
            }
        });
    }

    /**
     * Generate ID unik Siklus: SKL-YYYYMMDD-XXXX
     */
    public static function generateIdSiklus(): string
    {
        $date = now()->format('Ymd');
        do {
            $random = strtoupper(Str::random(4));
            $id = "SKL-{$date}-{$random}";
        } while (self::withTrashed()->where('id_siklus', $id)->exists());

        return $id;
    }

    /**
     * Menyimpan model dengan mekanisme retry jika terjadi tabrakan unik ID (SQLSTATE 23505).
     */
    public function saveWithUniqueRetry(int $maxAttempts = 5): bool
    {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                return DB::transaction(function () {
                    if (empty($this->id_siklus)) {
                        $this->id_siklus = static::generateIdSiklus();
                    }

                    return $this->save();
                });
            } catch (QueryException $e) {
                $isUniqueCollision = in_array((string) $e->getCode(), ['23505', '23000'], true)
                    || str_contains($e->getMessage(), 'siklus_tanam_pkey');

                if ($isUniqueCollision && $attempt < $maxAttempts) {
                    $this->id_siklus = static::generateIdSiklus();
                    continue;
                }

                throw $e;
            }
        }

        return false;
    }

    // ==========================================
    // QUERY SCOPES
    // ==========================================

    /**
     * Scope siklus berjalan (aktif atau sedang menunggu persetujuan pengakhiran Ketua).
     * Selama berstatus ini, lahan tidak boleh memulai siklus baru.
     */
    public function scopeBerjalan($query)
    {
        return $query->whereIn('status', self::STATUSES_BERJALAN);
    }

    /**
     * Scope siklus aktif yang boleh menerima input pencatatan Aktivitas atau Panen baru.
     * Ketika siklus sedang 'menunggu_selesai' atau 'menunggu_gagal', input pencatatan dikunci.
     */
    public function scopeBolehInput($query)
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    /**
     * Scope siklus selesai resmi.
     */
    public function scopeSelesai($query)
    {
        return $query->where('status', self::STATUS_SELESAI);
    }

    /**
     * Scope siklus gagal resmi.
     */
    public function scopeGagal($query)
    {
        return $query->where('status', self::STATUS_GAGAL);
    }

    /**
     * Scope siklus dibatalkan mandiri oleh anggota.
     */
    public function scopeDibatalkan($query)
    {
        return $query->where('status', self::STATUS_DIBATALKAN);
    }

    // ==========================================
    // HELPER STATUS CHECKS
    // ==========================================

    public function isBerjalan(): bool
    {
        return in_array($this->status, self::STATUSES_BERJALAN, true);
    }

    public function isBolehInput(): bool
    {
        return $this->status === self::STATUS_AKTIF;
    }

    public function isMenungguVerifikasiPengakhiran(): bool
    {
        return in_array($this->status, [self::STATUS_MENUNGGU_SELESAI, self::STATUS_MENUNGGU_GAGAL], true);
    }

    public function isMenungguPengakhiran(): bool
    {
        return $this->isMenungguVerifikasiPengakhiran();
    }

    // ==========================================
    // RELASI ELOQUENT
    // ==========================================

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

    public function aktivitas(): HasMany
    {
        return $this->hasMany(AktivitasTanam::class, 'id_siklus', 'id_siklus');
    }

    public function panen(): HasMany
    {
        return $this->hasMany(Panen::class, 'id_siklus', 'id_siklus');
    }

    public function riwayatPengakhiran(): HasMany
    {
        return $this->hasMany(RiwayatPengakhiranSiklus::class, 'id_siklus', 'id_siklus')->orderByDesc('created_at');
    }

    /**
     * Total hasil panen yang telah disetujui dalam satuan Kg.
     */
    public function totalPanenKg(): float
    {
        return (float) $this->panen()->where('status', 'disetujui')->sum('berat_kg');
    }

    /**
     * Total hasil panen yang telah disetujui dalam satuan Ton.
     */
    public function totalPanenTon(): float
    {
        return round($this->totalPanenKg() / 1000.0, 2);
    }

    /**
     * Persentase ketercapaian target panen (jika target ditentukan).
     */
    public function persentaseTargetPanen(): ?float
    {
        if ($this->target_panen_ton === null || (float) $this->target_panen_ton <= 0) {
            return null;
        }

        return round(($this->totalPanenTon() / (float) $this->target_panen_ton) * 100, 1);
    }
}
