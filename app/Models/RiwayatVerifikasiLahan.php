<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RiwayatVerifikasiLahan extends Model
{
    use HasFactory;

    protected $table = 'riwayat_verifikasi_lahan';
    protected $primaryKey = 'id_riwayat';
    public $incrementing = true;
    protected $keyType = 'int';

    // Tabel riwayat_verifikasi_lahan hanya memiliki kolom created_at, tanpa updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'id_lahan',
        'id_pengguna',
        'aksi',
        'status_sebelumnya',
        'status_sesudahnya',
        'catatan',
        'nomor_revisi',
        'snapshot_data',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_data' => 'array',
            'nomor_revisi' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Relationship to Lahan
     */
    public function lahan(): BelongsTo
    {
        return $this->belongsTo(Lahan::class, 'id_lahan', 'id_lahan')->withTrashed();
    }

    /**
     * Relationship to Actor / Verifier (Pengguna)
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }

    /**
     * Relationship to Notifications generated from this history record
     */
    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'id_riwayat', 'id_riwayat');
    }
}
