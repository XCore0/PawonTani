<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Pengguna extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'pengguna';
    protected $primaryKey = 'id_pengguna';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_pengguna',
        'nama',
        'nik',
        'username',
        'password',
        'email',
        'no_telepon',
        'alamat',
        'foto_profil',
        'role',
        'jabatan',
        'id_kelompok',
        'status',
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'desa_id',
        'lokasi_nama',
        'latitude',
        'longitude',
        'kontak_publik_aktif',
    ];

    /**
     * Relationship to Kelompok Tani
     */
    public function kelompokTani()
    {
        return $this->belongsTo(KelompokTani::class, 'id_kelompok', 'id_kelompok');
    }

    public function anggota()
    {
        return $this->hasOne(Anggota::class, 'id_pengguna', 'id_pengguna');
    }

    public function dokumenIdentitas()
    {
        return $this->hasMany(DokumenAnggota::class, 'id_pengguna', 'id_pengguna');
    }

    /**
     * Relationship to Owned Lahan
     */
    public function lahan()
    {
        return $this->hasMany(Lahan::class, 'id_pengguna', 'id_pengguna');
    }

    /**
     * Relationship to Lahan verified by this user
     */
    public function lahanDiverifikasi()
    {
        return $this->hasMany(Lahan::class, 'verifikator_id', 'id_pengguna');
    }

    /**
     * Relationship to Verification History logged by this user
     */
    public function riwayatVerifikasi()
    {
        return $this->hasMany(RiwayatVerifikasiLahan::class, 'id_pengguna', 'id_pengguna');
    }

    /**
     * Relationship to Notifications received by this user
     */
    public function notifikasiDiterima()
    {
        return $this->hasMany(Notifikasi::class, 'id_penerima', 'id_pengguna');
    }

    /**
     * Relationship to Notifications sent by this user
     */
    public function notifikasiDikirim()
    {
        return $this->hasMany(Notifikasi::class, 'id_pengirim', 'id_pengguna');
    }

    /**
     * Relationship to Plant Activities
     */
    public function aktivitas()
    {
        return $this->hasMany(AktivitasTanam::class, 'id_pengguna', 'id_pengguna');
    }

    /**
     * Relationship to Marketplace Products
     */
    public function produk()
    {
        return $this->hasMany(Produk::class, 'id_pengguna', 'id_pengguna');
    }

    /**
     * Relationship to Sales Transactions
     */
    public function penjualan()
    {
        return $this->hasMany(TransaksiPenjualan::class, 'id_pengguna', 'id_pengguna');
    }

    /**
     * Relationship to Siklus Tanam
     */
    public function siklusTanam()
    {
        return $this->hasMany(SiklusTanam::class, 'id_pengguna', 'id_pengguna');
    }

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Auto-generate id_pengguna with format PGR-XXXX and default role to Pengurus
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id_pengguna)) {
                $model->id_pengguna = self::generateIdPengguna();
            }
            if (empty($model->role)) {
                $model->role = 'Pengurus';
            }
        });
    }

    /**
     * Generate unique random id_pengguna: PGR-XXXXXX (alphanumeric, 6 karakter acak)
     */
    public static function generateIdPengguna(): string
    {
        do {
            $random = strtoupper(\Illuminate\Support\Str::random(6));
            $id = 'PGR-' . $random;
        } while (self::where('id_pengguna', $id)->exists());

        return $id;
    }
}
