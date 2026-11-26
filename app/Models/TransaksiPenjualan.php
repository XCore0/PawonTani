<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TransaksiPenjualan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transaksi_penjualan';
    protected $primaryKey = 'id_penjualan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_penjualan',
        'kode_transaksi',
        'id_pengguna',
        'id_kelompok',
        'id_pengurus',
        'id_panen',
        'id_produk',
        'id_komoditas',
        'nama_produk',
        'nama_pembeli',
        'no_telepon_pembeli',
        'jumlah',
        'satuan',
        'harga_satuan',
        'total_harga',
        'total_dibayar',
        'status_pembayaran',
        'status_penyerahan',
        'tanggal_transaksi',
        'waktu_transaksi',
        'metode_pembayaran',
        'catatan',
        'alasan_batal',
        'status_pengembalian_dana',
        'tanggal_diserahkan',
        'tanggal_dibatalkan',
    ];

    protected $casts = [
        'jumlah' => 'float',
        'harga_satuan' => 'float',
        'total_harga' => 'float',
        'total_dibayar' => 'float',
        'tanggal_transaksi' => 'date',
        'tanggal_diserahkan' => 'datetime',
        'tanggal_dibatalkan' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (empty($model->id_penjualan)) {
                $model->id_penjualan = self::generateIdPenjualan();
            }

            if (empty($model->kode_transaksi)) {
                $model->kode_transaksi = $model->id_penjualan;
            }

            // Simpan snapshot kelompok penjual agar transaksi Mobile/Marketplace
            // tetap dapat dilaporkan ke kelompok yang benar.
            if (empty($model->id_kelompok) && filled($model->id_pengguna)) {
                $model->id_kelompok = Pengguna::query()
                    ->where('id_pengguna', $model->id_pengguna)
                    ->value('id_kelompok');
            }
        });
    }

    public static function generateIdPenjualan(): string
    {
        $date = now()->format('Ymd');

        do {
            $random = strtoupper(Str::random(4));
            $id = "TRX-PN-{$date}-{$random}";
        } while (self::withTrashed()->where('id_penjualan', $id)->exists());

        return $id;
    }

    public function penjual(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengurus', 'id_pengguna');
    }

    public function kelompokTani(): BelongsTo
    {
        return $this->belongsTo(KelompokTani::class, 'id_kelompok', 'id_kelompok');
    }

    public function panen(): BelongsTo
    {
        return $this->belongsTo(Panen::class, 'id_panen', 'id_panen');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }

    public function komoditas(): BelongsTo
    {
        return $this->belongsTo(Komoditas::class, 'id_komoditas', 'id_komoditas');
    }

    public function riwayatPembayaran(): HasMany
    {
        return $this->hasMany(RiwayatPembayaranPenjualan::class, 'id_penjualan', 'id_penjualan')
            ->orderBy('created_at', 'asc');
    }

    public function getSisaPembayaranAttribute(): float
    {
        return max(0.0, (float) ($this->total_harga - $this->total_dibayar));
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status_pembayaran) {
            'lunas' => 'Lunas',
            'dp' => 'Uang Muka (DP)',
            'tempo' => 'Tempo Aktif',
            'dibatalkan' => 'Dibatalkan',
            default => 'Belum Dibayar',
        };
    }
}
