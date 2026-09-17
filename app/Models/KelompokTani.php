<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelompokTani extends Model
{
    use HasFactory;

    protected $table = 'kelompok_tani';
    protected $primaryKey = 'id_kelompok';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_kelompok',
        'nama_kelompok',
        'alamat',
        'status',
    ];

    /**
     * Relationship to Pengurus
     */
    public function pengurus()
    {
        return $this->hasMany(Pengguna::class, 'id_kelompok', 'id_kelompok')->where('role', 'Pengurus');
    }

    /**
     * Auto-generate id_kelompok like PokTan-XXXX if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id_kelompok)) {
                $model->id_kelompok = self::generateIdKelompok();
            }
        });
    }

    /**
     * Generate unique random id_kelompok: PokTan-AngkaRandom
     */
    public static function generateIdKelompok(): string
    {
        do {
            $randomNumber = mt_rand(1000, 9999);
            $id = 'PokTan-' . $randomNumber;
        } while (self::where('id_kelompok', $id)->exists());

        return $id;
    }
}
