<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anggota extends Model
{
    use HasFactory;

    protected $table = 'anggota';
    protected $primaryKey = 'id_anggota';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_anggota',
        'id_pengguna',
        'status_keanggotaan',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id_anggota)) {
                $model->id_anggota = self::generateIdAnggota();
            }
        });
    }

    public static function generateIdAnggota(): string
    {
        do {
            $id = 'AGT-' . random_int(1000, 9999);
        } while (self::where('id_anggota', $id)->exists());

        return $id;
    }

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }
}
