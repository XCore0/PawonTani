<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatPengakhiranSiklus extends Model
{
    use HasFactory;

    protected $table = 'riwayat_pengakhiran_siklus';
    protected $primaryKey = 'id_riwayat';
    public $timestamps = false;

    protected $fillable = [
        'id_siklus',
        'id_pengguna',
        'jenis_pengakhiran',
        'aksi',
        'status_sebelumnya',
        'status_sesudahnya',
        'alasan',
        'foto_bukti',
        'verifikator_id',
        'verifikator_nama',
        'catatan_verifikasi',
        'tanggal_keputusan',
        'snapshot_data',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'tanggal_keputusan' => 'datetime',
            'snapshot_data' => 'array',
        ];
    }

    public function siklusTanam(): BelongsTo
    {
        return $this->belongsTo(SiklusTanam::class, 'id_siklus', 'id_siklus');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'verifikator_id', 'id_pengguna');
    }
}
