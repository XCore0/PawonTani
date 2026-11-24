<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Produk extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'produk';
    protected $primaryKey = 'id_produk';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_produk',
        'id_pengguna',
        'id_komoditas',
        'nama_produk',
        'foto_produk',
        'deskripsi',
        'jenis_harga',
        'harga',
        'harga_min',
        'harga_max',
        'satuan',
        'stok_fisik',
        'stok_ditahan',
        'status',
    ];

    protected $casts = [
        'harga' => 'float',
        'harga_min' => 'float',
        'harga_max' => 'float',
        'stok_fisik' => 'float',
        'stok_ditahan' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id_produk)) {
                $model->id_produk = self::generateIdProduk();
            }
        });
    }

    public static function generateIdProduk(): string
    {
        $date = now()->format('Ymd');
        do {
            $random = strtoupper(Str::random(4));
            $id = "PRD-{$date}-{$random}";
        } while (self::withTrashed()->where('id_produk', $id)->exists());

        return $id;
    }

    /**
     * Relasi ke penjual/petani
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }

    /**
     * Relasi ke komoditas
     */
    public function komoditas(): BelongsTo
    {
        return $this->belongsTo(Komoditas::class, 'id_komoditas', 'id_komoditas');
    }

    /**
     * Relasi ke transaksi penjualan
     */
    public function penjualan(): HasMany
    {
        return $this->hasMany(TransaksiPenjualan::class, 'id_produk', 'id_produk');
    }

    /**
     * Scope untuk produk aktif publik (tampil di marketplace)
     */
    public function scopePublik($query)
    {
        return $query->whereIn('status', ['aktif', 'stok_habis']);
    }

    /**
     * Hitung stok yang tersedia untuk dipesan (stok fisik - stok ditahan komitmen)
     */
    public function getStokTersediaAttribute(): float
    {
        return max(0.0, (float) ($this->stok_fisik - $this->stok_ditahan));
    }

    /**
     * Cek apakah produk benar-benar stok habis
     */
    public function getIsStokHabisAttribute(): bool
    {
        return $this->stok_tersedia <= 0 || $this->status === 'stok_habis';
    }

    /**
     * Format harga untuk visualisasi (Rp ...)
     */
    public function getHargaFormattedAttribute(): string
    {
        if ($this->jenis_harga === 'rentang' && $this->harga_min && $this->harga_max) {
            return 'Rp ' . number_format($this->harga_min, 0, ',', '.') . ' - Rp ' . number_format($this->harga_max, 0, ',', '.');
        }
        return 'Rp ' . number_format($this->harga, 0, ',', '.');
    }

    /**
     * Total kuantitas produk yang telah berhasil terjual (transaksi tidak dibatalkan)
     */
    public function getTotalTerjualAttribute(): float
    {
        return (float) $this->penjualan()
            ->where('status_pembayaran', '!=', 'dibatalkan')
            ->sum('jumlah');
    }
}
