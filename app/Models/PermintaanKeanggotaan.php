<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermintaanKeanggotaan extends Model
{
    protected $table = 'permintaan_keanggotaan';

    protected $fillable = [
        'id_pengguna',
        'id_kelompok',
        'alasan',
        'status',
        'ditinjau_oleh',
        'catatan_admin',
        'ditinjau_pada',
    ];

    protected function casts(): array
    {
        return ['ditinjau_pada' => 'datetime'];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }

    public function kelompokTani(): BelongsTo
    {
        return $this->belongsTo(KelompokTani::class, 'id_kelompok', 'id_kelompok');
    }

    public function peninjau(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'ditinjau_oleh', 'id_pengguna');
    }
}
