<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Penjualan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'penjualan';
    protected $primaryKey = 'id_penjualan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_penjualan',
        'id_kelompok',
        'id_panen',
        'id_pengurus',
        'tanggal_penjualan',
        'nama_pembeli',
        'komoditas_nama',
        'berat_kg',
        'harga_per_kg',
        'total_harga',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_penjualan' => 'date',
            'berat_kg' => 'decimal:2',
            'harga_per_kg' => 'decimal:2',
            'total_harga' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Penjualan $penjualan): void {
            if (empty($penjualan->id_penjualan)) {
                do {
                    $id = 'JUAL-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
                } while (static::withTrashed()->where('id_penjualan', $id)->exists());

                $penjualan->id_penjualan = $id;
            }
        });
    }

    public function kelompokTani(): BelongsTo
    {
        return $this->belongsTo(KelompokTani::class, 'id_kelompok', 'id_kelompok');
    }

    public function panen(): BelongsTo
    {
        return $this->belongsTo(Panen::class, 'id_panen', 'id_panen');
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengurus', 'id_pengguna');
    }
}
