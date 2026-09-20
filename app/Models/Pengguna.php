<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pengguna extends Authenticatable
{
    use HasFactory, Notifiable;

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
     * Generate unique random id_pengguna: PGR-AngkaRandom
     */
    public static function generateIdPengguna(): string
    {
        do {
            $randomNumber = mt_rand(1000, 9999);
            $id = 'PGR-' . $randomNumber;
        } while (self::where('id_pengguna', $id)->exists());

        return $id;
    }
}
