<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AksesDokumen extends Model
{
    use HasFactory;

    protected $table = 'akses_dokumen';

    protected $fillable = [
        'id_pengguna',
        'tipe_dokumen',
        'id_dokumen',
        'id_kelompok',
        'tujuan_penggunaan',
        'waktu_persetujuan',
        'waktu_berakhir',
        'status_akses',
        'keterangan',
    ];

    protected $casts = [
        'waktu_persetujuan' => 'datetime',
        'waktu_berakhir' => 'datetime',
    ];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }

    public function kelompokTani(): BelongsTo
    {
        return $this->belongsTo(KelompokTani::class, 'id_kelompok', 'id_kelompok');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(LogAksesDokumen::class, 'id_akses_dokumen', 'id');
    }

    public function isAktif(): bool
    {
        if ($this->status_akses !== 'aktif') {
            return false;
        }
        if ($this->waktu_berakhir && now()->greaterThan($this->waktu_berakhir)) {
            return false;
        }
        return true;
    }
}
