<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RiwayatPembayaranPenjualan extends Model
{
    use HasFactory;

    protected $table = 'riwayat_pembayaran_penjualan';
    protected $primaryKey = 'id_pembayaran';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_pembayaran',
        'id_penjualan',
        'nominal',
        'tanggal_pembayaran',
        'keterangan',
        'status_verifikasi',
        'bukti_transfer',
    ];

    protected $casts = [
        'nominal' => 'float',
        'tanggal_pembayaran' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id_pembayaran)) {
                $model->id_pembayaran = self::generateIdPembayaran();
            }
        });
    }

    public static function generateIdPembayaran(): string
    {
        $date = now()->format('Ymd');
        do {
            $random = strtoupper(Str::random(4));
            $id = "PAY-{$date}-{$random}";
        } while (self::where('id_pembayaran', $id)->exists());

        return $id;
    }

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'id_penjualan', 'id_penjualan');
    }
}
