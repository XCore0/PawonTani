<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAksesDokumen extends Model
{
    use HasFactory;

    protected $table = 'log_akses_dokumen';

    protected $fillable = [
        'id_akses_dokumen',
        'id_pengguna',
        'tipe_dokumen',
        'id_dokumen',
        'jenis_aktivitas',
        'waktu_akses',
        'keterangan',
    ];

    protected $casts = [
        'waktu_akses' => 'datetime',
    ];

    public function aksesDokumen(): BelongsTo
    {
        return $this->belongsTo(AksesDokumen::class, 'id_akses_dokumen', 'id');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }
}
